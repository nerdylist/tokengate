<?php

require_once __DIR__ . '/Model.php';

class Profile extends Model
{
    protected $table = 'profiles';
    protected $fillable = ['user_id', 'profile_id', 'bio', 'hourly_rate', 'available', 'avatar_url', 'status_id'];

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

    /**
     * Get all guilds (categories) with skills, ranks, and XP for this profile
     * @param int $profileId
     * @return array Array of guilds with skills, proficiency levels, and XP
     */
    public function guilds($profileId)
    {
        $sql = "SELECT
                    c.id as guild_id,
                    c.name as guild_name,
                    c.slug as guild_slug,
                    s.id as skill_id,
                    s.name as skill_name,
                    ps.proficiency_level,
                    ps.xp,
                    ps.is_primary_guild
                FROM categories c
                INNER JOIN skills s ON c.id = s.category_id
                INNER JOIN profile_skills ps ON s.id = ps.skill_id
                WHERE ps.profile_id = ?
                ORDER BY c.name, s.name";

        $results = $this->db->query($sql, [$profileId]);

        // Group by guild
        $guilds = [];
        foreach ($results as $row) {
            $guildId = $row['guild_id'];
            if (!isset($guilds[$guildId])) {
                $guilds[$guildId] = [
                    'id' => $row['guild_id'],
                    'name' => $row['guild_name'],
                    'slug' => $row['guild_slug'],
                    'total_xp' => 0,
                    'is_primary' => false,
                    'skills' => []
                ];
            }

            $guilds[$guildId]['skills'][] = [
                'id' => $row['skill_id'],
                'name' => $row['skill_name'],
                'proficiency' => $row['proficiency_level'],
                'xp' => $row['xp']
            ];

            $guilds[$guildId]['total_xp'] += $row['xp'];

            if ($row['is_primary_guild'] == 1) {
                $guilds[$guildId]['is_primary'] = true;
            }
        }

        return array_values($guilds);
    }

    /**
     * Get the primary guild to display for this profile
     * @param int $profileId
     * @return array|false Primary guild data or false if none set
     */
    public function primaryGuild($profileId)
    {
        $guilds = $this->guilds($profileId);

        foreach ($guilds as $guild) {
            if ($guild['is_primary']) {
                // Calculate rank based on total XP
                $guild['rank'] = $this->calculateRank($guild['total_xp']);
                return $guild;
            }
        }

        // If no primary guild set, return the guild with most XP
        if (!empty($guilds)) {
            usort($guilds, function($a, $b) {
                return $b['total_xp'] - $a['total_xp'];
            });
            $guilds[0]['rank'] = $this->calculateRank($guilds[0]['total_xp']);
            return $guilds[0];
        }

        return false;
    }

    /**
     * Award XP to a specific skill (which contributes to guild XP)
     * @param int $profileId
     * @param int $skillId
     * @param int $xp Amount of XP to add
     * @return bool Success status
     */
    public function addGuildExperience($profileId, $skillId, $xp)
    {
        $sql = "UPDATE profile_skills
                SET xp = xp + ?, updated_at = ?
                WHERE profile_id = ? AND skill_id = ?";

        try {
            $this->db->execute($sql, [$xp, date('Y-m-d H:i:s'), $profileId, $skillId]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Calculate guild rank based on total XP
     * @param int $xp Total experience points
     * @return array Rank data with name, level, and color
     */
    public function calculateRank($xp)
    {
        $ranks = [
            ['name' => 'Initiate', 'level' => 1, 'min_xp' => 0, 'color' => '#71717a'],
            ['name' => 'Novice', 'level' => 2, 'min_xp' => 100, 'color' => '#71717a'],
            ['name' => 'Apprentice', 'level' => 3, 'min_xp' => 500, 'color' => '#ca8a04'],
            ['name' => 'Journeyman', 'level' => 4, 'min_xp' => 2000, 'color' => '#a1a1aa'],
            ['name' => 'Specialist', 'level' => 5, 'min_xp' => 5000, 'color' => '#a1a1aa'],
            ['name' => 'Expert', 'level' => 6, 'min_xp' => 10000, 'color' => '#eab308'],
            ['name' => 'Master', 'level' => 7, 'min_xp' => 25000, 'color' => '#eab308'],
            ['name' => 'Grandmaster', 'level' => 8, 'min_xp' => 50000, 'color' => '#a855f7'],
            ['name' => 'Officer', 'level' => 9, 'min_xp' => 100000, 'color' => '#a855f7'],
            ['name' => 'Guildmaster', 'level' => 10, 'min_xp' => 250000, 'color' => '#ef4444']
        ];

        $currentRank = $ranks[0];
        $nextRank = isset($ranks[1]) ? $ranks[1] : null;

        for ($i = count($ranks) - 1; $i >= 0; $i--) {
            if ($xp >= $ranks[$i]['min_xp']) {
                $currentRank = $ranks[$i];
                $nextRank = isset($ranks[$i + 1]) ? $ranks[$i + 1] : null;
                break;
            }
        }

        return [
            'name' => $currentRank['name'],
            'level' => $currentRank['level'],
            'color' => $currentRank['color'],
            'current_xp' => $xp,
            'min_xp' => $currentRank['min_xp'],
            'next_rank' => $nextRank ? $nextRank['name'] : 'Max Rank',
            'next_rank_xp' => $nextRank ? $nextRank['min_xp'] : $xp,
            'progress_percent' => $nextRank ?
                round((($xp - $currentRank['min_xp']) / ($nextRank['min_xp'] - $currentRank['min_xp'])) * 100) : 100
        ];
    }

    /**
     * Get the current status for a profile from profile_statuses table
     * @param int $profileId
     * @return array|false Status data or false if not found
     */
    public function getStatus($profileId)
    {
        $sql = "SELECT ps.id, ps.name, ps.slug, ps.color, ps.icon FROM profile_statuses ps
                INNER JOIN profiles p ON p.status_id = ps.id
                WHERE p.id = ?";
        return $this->db->queryOne($sql, [$profileId]);
    }

    /**
     * Get all active statuses from profile_statuses table
     * @return array List of active statuses ordered by sort_order
     */
    public function getAllActiveStatuses()
    {
        $sql = "SELECT * FROM profile_statuses WHERE is_active = 1 ORDER BY sort_order";
        return $this->db->query($sql);
    }
}
