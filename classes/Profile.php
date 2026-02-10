<?php

require_once __DIR__ . '/Model.php';

class Profile extends Model
{
    protected $table = 'profiles';
    protected $fillable = ['user_id', 'profile_id', 'bio', 'hourly_rate', 'available'];

    /**
     * Get the user for this profile
     * @param int $profileId
     * @return array|false
     */
    public function user($profileId)
    {
        $profile = $this->find($profileId);
        if (!$profile || !isset($profile['user_id'])) {
            return false;
        }

        $sql = "SELECT * FROM users WHERE id = ?";
        return $this->db->queryOne($sql, [$profile['user_id']]);
    }

    /**
     * Get all skills for this profile with proficiency levels
     * @param int $profileId
     * @return array
     */
    public function skills($profileId)
    {
        $sql = "SELECT s.*, ps.proficiency_level
                FROM skills s
                INNER JOIN profile_skills ps ON s.id = ps.skill_id
                WHERE ps.profile_id = ?
                ORDER BY s.name";
        return $this->db->query($sql, [$profileId]);
    }

    /**
     * Get all applications for this profile
     * @param int $profileId
     * @return array
     */
    public function applications($profileId)
    {
        $sql = "SELECT * FROM applications WHERE profile_id = ? ORDER BY created_at DESC";
        return $this->db->query($sql, [$profileId]);
    }

    /**
     * Add a skill to this profile with proficiency level
     * @param int $profileId
     * @param int $skillId
     * @param string $proficiencyLevel (beginner, intermediate, advanced, expert)
     * @return bool
     */
    public function addSkill($profileId, $skillId, $proficiencyLevel = 'intermediate')
    {
        try {
            $sql = "INSERT INTO profile_skills (profile_id, skill_id, proficiency_level, created_at)
                    VALUES (?, ?, ?, ?)";
            $this->db->execute($sql, [$profileId, $skillId, $proficiencyLevel, date('Y-m-d H:i:s')]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Remove a skill from this profile
     * @param int $profileId
     * @param int $skillId
     * @return int Number of affected rows
     */
    public function removeSkill($profileId, $skillId)
    {
        $sql = "DELETE FROM profile_skills WHERE profile_id = ? AND skill_id = ?";
        return $this->db->execute($sql, [$profileId, $skillId]);
    }

    /**
     * Generate a unique profile ID in format ABC-1234
     * @return string
     */
    public static function generateProfileId()
    {
        $profile = new self();
        $maxAttempts = 100;
        $attempts = 0;

        do {
            // Generate 3 random uppercase letters
            $letters = '';
            for ($i = 0; $i < 3; $i++) {
                $letters .= chr(rand(65, 90)); // A-Z
            }

            // Generate 4 random digits
            $digits = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

            $profileId = $letters . '-' . $digits;

            // Check if this ID already exists
            $existing = $profile->where('profile_id', $profileId)->first();

            if (!$existing) {
                return $profileId;
            }

            $attempts++;
        } while ($attempts < $maxAttempts);

        throw new Exception('Unable to generate unique profile ID');
    }

    /**
     * Search profiles with filters
     * @param string $query Search term for bio
     * @param array $filters Optional filters (skills, hourly_rate_min, hourly_rate_max, available)
     * @return array
     */
    public static function search($query = '', $filters = [])
    {
        $profile = new self();

        $sql = "SELECT DISTINCT p.* FROM profiles p";
        $params = [];
        $whereClauses = [];

        // Join with profile_skills if filtering by skills
        if (!empty($filters['skills']) && is_array($filters['skills'])) {
            $sql .= " INNER JOIN profile_skills ps ON p.id = ps.profile_id";
            $placeholders = implode(',', array_fill(0, count($filters['skills']), '?'));
            $whereClauses[] = "ps.skill_id IN ($placeholders)";
            $params = array_merge($params, $filters['skills']);
        }

        // Search bio
        if (!empty($query)) {
            $whereClauses[] = "p.bio LIKE ?";
            $params[] = '%' . $query . '%';
        }

        // Filter by hourly rate range
        if (isset($filters['hourly_rate_min'])) {
            $whereClauses[] = "p.hourly_rate >= ?";
            $params[] = $filters['hourly_rate_min'];
        }

        if (isset($filters['hourly_rate_max'])) {
            $whereClauses[] = "p.hourly_rate <= ?";
            $params[] = $filters['hourly_rate_max'];
        }

        // Filter by availability
        if (isset($filters['available'])) {
            $whereClauses[] = "p.available = ?";
            $params[] = $filters['available'] ? 1 : 0;
        }

        // Build WHERE clause
        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        $sql .= " ORDER BY p.created_at DESC";

        return $profile->db->query($sql, $params);
    }
}
