<?php

require_once __DIR__ . '/Model.php';

class Bounty extends Model
{
    protected $table = 'bounties';
    protected $fillable = ['user_id', 'category_id', 'title', 'description', 'budget_min', 'budget_max', 'deadline', 'status'];

    /**
     * Get the user who posted this bounty
     * @param int $bountyId
     * @return array|false
     */
    public function user($bountyId)
    {
        $bounty = $this->find($bountyId);
        if (!$bounty || !isset($bounty['user_id'])) {
            return false;
        }

        $sql = "SELECT * FROM users WHERE id = ?";
        return $this->db->queryOne($sql, [$bounty['user_id']]);
    }

    /**
     * Get the category for this bounty
     * @param int $bountyId
     * @return array|false
     */
    public function category($bountyId)
    {
        $bounty = $this->find($bountyId);
        if (!$bounty || !isset($bounty['category_id'])) {
            return false;
        }

        $sql = "SELECT * FROM categories WHERE id = ?";
        return $this->db->queryOne($sql, [$bounty['category_id']]);
    }

    /**
     * Get all skills for this bounty
     * @param int $bountyId
     * @return array
     */
    public function skills($bountyId)
    {
        $sql = "SELECT s.* FROM skills s
                INNER JOIN bounty_skills bs ON s.id = bs.skill_id
                WHERE bs.bounty_id = ?
                ORDER BY s.name";
        return $this->db->query($sql, [$bountyId]);
    }

    /**
     * Get all applications for this bounty
     * @param int $bountyId
     * @return array
     */
    public function applications($bountyId)
    {
        $sql = "SELECT * FROM applications WHERE bounty_id = ? ORDER BY created_at DESC";
        return $this->db->query($sql, [$bountyId]);
    }

    /**
     * Add a skill to this bounty
     * @param int $bountyId
     * @param int $skillId
     * @return bool
     */
    public function addSkill($bountyId, $skillId)
    {
        try {
            $sql = "INSERT INTO bounty_skills (bounty_id, skill_id, created_at) VALUES (?, ?, ?)";
            $this->db->execute($sql, [$bountyId, $skillId, date('Y-m-d H:i:s')]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Remove a skill from this bounty
     * @param int $bountyId
     * @param int $skillId
     * @return int Number of affected rows
     */
    public function removeSkill($bountyId, $skillId)
    {
        $sql = "DELETE FROM bounty_skills WHERE bounty_id = ? AND skill_id = ?";
        return $this->db->execute($sql, [$bountyId, $skillId]);
    }

    /**
     * Update the status of this bounty
     * @param int $bountyId
     * @param string $status
     * @return int Number of affected rows
     */
    public function updateStatus($bountyId, $status)
    {
        return $this->update($bountyId, ['status' => $status]);
    }

    /**
     * Get all active (open) bounties
     * @return array
     */
    public static function getActive()
    {
        $bounty = new self();
        return $bounty->where('status', 'open')->orderBy('created_at', 'DESC')->get();
    }

    /**
     * Get bounties by category
     * @param int $categoryId
     * @return array
     */
    public static function getByCategory($categoryId)
    {
        $bounty = new self();
        return $bounty->where('category_id', $categoryId)->orderBy('created_at', 'DESC')->get();
    }

    /**
     * Search bounties by title and description
     * @param string $query
     * @return array
     */
    public static function search($query)
    {
        $bounty = new self();
        $searchTerm = '%' . $query . '%';

        $sql = "SELECT * FROM bounties
                WHERE title LIKE ? OR description LIKE ?
                ORDER BY created_at DESC";

        return $bounty->db->query($sql, [$searchTerm, $searchTerm]);
    }
}
