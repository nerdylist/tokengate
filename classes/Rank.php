<?php

require_once __DIR__ . '/Model.php';

class Rank extends Model
{
    protected $table = 'ranks';
    protected $fillable = ['name', 'level', 'type', 'xp_required', 'description'];

    /**
     * Get ranks by type (modern or traditional)
     * @param string $type
     * @return array
     */
    public function getByType($type)
    {
        return $this->where('type', $type)->orderBy('level', 'ASC')->get();
    }

    /**
     * Get rank by level and type
     * @param int $level
     * @param string $type
     * @return array|false
     */
    public function getByLevel($level, $type)
    {
        $sql = "SELECT * FROM ranks WHERE level = ? AND type = ?";
        return $this->db->queryOne($sql, [$level, $type]);
    }

    /**
     * Get the next rank in progression
     * @param int $currentRankId
     * @return array|false
     */
    public function getNextRank($currentRankId)
    {
        $currentRank = $this->find($currentRankId);
        if (!$currentRank) {
            return false;
        }

        $sql = "SELECT * FROM ranks
                WHERE type = ? AND level > ?
                ORDER BY level ASC
                LIMIT 1";
        return $this->db->queryOne($sql, [$currentRank['type'], $currentRank['level']]);
    }
}
