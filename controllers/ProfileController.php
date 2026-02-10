<?php

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';

class ProfileController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * List profiles with filtering
     * @param array $filters Filters: skills, hourly_rate_min, hourly_rate_max, available
     * @return array List of profiles with related data
     */
    public function index($filters = [])
    {
        try {
            $sql = "SELECT
                        p.*,
                        u.name as user_name,
                        u.email as user_email
                    FROM profiles p
                    LEFT JOIN users u ON p.user_id = u.id
                    WHERE 1=1";

            $params = [];

            // Apply filters
            if (isset($filters['available'])) {
                $sql .= " AND p.available = ?";
                $params[] = $filters['available'];
            }

            if (!empty($filters['hourly_rate_min'])) {
                $sql .= " AND p.hourly_rate >= ?";
                $params[] = $filters['hourly_rate_min'];
            }

            if (!empty($filters['hourly_rate_max'])) {
                $sql .= " AND p.hourly_rate <= ?";
                $params[] = $filters['hourly_rate_max'];
            }

            // Filter by skills if provided
            if (!empty($filters['skills']) && is_array($filters['skills'])) {
                $placeholders = implode(',', array_fill(0, count($filters['skills']), '?'));
                $sql .= " AND p.id IN (
                    SELECT profile_id FROM profile_skills
                    WHERE skill_id IN ($placeholders)
                )";
                $params = array_merge($params, $filters['skills']);
            }

            $sql .= " ORDER BY p.created_at DESC";

            $profiles = $this->db->query($sql, $params);

            // Fetch skills for each profile
            foreach ($profiles as &$profile) {
                $profile['skills'] = $this->getProfileSkills($profile['id']);
            }

            return $profiles;
        } catch (Exception $e) {
            throw new Exception("Failed to fetch profiles: " . $e->getMessage());
        }
    }

    /**
     * Get single profile with all details
     * @param int $id Profile ID
     * @return array|false Profile data or false
     */
    public function show($id)
    {
        try {
            $sql = "SELECT
                        p.*,
                        u.name as user_name,
                        u.email as user_email,
                        u.id as user_id
                    FROM profiles p
                    LEFT JOIN users u ON p.user_id = u.id
                    WHERE p.id = ?";

            $profile = $this->db->queryOne($sql, [$id]);

            if (!$profile) {
                return false;
            }

            // Fetch associated skills
            $profile['skills'] = $this->getProfileSkills($id);

            return $profile;
        } catch (Exception $e) {
            throw new Exception("Failed to fetch profile: " . $e->getMessage());
        }
    }

    /**
     * Create new profile (requires authentication)
     * @param array $data Profile data
     * @return string Created profile ID
     */
    public function create($data)
    {
        try {
            // Validate authentication
            if (!Auth::check()) {
                throw new Exception("Authentication required");
            }

            $userId = Auth::id();

            // Check if user already has a profile
            $existing = $this->db->queryOne("SELECT id FROM profiles WHERE user_id = ?", [$userId]);
            if ($existing) {
                throw new Exception("User already has a profile");
            }

            $this->db->beginTransaction();

            // Generate unique profile_id
            $profileId = $this->generateProfileId();

            // Prepare profile data
            $profileData = [
                'user_id' => $userId,
                'profile_id' => $profileId,
                'bio' => $data['bio'] ?? null,
                'hourly_rate' => $data['hourly_rate'] ?? null,
                'available' => isset($data['available']) ? (int)$data['available'] : 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Insert profile
            $columns = implode(', ', array_keys($profileData));
            $placeholders = implode(', ', array_fill(0, count($profileData), '?'));
            $sql = "INSERT INTO profiles ($columns) VALUES ($placeholders)";

            $this->db->execute($sql, array_values($profileData));
            $newProfileId = $this->db->lastInsertId();

            // Associate skills if provided
            if (!empty($data['skills']) && is_array($data['skills'])) {
                $this->associateProfileSkills($newProfileId, $data['skills']);
            }

            $this->db->commit();

            return $newProfileId;
        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception("Failed to create profile: " . $e->getMessage());
        }
    }

    /**
     * Update profile (requires authentication and ownership)
     * @param int $id Profile ID
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

            // Validate ownership
            $profile = $this->db->queryOne("SELECT user_id FROM profiles WHERE id = ?", [$id]);
            if (!$profile) {
                throw new Exception("Profile not found");
            }

            if ($profile['user_id'] != Auth::id()) {
                throw new Exception("Permission denied: You don't own this profile");
            }

            $this->db->beginTransaction();

            // Prepare update data
            $updateFields = [];
            $params = [];

            $allowedFields = ['bio', 'hourly_rate', 'available'];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateFields[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }

            // Always update updated_at
            $updateFields[] = "updated_at = ?";
            $params[] = date('Y-m-d H:i:s');
            $params[] = $id;

            // Update profile
            $sql = "UPDATE profiles SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $affectedRows = $this->db->execute($sql, $params);

            // Update skills if provided
            if (isset($data['skills']) && is_array($data['skills'])) {
                // Delete existing skills
                $this->db->execute("DELETE FROM profile_skills WHERE profile_id = ?", [$id]);

                // Insert new skills
                if (!empty($data['skills'])) {
                    $this->associateProfileSkills($id, $data['skills']);
                }
            }

            $this->db->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception("Failed to update profile: " . $e->getMessage());
        }
    }

    /**
     * Delete profile (requires authentication and ownership)
     * @param int $id Profile ID
     * @return int Number of affected rows
     */
    public function delete($id)
    {
        try {
            // Validate authentication
            if (!Auth::check()) {
                throw new Exception("Authentication required");
            }

            // Validate ownership
            $profile = $this->db->queryOne("SELECT user_id FROM profiles WHERE id = ?", [$id]);
            if (!$profile) {
                throw new Exception("Profile not found");
            }

            if ($profile['user_id'] != Auth::id()) {
                throw new Exception("Permission denied: You don't own this profile");
            }

            // Delete profile (cascade will handle related records)
            $sql = "DELETE FROM profiles WHERE id = ?";
            return $this->db->execute($sql, [$id]);
        } catch (Exception $e) {
            throw new Exception("Failed to delete profile: " . $e->getMessage());
        }
    }

    /**
     * Get profile for a user
     * @param int $userId User ID
     * @return array|false Profile data or false
     */
    public function getUserProfile($userId)
    {
        try {
            $sql = "SELECT
                        p.*,
                        u.name as user_name,
                        u.email as user_email
                    FROM profiles p
                    LEFT JOIN users u ON p.user_id = u.id
                    WHERE p.user_id = ?";

            $profile = $this->db->queryOne($sql, [$userId]);

            if (!$profile) {
                return false;
            }

            // Fetch associated skills
            $profile['skills'] = $this->getProfileSkills($profile['id']);

            return $profile;
        } catch (Exception $e) {
            throw new Exception("Failed to fetch user profile: " . $e->getMessage());
        }
    }

    /**
     * Get skills for a profile
     * @param int $profileId Profile ID
     * @return array List of skills with proficiency
     */
    private function getProfileSkills($profileId)
    {
        try {
            $sql = "SELECT s.id, s.name, s.slug, s.category_id, ps.proficiency_level
                    FROM skills s
                    INNER JOIN profile_skills ps ON s.id = ps.skill_id
                    WHERE ps.profile_id = ?";

            return $this->db->query($sql, [$profileId]);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Associate skills with a profile
     * @param int $profileId Profile ID
     * @param array $skills Array of skill data (id and optional proficiency_level)
     */
    private function associateProfileSkills($profileId, $skills)
    {
        try {
            foreach ($skills as $skill) {
                // Handle both array format and simple ID format
                if (is_array($skill)) {
                    $skillId = $skill['id'] ?? $skill['skill_id'] ?? null;
                    $proficiency = $skill['proficiency_level'] ?? 'intermediate';
                } else {
                    $skillId = $skill;
                    $proficiency = 'intermediate';
                }

                if (!$skillId) {
                    continue;
                }

                $sql = "INSERT INTO profile_skills (profile_id, skill_id, proficiency_level, created_at)
                        VALUES (?, ?, ?, ?)";
                $this->db->execute($sql, [$profileId, $skillId, $proficiency, date('Y-m-d H:i:s')]);
            }
        } catch (Exception $e) {
            throw new Exception("Failed to associate skills: " . $e->getMessage());
        }
    }

    /**
     * Generate unique profile ID
     * @return string Unique profile ID
     */
    private function generateProfileId()
    {
        do {
            $profileId = 'P' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 7));
            $existing = $this->db->queryOne("SELECT id FROM profiles WHERE profile_id = ?", [$profileId]);
        } while ($existing);

        return $profileId;
    }
}
