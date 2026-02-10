<?php

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';

class VoteController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Cast vote for a profile on a bounty
     * @param int $bountyId Bounty ID
     * @param int $profileId Profile ID
     * @return string Created vote ID
     */
    public function vote($bountyId, $profileId)
    {
        try {
            // Validate authentication
            if (!Auth::check()) {
                throw new Exception("Authentication required");
            }

            $userId = Auth::id();

            // Validate bounty exists
            $bounty = $this->db->queryOne("SELECT id, status FROM bounties WHERE id = ?", [$bountyId]);
            if (!$bounty) {
                throw new Exception("Bounty not found");
            }

            // Validate profile exists
            $profile = $this->db->queryOne("SELECT id FROM profiles WHERE id = ?", [$profileId]);
            if (!$profile) {
                throw new Exception("Profile not found");
            }

            // Check if already voted
            $existing = $this->db->queryOne(
                "SELECT id FROM votes WHERE bounty_id = ? AND profile_id = ? AND voter_user_id = ?",
                [$bountyId, $profileId, $userId]
            );

            if ($existing) {
                throw new Exception("You have already voted for this profile on this bounty");
            }

            // Create vote
            $voteData = [
                'bounty_id' => $bountyId,
                'profile_id' => $profileId,
                'voter_user_id' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $columns = implode(', ', array_keys($voteData));
            $placeholders = implode(', ', array_fill(0, count($voteData), '?'));
            $sql = "INSERT INTO votes ($columns) VALUES ($placeholders)";

            $this->db->execute($sql, array_values($voteData));

            return $this->db->lastInsertId();
        } catch (Exception $e) {
            throw new Exception("Failed to cast vote: " . $e->getMessage());
        }
    }

    /**
     * Remove vote for a profile on a bounty
     * @param int $bountyId Bounty ID
     * @param int $profileId Profile ID
     * @return int Number of affected rows
     */
    public function unvote($bountyId, $profileId)
    {
        try {
            // Validate authentication
            if (!Auth::check()) {
                throw new Exception("Authentication required");
            }

            $userId = Auth::id();

            // Delete vote
            $sql = "DELETE FROM votes WHERE bounty_id = ? AND profile_id = ? AND voter_user_id = ?";
            $affectedRows = $this->db->execute($sql, [$bountyId, $profileId, $userId]);

            if ($affectedRows === 0) {
                throw new Exception("Vote not found");
            }

            return $affectedRows;
        } catch (Exception $e) {
            throw new Exception("Failed to remove vote: " . $e->getMessage());
        }
    }

    /**
     * Get vote count for a profile on a bounty
     * @param int $bountyId Bounty ID
     * @param int $profileId Profile ID
     * @return int Vote count
     */
    public function getVoteCount($bountyId, $profileId)
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM votes WHERE bounty_id = ? AND profile_id = ?";
            $result = $this->db->queryOne($sql, [$bountyId, $profileId]);

            return (int) $result['count'];
        } catch (Exception $e) {
            throw new Exception("Failed to get vote count: " . $e->getMessage());
        }
    }

    /**
     * Check if current user has voted for a profile on a bounty
     * @param int $bountyId Bounty ID
     * @param int $profileId Profile ID
     * @return bool True if voted, false otherwise
     */
    public function hasVoted($bountyId, $profileId)
    {
        try {
            // Return false if not authenticated
            if (!Auth::check()) {
                return false;
            }

            $userId = Auth::id();

            $sql = "SELECT COUNT(*) as count FROM votes
                    WHERE bounty_id = ? AND profile_id = ? AND voter_user_id = ?";
            $result = $this->db->queryOne($sql, [$bountyId, $profileId, $userId]);

            return (int) $result['count'] > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get all votes for a bounty grouped by profile
     * @param int $bountyId Bounty ID
     * @return array List of profiles with vote counts
     */
    public function getBountyVotes($bountyId)
    {
        try {
            $sql = "SELECT
                        p.id as profile_id,
                        p.profile_id as profile_public_id,
                        p.bio,
                        p.hourly_rate,
                        u.name as user_name,
                        COUNT(v.id) as vote_count
                    FROM votes v
                    INNER JOIN profiles p ON v.profile_id = p.id
                    INNER JOIN users u ON p.user_id = u.id
                    WHERE v.bounty_id = ?
                    GROUP BY p.id, p.profile_id, p.bio, p.hourly_rate, u.name
                    ORDER BY vote_count DESC, p.created_at ASC";

            return $this->db->query($sql, [$bountyId]);
        } catch (Exception $e) {
            throw new Exception("Failed to get bounty votes: " . $e->getMessage());
        }
    }

    /**
     * Get all votes cast by a user
     * @param int $userId User ID (optional, defaults to current user)
     * @return array List of votes
     */
    public function getUserVotes($userId = null)
    {
        try {
            // Use current user if not specified
            if ($userId === null) {
                if (!Auth::check()) {
                    throw new Exception("Authentication required");
                }
                $userId = Auth::id();
            }

            $sql = "SELECT
                        v.*,
                        b.title as bounty_title,
                        p.profile_id as profile_public_id,
                        u.name as profile_user_name
                    FROM votes v
                    INNER JOIN bounties b ON v.bounty_id = b.id
                    INNER JOIN profiles p ON v.profile_id = p.id
                    INNER JOIN users u ON p.user_id = u.id
                    WHERE v.voter_user_id = ?
                    ORDER BY v.created_at DESC";

            return $this->db->query($sql, [$userId]);
        } catch (Exception $e) {
            throw new Exception("Failed to get user votes: " . $e->getMessage());
        }
    }

    /**
     * Get top voted profiles for a bounty
     * @param int $bountyId Bounty ID
     * @param int $limit Number of results to return
     * @return array List of top profiles
     */
    public function getTopVoted($bountyId, $limit = 10)
    {
        try {
            $sql = "SELECT
                        p.id as profile_id,
                        p.profile_id as profile_public_id,
                        p.bio,
                        p.hourly_rate,
                        p.available,
                        u.id as user_id,
                        u.name as user_name,
                        u.email as user_email,
                        COUNT(v.id) as vote_count
                    FROM profiles p
                    INNER JOIN users u ON p.user_id = u.id
                    LEFT JOIN votes v ON p.id = v.profile_id AND v.bounty_id = ?
                    GROUP BY p.id, p.profile_id, p.bio, p.hourly_rate, p.available,
                             u.id, u.name, u.email
                    HAVING vote_count > 0
                    ORDER BY vote_count DESC, p.created_at ASC
                    LIMIT ?";

            $profiles = $this->db->query($sql, [$bountyId, $limit]);

            // Get skills for each profile
            foreach ($profiles as &$profile) {
                $profile['skills'] = $this->getProfileSkills($profile['profile_id']);
            }

            return $profiles;
        } catch (Exception $e) {
            throw new Exception("Failed to get top voted profiles: " . $e->getMessage());
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
