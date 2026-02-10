<?php

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';

class ApplicationController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Submit application (requires authentication and profile)
     * @param array $data Application data (bounty_id, cover_letter, proposed_rate)
     * @return string Created application ID
     */
    public function create($data)
    {
        try {
            // Validate authentication
            if (!Auth::check()) {
                throw new Exception("Authentication required");
            }

            $userId = Auth::id();

            // Check if user has a profile
            $profile = $this->db->queryOne("SELECT id FROM profiles WHERE user_id = ?", [$userId]);
            if (!$profile) {
                throw new Exception("You must create a profile before applying to bounties");
            }

            // Validate required fields
            if (empty($data['bounty_id']) || empty($data['cover_letter'])) {
                throw new Exception("Missing required fields: bounty_id, cover_letter");
            }

            // Check if bounty exists and is open
            $bounty = $this->db->queryOne("SELECT id, status, user_id FROM bounties WHERE id = ?", [$data['bounty_id']]);
            if (!$bounty) {
                throw new Exception("Bounty not found");
            }

            if ($bounty['status'] !== 'open') {
                throw new Exception("This bounty is not accepting applications");
            }

            // Prevent user from applying to their own bounty
            if ($bounty['user_id'] == $userId) {
                throw new Exception("You cannot apply to your own bounty");
            }

            // Check if already applied
            $existing = $this->db->queryOne(
                "SELECT id FROM applications WHERE bounty_id = ? AND profile_id = ?",
                [$data['bounty_id'], $profile['id']]
            );

            if ($existing) {
                throw new Exception("You have already applied to this bounty");
            }

            // Create application
            $applicationData = [
                'bounty_id' => $data['bounty_id'],
                'profile_id' => $profile['id'],
                'cover_letter' => $data['cover_letter'],
                'proposed_rate' => $data['proposed_rate'] ?? null,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $columns = implode(', ', array_keys($applicationData));
            $placeholders = implode(', ', array_fill(0, count($applicationData), '?'));
            $sql = "INSERT INTO applications ($columns) VALUES ($placeholders)";

            $this->db->execute($sql, array_values($applicationData));

            return $this->db->lastInsertId();
        } catch (Exception $e) {
            throw new Exception("Failed to submit application: " . $e->getMessage());
        }
    }

    /**
     * Update application (requires authentication and ownership)
     * @param int $id Application ID
     * @param array $data Update data
     * @return int Number of affected rows
     */
    public function update($id, $data)
    {
        try {
            // Validate authentication
            if (!Auth::check()) {
                throw new Exception("Authentication required");
            }

            $userId = Auth::id();

            // Get application and validate ownership
            $application = $this->db->queryOne(
                "SELECT a.*, p.user_id FROM applications a
                 INNER JOIN profiles p ON a.profile_id = p.id
                 WHERE a.id = ?",
                [$id]
            );

            if (!$application) {
                throw new Exception("Application not found");
            }

            if ($application['user_id'] != $userId) {
                throw new Exception("Permission denied: You don't own this application");
            }

            // Can only update pending applications
            if ($application['status'] !== 'pending') {
                throw new Exception("Cannot update application with status: " . $application['status']);
            }

            // Prepare update data
            $updateFields = [];
            $params = [];

            $allowedFields = ['cover_letter', 'proposed_rate'];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateFields[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }

            if (empty($updateFields)) {
                throw new Exception("No valid fields to update");
            }

            // Always update updated_at
            $updateFields[] = "updated_at = ?";
            $params[] = date('Y-m-d H:i:s');
            $params[] = $id;

            // Update application
            $sql = "UPDATE applications SET " . implode(', ', $updateFields) . " WHERE id = ?";
            return $this->db->execute($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Failed to update application: " . $e->getMessage());
        }
    }

    /**
     * Accept application (requires authentication and bounty ownership)
     * @param int $id Application ID
     * @return int Number of affected rows
     */
    public function accept($id)
    {
        try {
            // Validate authentication
            if (!Auth::check()) {
                throw new Exception("Authentication required");
            }

            $userId = Auth::id();

            // Get application and bounty
            $application = $this->db->queryOne(
                "SELECT a.*, b.user_id as bounty_owner FROM applications a
                 INNER JOIN bounties b ON a.bounty_id = b.id
                 WHERE a.id = ?",
                [$id]
            );

            if (!$application) {
                throw new Exception("Application not found");
            }

            if ($application['bounty_owner'] != $userId) {
                throw new Exception("Permission denied: You don't own this bounty");
            }

            if ($application['status'] !== 'pending') {
                throw new Exception("Application is not pending");
            }

            $this->db->beginTransaction();

            // Accept the application
            $sql = "UPDATE applications SET status = ?, updated_at = ? WHERE id = ?";
            $this->db->execute($sql, ['accepted', date('Y-m-d H:i:s'), $id]);

            // Reject all other pending applications for this bounty
            $sql = "UPDATE applications SET status = ?, updated_at = ?
                    WHERE bounty_id = ? AND id != ? AND status = ?";
            $this->db->execute($sql, [
                'rejected',
                date('Y-m-d H:i:s'),
                $application['bounty_id'],
                $id,
                'pending'
            ]);

            // Update bounty status to in_progress
            $sql = "UPDATE bounties SET status = ?, updated_at = ? WHERE id = ?";
            $this->db->execute($sql, ['in_progress', date('Y-m-d H:i:s'), $application['bounty_id']]);

            $this->db->commit();

            return 1;
        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception("Failed to accept application: " . $e->getMessage());
        }
    }

    /**
     * Reject application (requires authentication and bounty ownership)
     * @param int $id Application ID
     * @return int Number of affected rows
     */
    public function reject($id)
    {
        try {
            // Validate authentication
            if (!Auth::check()) {
                throw new Exception("Authentication required");
            }

            $userId = Auth::id();

            // Get application and bounty
            $application = $this->db->queryOne(
                "SELECT a.*, b.user_id as bounty_owner FROM applications a
                 INNER JOIN bounties b ON a.bounty_id = b.id
                 WHERE a.id = ?",
                [$id]
            );

            if (!$application) {
                throw new Exception("Application not found");
            }

            if ($application['bounty_owner'] != $userId) {
                throw new Exception("Permission denied: You don't own this bounty");
            }

            if ($application['status'] !== 'pending') {
                throw new Exception("Application is not pending");
            }

            // Reject the application
            $sql = "UPDATE applications SET status = ?, updated_at = ? WHERE id = ?";
            return $this->db->execute($sql, ['rejected', date('Y-m-d H:i:s'), $id]);
        } catch (Exception $e) {
            throw new Exception("Failed to reject application: " . $e->getMessage());
        }
    }

    /**
     * Withdraw application (requires authentication and ownership)
     * @param int $id Application ID
     * @return int Number of affected rows
     */
    public function withdraw($id)
    {
        try {
            // Validate authentication
            if (!Auth::check()) {
                throw new Exception("Authentication required");
            }

            $userId = Auth::id();

            // Get application and validate ownership
            $application = $this->db->queryOne(
                "SELECT a.*, p.user_id FROM applications a
                 INNER JOIN profiles p ON a.profile_id = p.id
                 WHERE a.id = ?",
                [$id]
            );

            if (!$application) {
                throw new Exception("Application not found");
            }

            if ($application['user_id'] != $userId) {
                throw new Exception("Permission denied: You don't own this application");
            }

            if ($application['status'] !== 'pending') {
                throw new Exception("Can only withdraw pending applications");
            }

            // Withdraw the application
            $sql = "UPDATE applications SET status = ?, updated_at = ? WHERE id = ?";
            return $this->db->execute($sql, ['withdrawn', date('Y-m-d H:i:s'), $id]);
        } catch (Exception $e) {
            throw new Exception("Failed to withdraw application: " . $e->getMessage());
        }
    }

    /**
     * Get all applications for a bounty
     * @param int $bountyId Bounty ID
     * @return array List of applications
     */
    public function getForBounty($bountyId)
    {
        try {
            // Validate authentication
            if (!Auth::check()) {
                throw new Exception("Authentication required");
            }

            $userId = Auth::id();

            // Verify user owns the bounty
            $bounty = $this->db->queryOne("SELECT user_id FROM bounties WHERE id = ?", [$bountyId]);
            if (!$bounty) {
                throw new Exception("Bounty not found");
            }

            if ($bounty['user_id'] != $userId) {
                throw new Exception("Permission denied: You don't own this bounty");
            }

            // Get applications
            $sql = "SELECT
                        a.*,
                        p.profile_id,
                        p.bio,
                        p.hourly_rate,
                        u.name as applicant_name,
                        u.email as applicant_email
                    FROM applications a
                    INNER JOIN profiles p ON a.profile_id = p.id
                    INNER JOIN users u ON p.user_id = u.id
                    WHERE a.bounty_id = ?
                    ORDER BY a.created_at DESC";

            $applications = $this->db->query($sql, [$bountyId]);

            // Get skills for each applicant's profile
            foreach ($applications as &$application) {
                $application['skills'] = $this->getProfileSkills($application['profile_id']);
            }

            return $applications;
        } catch (Exception $e) {
            throw new Exception("Failed to fetch bounty applications: " . $e->getMessage());
        }
    }

    /**
     * Get all applications by a profile
     * @param int $profileId Profile ID
     * @return array List of applications
     */
    public function getForProfile($profileId)
    {
        try {
            // Validate authentication
            if (!Auth::check()) {
                throw new Exception("Authentication required");
            }

            $userId = Auth::id();

            // Verify user owns the profile
            $profile = $this->db->queryOne("SELECT user_id FROM profiles WHERE id = ?", [$profileId]);
            if (!$profile) {
                throw new Exception("Profile not found");
            }

            if ($profile['user_id'] != $userId) {
                throw new Exception("Permission denied: You don't own this profile");
            }

            // Get applications
            $sql = "SELECT
                        a.*,
                        b.title as bounty_title,
                        b.description as bounty_description,
                        b.budget_min,
                        b.budget_max,
                        b.deadline,
                        b.status as bounty_status,
                        c.name as category_name,
                        u.name as bounty_owner_name
                    FROM applications a
                    INNER JOIN bounties b ON a.bounty_id = b.id
                    INNER JOIN categories c ON b.category_id = c.id
                    INNER JOIN users u ON b.user_id = u.id
                    WHERE a.profile_id = ?
                    ORDER BY a.created_at DESC";

            return $this->db->query($sql, [$profileId]);
        } catch (Exception $e) {
            throw new Exception("Failed to fetch profile applications: " . $e->getMessage());
        }
    }

    /**
     * Get skills for a profile (internal use)
     * @param int $profileId Profile ID
     * @return array List of skills
     */
    private function getProfileSkills($profileId)
    {
        try {
            $sql = "SELECT s.id, s.name, s.slug, ps.proficiency_level
                    FROM skills s
                    INNER JOIN profile_skills ps ON s.id = ps.skill_id
                    WHERE ps.profile_id = ?";

            return $this->db->query($sql, [$profileId]);
        } catch (Exception $e) {
            return [];
        }
    }
}
