<?php

require_once __DIR__ . '/Model.php';

class ProfileGuild extends Model
{
    protected $table = 'profile_guilds';
    protected $fillable = ['profile_id', 'guild_id', 'rank_id', 'xp', 'is_primary', 'joined_at'];

    /**
     * Add a guild to a profile
     * @param int $profileId
     * @param int $guildId
     * @param int $rankId
     * @param int $isPrimary
     * @return string Last insert ID
     */
    public function addGuildToProfile($profileId, $guildId, $rankId, $isPrimary = 0)
    {
        return $this->create([
            'profile_id' => $profileId,
            'guild_id' => $guildId,
            'rank_id' => $rankId,
            'xp' => 0,
            'is_primary' => $isPrimary,
            'joined_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Update rank for a profile in a guild
     * @param int $profileId
     * @param int $guildId
     * @param int $newRankId
     * @return int Number of affected rows
     */
    public function updateRank($profileId, $guildId, $newRankId)
    {
        $sql = "UPDATE profile_guilds
                SET rank_id = ?, updated_at = ?
                WHERE profile_id = ? AND guild_id = ?";
        return $this->db->execute($sql, [$newRankId, date('Y-m-d H:i:s'), $profileId, $guildId]);
    }

    /**
     * Add experience points to a profile's guild membership
     * @param int $profileId
     * @param int $guildId
     * @param int $xp
     * @return int Number of affected rows
     */
    public function addExperience($profileId, $guildId, $xp)
    {
        $sql = "UPDATE profile_guilds
                SET xp = xp + ?, updated_at = ?
                WHERE profile_id = ? AND guild_id = ?";
        return $this->db->execute($sql, [$xp, date('Y-m-d H:i:s'), $profileId, $guildId]);
    }

    /**
     * Set a guild as the primary guild for a profile
     * @param int $profileId
     * @param int $guildId
     * @return bool
     */
    public function setPrimaryGuild($profileId, $guildId)
    {
        try {
            $this->db->beginTransaction();

            // Set all guilds to non-primary
            $sql1 = "UPDATE profile_guilds SET is_primary = 0, updated_at = ? WHERE profile_id = ?";
            $this->db->execute($sql1, [date('Y-m-d H:i:s'), $profileId]);

            // Set the specified guild as primary
            $sql2 = "UPDATE profile_guilds SET is_primary = 1, updated_at = ? WHERE profile_id = ? AND guild_id = ?";
            $this->db->execute($sql2, [date('Y-m-d H:i:s'), $profileId, $guildId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Get all guilds for a profile
     * @param int $profileId
     * @return array
     */
    public function getProfileGuilds($profileId)
    {
        $sql = "SELECT g.*, r.name as rank_name, r.level as rank_level,
                       pg.xp, pg.is_primary, pg.joined_at, pg.rank_id
                FROM guilds g
                INNER JOIN profile_guilds pg ON g.id = pg.guild_id
                INNER JOIN ranks r ON pg.rank_id = r.id
                WHERE pg.profile_id = ?
                ORDER BY pg.is_primary DESC, pg.xp DESC";
        return $this->db->query($sql, [$profileId]);
    }
}
