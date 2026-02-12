<?php

require_once __DIR__ . '/Model.php';

class GuildThread extends Model
{
    protected $table = 'guild_threads';
    protected $fillable = ['guild_id', 'profile_id', 'title', 'content', 'is_pinned', 'is_locked', 'view_count'];

    /**
     * Get all threads for a guild
     * @param int $guildId
     * @return array
     */
    public function getByGuild($guildId)
    {
        $sql = "SELECT * FROM guild_threads
                WHERE guild_id = ?
                ORDER BY is_pinned DESC, created_at DESC";
        return $this->db->query($sql, [$guildId]);
    }

    /**
     * Get thread with author profile information
     * @param int $threadId
     * @return array|false
     */
    public function getWithAuthor($threadId)
    {
        $sql = "SELECT gt.*, p.profile_id, p.avatar_url, u.name as author_name
                FROM guild_threads gt
                INNER JOIN profiles p ON gt.profile_id = p.id
                INNER JOIN users u ON p.user_id = u.id
                WHERE gt.id = ?";
        return $this->db->queryOne($sql, [$threadId]);
    }

    /**
     * Increment view count for a thread
     * @param int $threadId
     * @return int Number of affected rows
     */
    public function incrementViewCount($threadId)
    {
        $sql = "UPDATE guild_threads
                SET view_count = view_count + 1, updated_at = ?
                WHERE id = ?";
        return $this->db->execute($sql, [date('Y-m-d H:i:s'), $threadId]);
    }

    /**
     * Get comment count for a thread
     * @param int $threadId
     * @return int
     */
    public function getCommentCount($threadId)
    {
        $sql = "SELECT COUNT(*) as count FROM guild_comments WHERE thread_id = ?";
        $result = $this->db->queryOne($sql, [$threadId]);
        return (int) $result['count'];
    }
}
