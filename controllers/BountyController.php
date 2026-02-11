<?php

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';

class BountyController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * List bounties with filtering
     * @param array $filters Filters: category_id, skills, budget_min, budget_max, status
     * @return array List of bounties with related data
     */
    public function index($filters = [])
    {
        try {
            $sql = "SELECT
                        b.*,
                        c.name as category_name,
                        c.slug as category_slug,
                        u.name as user_name,
                        u.email as user_email,
                        (SELECT COUNT(*) FROM applications WHERE bounty_id = b.id) as application_count
                    FROM bounties b
                    LEFT JOIN categories c ON b.category_id = c.id
                    LEFT JOIN users u ON b.user_id = u.id
                    WHERE 1=1";

            $params = [];

            // Apply filters
            if (!empty($filters['category_id'])) {
                $sql .= " AND b.category_id = ?";
                $params[] = $filters['category_id'];
            }

            if (!empty($filters['status'])) {
                $sql .= " AND b.status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['budget_min'])) {
                $sql .= " AND b.budget_max >= ?";
                $params[] = $filters['budget_min'];
            }

            if (!empty($filters['budget_max'])) {
                $sql .= " AND b.budget_min <= ?";
                $params[] = $filters['budget_max'];
            }

            // Filter by skills if provided
            if (!empty($filters['skills']) && is_array($filters['skills'])) {
                $placeholders = implode(',', array_fill(0, count($filters['skills']), '?'));
                $sql .= " AND b.id IN (
                    SELECT bounty_id FROM bounty_skills
                    WHERE skill_id IN ($placeholders)
                    GROUP BY bounty_id
                    HAVING COUNT(DISTINCT skill_id) = ?
                )";
                $params = array_merge($params, $filters['skills']);
                $params[] = count($filters['skills']);
            }

            $sql .= " ORDER BY b.created_at DESC";

            $bounties = $this->db->query($sql, $params);

            // Fetch skills for each bounty
            foreach ($bounties as &$bounty) {
                $bounty['skills'] = $this->getBountySkills($bounty['id']);
            }

            return $bounties;
        } catch (Exception $e) {
            throw new Exception("Failed to fetch bounties: " . $e->getMessage());
        }
    }

    /**
     * Get single bounty with all details
     * @param int $id Bounty ID
     * @return array|false Bounty data or false
     */
    public function show($id)
    {
        try {
            $sql = "SELECT
                        b.*,
                        c.name as category_name,
                        c.slug as category_slug,
                        u.name as user_name,
                        u.email as user_email,
                        u.id as user_id,
                        (SELECT COUNT(*) FROM applications WHERE bounty_id = b.id) as application_count
                    FROM bounties b
                    LEFT JOIN categories c ON b.category_id = c.id
                    LEFT JOIN users u ON b.user_id = u.id
                    WHERE b.id = ?";

            $bounty = $this->db->queryOne($sql, [$id]);

            if (!$bounty) {
                return false;
            }

            // Fetch associated skills
            $bounty['skills'] = $this->getBountySkills($id);

            return $bounty;
        } catch (Exception $e) {
            throw new Exception("Failed to fetch bounty: " . $e->getMessage());
        }
    }

    /**
     * Create new bounty (requires authentication)
     * @param array $data Bounty data
     * @return string Created bounty ID
     */
    public function create($data)
    {
        try {
            // Validate authentication
            if (!Auth::check()) {
                throw new Exception("Authentication required");
            }

            // Validate required fields
            if (empty($data['title']) || empty($data['description']) || empty($data['category_id'])) {
                throw new Exception("Missing required fields: title, description, category_id");
            }

            $this->db->beginTransaction();

            // Prepare bounty data
            $bountyData = [
                'user_id' => Auth::id(),
                'category_id' => $data['category_id'],
                'title' => $data['title'],
                'description' => $data['description'],
                'budget_min' => $data['budget_min'] ?? null,
                'budget_max' => $data['budget_max'] ?? null,
                'deadline' => $data['deadline'] ?? null,
                'status' => 'open',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Insert bounty
            $columns = implode(', ', array_keys($bountyData));
            $placeholders = implode(', ', array_fill(0, count($bountyData), '?'));
            $sql = "INSERT INTO bounties ($columns) VALUES ($placeholders)";

            $this->db->execute($sql, array_values($bountyData));
            $bountyId = $this->db->lastInsertId();

            // Associate skills if provided
            if (!empty($data['skills']) && is_array($data['skills'])) {
                $this->associateBountySkills($bountyId, $data['skills']);
            }

            $this->db->commit();

            return $bountyId;
        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception("Failed to create bounty: " . $e->getMessage());
        }
    }

    /**
     * Update bounty (requires authentication and ownership)
     * @param int $id Bounty ID
     * @param array $data Update data
     * @return int Number of affected rows
     */
    public function update($id, $data)
    {
        try {
            // Validate authentication
            if (!Auth::check()) {
                throw new Exception("Authentication required");
            }

            // Validate ownership
            $bounty = $this->db->queryOne("SELECT user_id FROM bounties WHERE id = ?", [$id]);
            if (!$bounty) {
                throw new Exception("Bounty not found");
            }

            if ($bounty['user_id'] != Auth::id()) {
                throw new Exception("Permission denied: You don't own this bounty");
            }

            $this->db->beginTransaction();

            // Prepare update data
            $updateFields = [];
            $params = [];

            $allowedFields = ['title', 'description', 'category_id', 'budget_min', 'budget_max', 'deadline', 'status'];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateFields[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }

            // Always update updated_at
            $updateFields[] = "updated_at = ?";
            $params[] = date('Y-m-d H:i:s');
            $params[] = $id;

            // Update bounty
            $sql = "UPDATE bounties SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $affectedRows = $this->db->execute($sql, $params);

            // Update skills if provided
            if (isset($data['skills']) && is_array($data['skills'])) {
                // Delete existing skills
                $this->db->execute("DELETE FROM bounty_skills WHERE bounty_id = ?", [$id]);

                // Insert new skills
                if (!empty($data['skills'])) {
                    $this->associateBountySkills($id, $data['skills']);
                }
            }

            $this->db->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception("Failed to update bounty: " . $e->getMessage());
        }
    }

    /**
     * Delete bounty (requires authentication and ownership)
     * @param int $id Bounty ID
     * @return int Number of affected rows
     */
    public function delete($id)
    {
        try {
            // Validate authentication
            if (!Auth::check()) {
                throw new Exception("Authentication required");
            }

            // Validate ownership
            $bounty = $this->db->queryOne("SELECT user_id FROM bounties WHERE id = ?", [$id]);
            if (!$bounty) {
                throw new Exception("Bounty not found");
            }

            if ($bounty['user_id'] != Auth::id()) {
                throw new Exception("Permission denied: You don't own this bounty");
            }

            // Delete bounty (cascade will handle related records)
            $sql = "DELETE FROM bounties WHERE id = ?";
            return $this->db->execute($sql, [$id]);
        } catch (Exception $e) {
            throw new Exception("Failed to delete bounty: " . $e->getMessage());
        }
    }

    /**
     * Get all bounties by user
     * @param int $userId User ID
     * @return array List of bounties
     */
    public function getUserBounties($userId)
    {
        try {
            $sql = "SELECT
                        b.*,
                        c.name as category_name,
                        c.slug as category_slug,
                        (SELECT COUNT(*) FROM applications WHERE bounty_id = b.id) as application_count
                    FROM bounties b
                    LEFT JOIN categories c ON b.category_id = c.id
                    WHERE b.user_id = ?
                    ORDER BY b.created_at DESC";

            $bounties = $this->db->query($sql, [$userId]);

            // Fetch skills for each bounty
            foreach ($bounties as &$bounty) {
                $bounty['skills'] = $this->getBountySkills($bounty['id']);
            }

            return $bounties;
        } catch (Exception $e) {
            throw new Exception("Failed to fetch user bounties: " . $e->getMessage());
        }
    }

    /**
     * Get skills for a bounty
     * @param int $bountyId Bounty ID
     * @return array List of skills
     */
    private function getBountySkills($bountyId)
    {
        try {
            $sql = "SELECT s.id, s.name, s.slug, s.category_id
                    FROM skills s
                    INNER JOIN bounty_skills bs ON s.id = bs.skill_id
                    WHERE bs.bounty_id = ?";

            return $this->db->query($sql, [$bountyId]);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Associate skills with a bounty
     * @param int $bountyId Bounty ID
     * @param array $skillIds Array of skill IDs
     */
    private function associateBountySkills($bountyId, $skillIds)
    {
        try {
            foreach ($skillIds as $skillId) {
                $sql = "INSERT INTO bounty_skills (bounty_id, skill_id, created_at) VALUES (?, ?, ?)";
                $this->db->execute($sql, [$bountyId, $skillId, date('Y-m-d H:i:s')]);
            }
        } catch (Exception $e) {
            throw new Exception("Failed to associate skills: " . $e->getMessage());
        }
    }

    /**
     * Get ranks for a bounty
     * @param int $bountyId Bounty ID
     * @return array List of ranks
     */
    public function getBountyRanks($bountyId)
    {
        try {
            $sql = "SELECT r.id, r.name, r.level, r.type, r.description
                    FROM ranks r
                    INNER JOIN bounty_ranks br ON r.id = br.rank_id
                    WHERE br.bounty_id = ?
                    ORDER BY r.type, r.level";

            return $this->db->query($sql, [$bountyId]);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Associate ranks with a bounty
     * @param int $bountyId Bounty ID
     * @param array $rankIds Array of rank IDs
     */
    public function saveRankAssociations($bountyId, $rankIds)
    {
        try {
            if (empty($rankIds) || !is_array($rankIds)) {
                return;
            }

            foreach ($rankIds as $rankId) {
                $sql = "INSERT INTO bounty_ranks (bounty_id, rank_id, created_at) VALUES (?, ?, ?)";
                $this->db->execute($sql, [$bountyId, $rankId, date('Y-m-d H:i:s')]);
            }
        } catch (Exception $e) {
            throw new Exception("Failed to associate ranks: " . $e->getMessage());
        }
    }
}
