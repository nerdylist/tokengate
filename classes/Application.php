<?php

require_once __DIR__ . '/Model.php';

class Application extends Model
{
    protected $table = 'applications';
    protected $fillable = ['bounty_id', 'profile_id', 'cover_letter', 'proposed_rate', 'status'];

    /**
     * Get the bounty for this application
     * @param int $applicationId
     * @return array|false
     */
    public function bounty($applicationId)
    {
        $application = $this->find($applicationId);
        if (!$application || !isset($application['bounty_id'])) {
            return false;
        }

        $sql = "SELECT * FROM bounties WHERE id = ?";
        return $this->db->queryOne($sql, [$application['bounty_id']]);
    }

    /**
     * Get the profile for this application
     * @param int $applicationId
     * @return array|false
     */
    public function profile($applicationId)
    {
        $application = $this->find($applicationId);
        if (!$application || !isset($application['profile_id'])) {
            return false;
        }

        $sql = "SELECT * FROM profiles WHERE id = ?";
        return $this->db->queryOne($sql, [$application['profile_id']]);
    }

    /**
     * Accept this application
     * @param int $applicationId
     * @return int Number of affected rows
     */
    public function accept($applicationId)
    {
        return $this->update($applicationId, ['status' => 'accepted']);
    }

    /**
     * Reject this application
     * @param int $applicationId
     * @return int Number of affected rows
     */
    public function reject($applicationId)
    {
        return $this->update($applicationId, ['status' => 'rejected']);
    }

    /**
     * Withdraw this application
     * @param int $applicationId
     * @return int Number of affected rows
     */
    public function withdraw($applicationId)
    {
        return $this->update($applicationId, ['status' => 'withdrawn']);
    }

    /**
     * Get all applications for a specific bounty
     * @param int $bountyId
     * @return array
     */
    public static function getForBounty($bountyId)
    {
        $application = new self();
        return $application->where('bounty_id', $bountyId)->orderBy('created_at', 'DESC')->get();
    }

    /**
     * Get all applications by a specific profile
     * @param int $profileId
     * @return array
     */
    public static function getForProfile($profileId)
    {
        $application = new self();
        return $application->where('profile_id', $profileId)->orderBy('created_at', 'DESC')->get();
    }
}
