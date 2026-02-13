<?php

require_once __DIR__ . '/Model.php';

class Guild extends Model
{
    protected $table = 'guilds';
    protected $fillable = ['name', 'slug', 'type', 'description', 'icon', 'color'];

    /**
     * Find guild by slug
     * @param string $slug
     * @return array|false
     */
    public function findBySlug($slug)
    {
        $sql = "SELECT * FROM guilds WHERE slug = ?";
        return $this->db->queryOne($sql, [$slug]);
    }

    /**
     * Get guilds by type (modern or traditional)
     * @param string $type
     * @return array
     */
    public function getByType($type)
    {
        return $this->where('type', $type)->orderBy('name', 'ASC')->get();
    }

    /**
     * Get all members of a guild
     * @param int $guildId
     * @return array
     */
    public function members($guildId)
    {
        $sql = "SELECT p.*, pg.rank_id, pg.xp, pg.joined_at, r.name as rank_name, r.level as rank_level
                FROM profiles p
                INNER JOIN profile_guilds pg ON p.id = pg.profile_id
                INNER JOIN ranks r ON pg.rank_id = r.id
                WHERE pg.guild_id = ?
                ORDER BY pg.xp DESC, pg.joined_at ASC";
        return $this->db->query($sql, [$guildId]);
    }

    /**
     * Get members of a guild by rank
     * @param int $guildId
     * @param int $rankId
     * @return array
     */
    public function membersByRank($guildId, $rankId)
    {
        $sql = "SELECT p.*, pg.xp, pg.joined_at
                FROM profiles p
                INNER JOIN profile_guilds pg ON p.id = pg.profile_id
                WHERE pg.guild_id = ? AND pg.rank_id = ?
                ORDER BY pg.xp DESC";
        return $this->db->query($sql, [$guildId, $rankId]);
    }
}
