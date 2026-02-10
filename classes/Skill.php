<?php

require_once __DIR__ . '/Model.php';

class Skill extends Model
{
    protected $table = 'skills';
    protected $fillable = ['name', 'slug', 'category_id'];

    /**
     * Find a skill by slug
     * @param string $slug
     * @return array|false
     */
    public function findBySlug($slug)
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Get the category this skill belongs to
     * @param int $skillId
     * @return array|false
     */
    public function category($skillId)
    {
        $skill = $this->find($skillId);
        if (!$skill || !isset($skill['category_id'])) {
            return false;
        }

        $sql = "SELECT * FROM categories WHERE id = ?";
        return $this->db->queryOne($sql, [$skill['category_id']]);
    }

    /**
     * Find existing skill by name or create new one
     * @param string $name
     * @param int $categoryId Optional category ID
     * @return array The skill record
     */
    public static function findOrCreateByName($name, $categoryId = null)
    {
        $skill = new self();

        // Try to find existing skill by name
        $existing = $skill->where('name', $name)->first();
        if ($existing) {
            return $existing;
        }

        // Create new skill with auto-generated slug
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        $data = [
            'name' => $name,
            'slug' => $slug
        ];

        if ($categoryId !== null) {
            $data['category_id'] = $categoryId;
        }

        $skillId = $skill->create($data);
        return $skill->find($skillId);
    }
}
