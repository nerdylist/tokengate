<?php

require_once __DIR__ . '/Model.php';

class GuildComment extends Model
{
    protected $table = 'guild_comments';
    protected $fillable = ['thread_id', 'profile_id', 'content'];

    /**
     * Get all comments for a thread
     * @param int $threadId
     * @return array
     */
    public function getByThread($threadId)
    {
        $sql = "SELECT * FROM guild_comments
                WHERE thread_id = ?
                ORDER BY created_at ASC";
        return $this->db->query($sql, [$threadId]);
    }

    /**
     * Get comment with author profile information
     * @param int $commentId
     * @return array|false
     */
    public function getWithAuthor($commentId)
    {
        $sql = "SELECT gc.*, p.profile_id, p.avatar_url, u.name as author_name
                FROM guild_comments gc
                INNER JOIN profiles p ON gc.profile_id = p.id
                INNER JOIN users u ON p.user_id = u.id
                WHERE gc.id = ?";
        return $this->db->queryOne($sql, [$commentId]);
    }
}
