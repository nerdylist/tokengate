<?php
/**
 * Admin Controller
 * Handles all admin operations (CRUD for users, bounties, profiles, etc.)
 */

class AdminController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Get dashboard statistics
     * @return array Statistics for dashboard
     */
    public function getDashboardStats()
    {
        $stats = [];

        // Total users
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM users");
        $stats['users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Total bounties
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM bounties");
        $stats['bounties'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Total profiles
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM rent_profiles");
        $stats['profiles'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Total applications
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM applications");
        $stats['applications'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        return $stats;
    }

    /**
     * Get recent activity (last 10 bounties and applications)
     * @return array Recent activity data
     */
    public function getRecentActivity()
    {
        $activity = [];

        // Recent bounties
        $sql = "SELECT b.id, b.title, b.created_at, u.email as user_email
                FROM bounties b
                LEFT JOIN users u ON b.user_id = u.id
                ORDER BY b.created_at DESC
                LIMIT 10";
        $stmt = $this->db->query($sql);
        $activity['bounties'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Recent applications
        $sql = "SELECT a.id, b.title as bounty_title, a.profile_id, a.status, a.created_at
                FROM applications a
                LEFT JOIN bounties b ON a.bounty_id = b.id
                ORDER BY a.created_at DESC
                LIMIT 10";
        $stmt = $this->db->query($sql);
        $activity['applications'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $activity;
    }

    /**
     * Toggle user admin status
     * @param int $userId User ID
     * @param int $isAdmin New admin status (0 or 1)
     * @return array Success/error response
     */
    public function updateUserRole($userId, $isAdmin)
    {
        try {
            $stmt = $this->db->prepare("UPDATE users SET is_admin = ? WHERE id = ?");
            $stmt->execute([$isAdmin, $userId]);

            return ['success' => true, 'message' => 'User role updated successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error updating user role: ' . $e->getMessage()];
        }
    }

    /**
     * Update bounty status
     * @param int $bountyId Bounty ID
     * @param string $status New status
     * @return array Success/error response
     */
    public function updateBountyStatus($bountyId, $status)
    {
        $validStatuses = ['open', 'in_progress', 'completed', 'cancelled'];

        if (!in_array($status, $validStatuses)) {
            return ['success' => false, 'message' => 'Invalid status'];
        }

        try {
            $stmt = $this->db->prepare("UPDATE bounties SET status = ? WHERE id = ?");
            $stmt->execute([$status, $bountyId]);

            return ['success' => true, 'message' => 'Bounty status updated successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error updating bounty status: ' . $e->getMessage()];
        }
    }

    /**
     * Update application status
     * @param int $applicationId Application ID
     * @param string $status New status
     * @return array Success/error response
     */
    public function updateApplicationStatus($applicationId, $status)
    {
        $validStatuses = ['pending', 'accepted', 'rejected'];

        if (!in_array($status, $validStatuses)) {
            return ['success' => false, 'message' => 'Invalid status'];
        }

        try {
            $stmt = $this->db->prepare("UPDATE applications SET status = ? WHERE id = ?");
            $stmt->execute([$status, $applicationId]);

            return ['success' => true, 'message' => 'Application status updated successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error updating application status: ' . $e->getMessage()];
        }
    }

    /**
     * Delete bounty and cascade to applications
     * @param int $id Bounty ID
     * @return array Success/error response
     */
    public function deleteBounty($id)
    {
        try {
            $this->db->beginTransaction();

            // Delete applications for this bounty
            $stmt = $this->db->prepare("DELETE FROM applications WHERE bounty_id = ?");
            $stmt->execute([$id]);

            // Delete bounty
            $stmt = $this->db->prepare("DELETE FROM bounties WHERE id = ?");
            $stmt->execute([$id]);

            $this->db->commit();

            return ['success' => true, 'message' => 'Bounty deleted successfully'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Error deleting bounty: ' . $e->getMessage()];
        }
    }

    /**
     * Delete profile and related data
     * @param int $id Profile ID
     * @return array Success/error response
     */
    public function deleteProfile($id)
    {
        try {
            $this->db->beginTransaction();

            // Delete applications for this profile
            $stmt = $this->db->prepare("DELETE FROM applications WHERE profile_id = (SELECT profile_id FROM rent_profiles WHERE id = ?)");
            $stmt->execute([$id]);

            // Delete profile_skills for this profile
            $stmt = $this->db->prepare("DELETE FROM profile_skills WHERE profile_id = (SELECT profile_id FROM rent_profiles WHERE id = ?)");
            $stmt->execute([$id]);

            // Delete rent_profile
            $stmt = $this->db->prepare("DELETE FROM rent_profiles WHERE id = ?");
            $stmt->execute([$id]);

            $this->db->commit();

            return ['success' => true, 'message' => 'Profile deleted successfully'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Error deleting profile: ' . $e->getMessage()];
        }
    }

    /**
     * Delete user and cascade all related data
     * @param int $id User ID
     * @return array Success/error response
     */
    public function deleteUser($id)
    {
        try {
            $this->db->beginTransaction();

            // Get all bounties by this user
            $stmt = $this->db->prepare("SELECT id FROM bounties WHERE user_id = ?");
            $stmt->execute([$id]);
            $bounties = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Delete applications for each bounty
            foreach ($bounties as $bounty) {
                $stmt = $this->db->prepare("DELETE FROM applications WHERE bounty_id = ?");
                $stmt->execute([$bounty['id']]);
            }

            // Delete all bounties by this user
            $stmt = $this->db->prepare("DELETE FROM bounties WHERE user_id = ?");
            $stmt->execute([$id]);

            // Get all profiles by this user
            $stmt = $this->db->prepare("SELECT profile_id FROM rent_profiles WHERE user_id = ?");
            $stmt->execute([$id]);
            $profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Delete applications and skills for each profile
            foreach ($profiles as $profile) {
                $stmt = $this->db->prepare("DELETE FROM applications WHERE profile_id = ?");
                $stmt->execute([$profile['profile_id']]);

                $stmt = $this->db->prepare("DELETE FROM profile_skills WHERE profile_id = ?");
                $stmt->execute([$profile['profile_id']]);
            }

            // Delete all profiles by this user
            $stmt = $this->db->prepare("DELETE FROM rent_profiles WHERE user_id = ?");
            $stmt->execute([$id]);

            // Delete user
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);

            $this->db->commit();

            return ['success' => true, 'message' => 'User deleted successfully'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Error deleting user: ' . $e->getMessage()];
        }
    }

    /**
     * Delete category
     * @param int $id Category ID
     * @return array Success/error response
     */
    public function deleteCategory($id)
    {
        try {
            // Check if category has bounties
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM bounties WHERE category_id = ?");
            $stmt->execute([$id]);
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            if ($count > 0) {
                return ['success' => false, 'message' => 'Cannot delete category with existing bounties'];
            }

            // Check if category has skills
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM skills WHERE category_id = ?");
            $stmt->execute([$id]);
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            if ($count > 0) {
                return ['success' => false, 'message' => 'Cannot delete category with existing skills'];
            }

            $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);

            return ['success' => true, 'message' => 'Category deleted successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error deleting category: ' . $e->getMessage()];
        }
    }

    /**
     * Delete skill
     * @param int $id Skill ID
     * @return array Success/error response
     */
    public function deleteSkill($id)
    {
        try {
            $this->db->beginTransaction();

            // Get skill slug
            $stmt = $this->db->prepare("SELECT slug FROM skills WHERE id = ?");
            $stmt->execute([$id]);
            $skill = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($skill) {
                // Delete profile_skills entries
                $stmt = $this->db->prepare("DELETE FROM profile_skills WHERE skill_slug = ?");
                $stmt->execute([$skill['slug']]);
            }

            // Delete skill
            $stmt = $this->db->prepare("DELETE FROM skills WHERE id = ?");
            $stmt->execute([$id]);

            $this->db->commit();

            return ['success' => true, 'message' => 'Skill deleted successfully'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Error deleting skill: ' . $e->getMessage()];
        }
    }

    /**
     * Delete application
     * @param int $id Application ID
     * @return array Success/error response
     */
    public function deleteApplication($id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM applications WHERE id = ?");
            $stmt->execute([$id]);

            return ['success' => true, 'message' => 'Application deleted successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error deleting application: ' . $e->getMessage()];
        }
    }
}
