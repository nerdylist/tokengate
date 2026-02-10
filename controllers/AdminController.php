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
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM profiles");
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

    /**
     * Delete guild
     * @param int $id Guild ID
     * @return array Success/error response
     */
    public function deleteGuild($id)
    {
        try {
            // Check if guild has profile_guilds (members)
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM profile_guilds WHERE guild_id = ?");
            $stmt->execute([$id]);
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            if ($count > 0) {
                return ['success' => false, 'message' => 'Cannot delete guild with existing members'];
            }

            // Check if guild has quests
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM quests WHERE guild_id = ?");
            $stmt->execute([$id]);
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            if ($count > 0) {
                return ['success' => false, 'message' => 'Cannot delete guild with existing quests'];
            }

            $stmt = $this->db->prepare("DELETE FROM guilds WHERE id = ?");
            $stmt->execute([$id]);

            return ['success' => true, 'message' => 'Guild deleted successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error deleting guild: ' . $e->getMessage()];
        }
    }

    /**
     * Delete rank
     * @param int $id Rank ID
     * @return array Success/error response
     */
    public function deleteRank($id)
    {
        try {
            // Check if rank is used in profile_guilds
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM profile_guilds WHERE rank_id = ?");
            $stmt->execute([$id]);
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            if ($count > 0) {
                return ['success' => false, 'message' => 'Cannot delete rank that is assigned to profiles'];
            }

            // Check if rank is used as min_rank_id in quests
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM quests WHERE min_rank_id = ?");
            $stmt->execute([$id]);
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            if ($count > 0) {
                return ['success' => false, 'message' => 'Cannot delete rank that is required by quests'];
            }

            $stmt = $this->db->prepare("DELETE FROM ranks WHERE id = ?");
            $stmt->execute([$id]);

            return ['success' => true, 'message' => 'Rank deleted successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error deleting rank: ' . $e->getMessage()];
        }
    }

    /**
     * Approve pending skill
     * @param int $skillId Skill ID
     * @param int $adminId Admin user ID who approved it
     * @return array Success/error response
     */
    public function approvePendingSkill($skillId, $adminId)
    {
        try {
            $this->db->beginTransaction();

            // Get skill data
            $stmt = $this->db->prepare("SELECT id, name, slug, category_id, submitted_by_profile_id, status FROM skills WHERE id = ? AND status = 'pending'");
            $stmt->execute([$skillId]);
            $skill = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$skill) {
                throw new Exception('Pending skill not found or already processed');
            }

            // Update skill status to approved
            $stmt = $this->db->prepare("UPDATE skills SET status = 'approved', reviewed_at = CURRENT_TIMESTAMP, reviewed_by_admin_id = ? WHERE id = ?");
            $stmt->execute([$adminId, $skillId]);

            // Add to requester's profile_skills if they submitted it
            if ($skill['submitted_by_profile_id']) {
                // Check if already in profile_skills
                $stmt = $this->db->prepare("SELECT 1 FROM profile_skills WHERE profile_id = ? AND skill_id = ?");
                $stmt->execute([$skill['submitted_by_profile_id'], $skillId]);
                $exists = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$exists) {
                    $stmt = $this->db->prepare("INSERT INTO profile_skills (profile_id, skill_id, proficiency_level) VALUES (?, ?, 'intermediate')");
                    $stmt->execute([$skill['submitted_by_profile_id'], $skillId]);
                }
            }

            $this->db->commit();

            return ['success' => true, 'message' => 'Skill approved and added to system'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Error approving skill: ' . $e->getMessage()];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Reject pending skill
     * @param int $skillId Skill ID
     * @param int $adminId Admin user ID who rejected it
     * @return array Success/error response
     */
    public function rejectPendingSkill($skillId, $adminId)
    {
        try {
            $stmt = $this->db->prepare("UPDATE skills SET status = 'rejected', reviewed_at = CURRENT_TIMESTAMP, reviewed_by_admin_id = ? WHERE id = ? AND status = 'pending'");
            $stmt->execute([$adminId, $skillId]);

            if ($stmt->rowCount() === 0) {
                throw new Exception('Pending skill not found or already processed');
            }

            return ['success' => true, 'message' => 'Skill request rejected'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error rejecting skill: ' . $e->getMessage()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Create profile status
     * @param array $data Status data
     * @return array Success/error response
     */
    public function createProfileStatus($data)
    {
        try {
            // Validate color format
            if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $data['color'])) {
                return ['success' => false, 'message' => 'Invalid color format. Use hex color code (e.g., #10b981)'];
            }

            // Validate slug format
            if (!preg_match('/^[a-z0-9-]+$/', $data['slug'])) {
                return ['success' => false, 'message' => 'Invalid slug format. Use lowercase letters, numbers, and hyphens only'];
            }

            // Check if slug already exists
            $stmt = $this->db->prepare("SELECT id FROM profile_statuses WHERE slug = ?");
            $stmt->execute([$data['slug']]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'A status with this slug already exists'];
            }

            // Validate icon format if provided
            if (!empty($data['icon'])) {
                if (!$this->validateIcon($data['icon'])) {
                    return ['success' => false, 'message' => 'Invalid icon format'];
                }
            }

            $stmt = $this->db->prepare("INSERT INTO profile_statuses (name, slug, color, icon, sort_order, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
            $stmt->execute([
                $data['name'],
                $data['slug'],
                $data['color'],
                $data['icon'] ?? null,
                $data['sort_order'],
                $data['is_active']
            ]);

            return ['success' => true, 'message' => 'Profile status created successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error creating profile status: ' . $e->getMessage()];
        }
    }

    /**
     * Update profile status
     * @param array $data Status data
     * @return array Success/error response
     */
    public function updateProfileStatus($data)
    {
        try {
            // Validate color format
            if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $data['color'])) {
                return ['success' => false, 'message' => 'Invalid color format. Use hex color code (e.g., #10b981)'];
            }

            // Validate slug format
            if (!preg_match('/^[a-z0-9-]+$/', $data['slug'])) {
                return ['success' => false, 'message' => 'Invalid slug format. Use lowercase letters, numbers, and hyphens only'];
            }

            // Check if slug already exists (excluding current record)
            $stmt = $this->db->prepare("SELECT id FROM profile_statuses WHERE slug = ? AND id != ?");
            $stmt->execute([$data['slug'], $data['id']]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'A status with this slug already exists'];
            }

            // Validate icon format if provided
            if (!empty($data['icon'])) {
                if (!$this->validateIcon($data['icon'])) {
                    return ['success' => false, 'message' => 'Invalid icon format'];
                }
            }

            $stmt = $this->db->prepare("UPDATE profile_statuses SET name = ?, slug = ?, color = ?, icon = ?, sort_order = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([
                $data['name'],
                $data['slug'],
                $data['color'],
                $data['icon'] ?? null,
                $data['sort_order'],
                $data['is_active'],
                $data['id']
            ]);

            return ['success' => true, 'message' => 'Profile status updated successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error updating profile status: ' . $e->getMessage()];
        }
    }

    /**
     * Toggle profile status active state
     * @param int $id Status ID
     * @param int $isActive Active state (0 or 1)
     * @return array Success/error response
     */
    public function toggleProfileStatus($id, $isActive)
    {
        try {
            $stmt = $this->db->prepare("UPDATE profile_statuses SET is_active = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$isActive, $id]);

            return ['success' => true, 'message' => 'Status updated successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error updating status: ' . $e->getMessage()];
        }
    }

    /**
     * Delete profile status
     * @param int $id Status ID
     * @return array Success/error response
     */
    public function deleteProfileStatus($id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM profile_statuses WHERE id = ?");
            $stmt->execute([$id]);

            return ['success' => true, 'message' => 'Profile status deleted successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error deleting profile status: ' . $e->getMessage()];
        }
    }

    /**
     * Update profile status icon only
     * @param int $id Status ID
     * @param string|null $icon Icon value (svg:name or emoji:value)
     * @return array Success/error response
     */
    public function updateProfileStatusIcon($id, $icon)
    {
        try {
            // Validate icon format if provided
            if (!empty($icon) && !$this->validateIcon($icon)) {
                return ['success' => false, 'message' => 'Invalid icon format'];
            }

            $stmt = $this->db->prepare("UPDATE profile_statuses SET icon = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$icon, $id]);

            return ['success' => true, 'message' => 'Icon updated successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error updating icon: ' . $e->getMessage()];
        }
    }

    /**
     * Validate icon format
     * @param string $icon Icon value
     * @return bool True if valid
     */
    private function validateIcon($icon)
    {
        if (empty($icon)) {
            return true;
        }

        // Check SVG icon format
        if (strpos($icon, 'svg:') === 0) {
            $iconName = substr($icon, 4);
            return preg_match('/^[a-z0-9-]+$/', $iconName);
        }

        // Check emoji format
        if (strpos($icon, 'emoji:') === 0) {
            $emojiValue = substr($icon, 6);

            // Count emojis using regex
            if (function_exists('mb_strlen')) {
                $length = mb_strlen($emojiValue, 'UTF-8');
                if ($length > 16) {
                    return false;
                }
            }

            // Basic emoji validation - check if contains valid Unicode emoji ranges
            return preg_match('/^[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{FE00}-\x{FE0F}\x{1F000}-\x{1F02F}\x{1F0A0}-\x{1F0FF}\x{1F100}-\x{1F64F}\x{1F680}-\x{1F6FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{200D}\x{20E3}\s]*$/u', $emojiValue);
        }

        return false;
    }
}
