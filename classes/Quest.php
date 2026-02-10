<?php

require_once __DIR__ . '/Model.php';

class Quest extends Model
{
    protected $table = 'quests';
    protected $fillable = ['guild_id', 'name', 'description', 'type', 'min_rank_id', 'xp_reward', 'is_active'];

    /**
     * Get active quests
     * @return array
     */
    public function getActive()
    {
        return $this->where('is_active', 1)->orderBy('created_at', 'DESC')->get();
    }

    /**
     * Get quests by guild
     * @param int $guildId
     * @return array
     */
    public function getByGuild($guildId)
    {
        return $this->where('guild_id', $guildId)
                    ->where('is_active', 1)
                    ->orderBy('created_at', 'DESC')
                    ->get();
    }

    /**
     * Get quests by type
     * @param string $type
     * @return array
     */
    public function getByType($type)
    {
        return $this->where('type', $type)
                    ->where('is_active', 1)
                    ->orderBy('xp_reward', 'DESC')
                    ->get();
    }
}
