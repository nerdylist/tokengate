<?php

require_once __DIR__ . '/../classes/Database.php';

class SearchController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Search across bounties, profiles, and guilds
     * @param string $query Search term
     * @return array Results grouped by type (bounties, profiles)
     */
    public function search($query = '')
    {
        try {
            $results = [
                'bounties' => [],
                'profiles' => []
            ];

            if (empty($query)) {
                return $results;
            }

            // Search bounties
            $results['bounties'] = $this->searchBounties($query);

            // Search profiles
            $results['profiles'] = $this->searchProfiles($query);

            return $results;
        } catch (Exception $e) {
            throw new Exception("Failed to perform search: " . $e->getMessage());
        }
    }

    /**
     * Search bounties by title, description, and skills
     * @param string $query Search term
     * @return array List of bounties
     */
    private function searchBounties($query)
    {
        try {
            $searchTerm = '%' . $query . '%';

            $sql = "SELECT DISTINCT
                        b.*,
                        c.name as category_name,
                        c.slug as category_slug,
                        u.name as user_name,
                        u.email as user_email,
                        (SELECT COUNT(*) FROM applications WHERE bounty_id = b.id) as application_count
                    FROM bounties b
                    LEFT JOIN categories c ON b.category_id = c.id
                    LEFT JOIN users u ON b.user_id = u.id
                    LEFT JOIN bounty_skills bs ON b.id = bs.bounty_id
                    LEFT JOIN skills s ON bs.skill_id = s.id
                    WHERE (
                        b.title LIKE ? COLLATE NOCASE
                        OR b.description LIKE ? COLLATE NOCASE
                        OR s.name LIKE ? COLLATE NOCASE
                    )
                    AND b.status = 'open'
                    ORDER BY b.created_at DESC";

            $bounties = $this->db->query($sql, [$searchTerm, $searchTerm, $searchTerm]);

            // Fetch skills for each bounty
            foreach ($bounties as &$bounty) {
                $bounty['skills'] = $this->getBountySkills($bounty['id']);
            }

            return $bounties;
        } catch (Exception $e) {
            error_log("Search bounties error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Search profiles by name, bio, and skills
     * @param string $query Search term
     * @return array List of profiles
     */
    private function searchProfiles($query)
    {
        try {
            $searchTerm = '%' . $query . '%';

            $sql = "SELECT DISTINCT
                        p.*,
                        u.name as user_name,
                        u.email as user_email
                    FROM profiles p
                    LEFT JOIN users u ON p.user_id = u.id
                    LEFT JOIN profile_skills ps ON p.id = ps.profile_id
                    LEFT JOIN skills s ON ps.skill_id = s.id
                    WHERE (
                        u.name LIKE ? COLLATE NOCASE
                        OR p.bio LIKE ? COLLATE NOCASE
                        OR s.name LIKE ? COLLATE NOCASE
                    )
                    AND p.available = 1
                    ORDER BY p.created_at DESC";

            $profiles = $this->db->query($sql, [$searchTerm, $searchTerm, $searchTerm]);

            // Fetch skills for each profile
            foreach ($profiles as &$profile) {
                $profile['skills'] = $this->getProfileSkills($profile['id']);
            }

            return $profiles;
        } catch (Exception $e) {
            error_log("Search profiles error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get skills for a bounty
     * @param int $bountyId Bounty ID
     * @return array List of skills
     */
    private function getBountySkills($bountyId)
    {
        try {
            $sql = "SELECT s.id, s.name, s.slug, s.category_id
                    FROM skills s
                    INNER JOIN bounty_skills bs ON s.id = bs.skill_id
                    WHERE bs.bounty_id = ?";

            return $this->db->query($sql, [$bountyId]);
        } catch (Exception $e) {
            return [];
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
}
