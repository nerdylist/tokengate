<?php

require_once __DIR__ . '/Model.php';

class Category extends Model
{
    protected $table = 'categories';
    protected $fillable = ['name', 'slug', 'description'];

    /**
     * Find a category by slug
     * @param string $slug
     * @return array|false
     */
    public function findBySlug($slug)
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Get count of bounties in this category
     * @param int $categoryId
     * @return int
     */
    public function getBountiesCount($categoryId)
    {
        $sql = "SELECT COUNT(*) as count FROM bounties WHERE category_id = ?";
        $result = $this->db->queryOne($sql, [$categoryId]);
        return (int) $result['count'];
    }

    /**
     * Get all bounties in this category
     * @param int $categoryId
     * @return array
     */
    public function bounties($categoryId)
    {
        $sql = "SELECT * FROM bounties WHERE category_id = ? ORDER BY created_at DESC";
        return $this->db->query($sql, [$categoryId]);
    }
}
