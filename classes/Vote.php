<?php

require_once __DIR__ . '/Model.php';

class Vote extends Model
{
    protected $table = 'votes';
    protected $fillable = ['bounty_id', 'profile_id', 'voter_user_id'];

    /**
     * Get the bounty for this vote
     * @param int $voteId
     * @return array|false
     */
    public function bounty($voteId)
    {
        $vote = $this->find($voteId);
        if (!$vote || !isset($vote['bounty_id'])) {
            return false;
        }

        $sql = "SELECT * FROM bounties WHERE id = ?";
        return $this->db->queryOne($sql, [$vote['bounty_id']]);
    }

    /**
     * Get the profile for this vote
     * @param int $voteId
     * @return array|false
     */
    public function profile($voteId)
    {
        $vote = $this->find($voteId);
        if (!$vote || !isset($vote['profile_id'])) {
            return false;
        }

        $sql = "SELECT * FROM profiles WHERE id = ?";
        return $this->db->queryOne($sql, [$vote['profile_id']]);
    }

    /**
     * Check if a user has already voted for a profile on a bounty
     * @param int $bountyId
     * @param int $profileId
     * @param int $voterUserId
     * @return bool
     */
    public static function hasVoted($bountyId, $profileId, $voterUserId)
    {
        $vote = new self();
        $sql = "SELECT COUNT(*) as count FROM votes
                WHERE bounty_id = ? AND profile_id = ? AND voter_user_id = ?";
        $result = $vote->db->queryOne($sql, [$bountyId, $profileId, $voterUserId]);
        return (int) $result['count'] > 0;
    }

    /**
     * Create a vote for a profile on a bounty
     * @param int $bountyId
     * @param int $profileId
     * @param int $voterUserId
     * @return string|false Last insert ID or false if already voted
     */
    public static function vote($bountyId, $profileId, $voterUserId)
    {
        // Check if already voted
        if (self::hasVoted($bountyId, $profileId, $voterUserId)) {
            return false;
        }

        $vote = new self();
        return $vote->create([
            'bounty_id' => $bountyId,
            'profile_id' => $profileId,
            'voter_user_id' => $voterUserId
        ]);
    }

    /**
     * Remove a vote
     * @param int $bountyId
     * @param int $profileId
     * @param int $voterUserId
     * @return int Number of affected rows
     */
    public static function removeVote($bountyId, $profileId, $voterUserId)
    {
        $vote = new self();
        $sql = "DELETE FROM votes
                WHERE bounty_id = ? AND profile_id = ? AND voter_user_id = ?";
        return $vote->db->execute($sql, [$bountyId, $profileId, $voterUserId]);
    }

    /**
     * Get vote count for a profile on a specific bounty
     * @param int $bountyId
     * @param int $profileId
     * @return int
     */
    public static function getVoteCount($bountyId, $profileId)
    {
        $vote = new self();
        $sql = "SELECT COUNT(*) as count FROM votes
                WHERE bounty_id = ? AND profile_id = ?";
        $result = $vote->db->queryOne($sql, [$bountyId, $profileId]);
        return (int) $result['count'];
    }
}
