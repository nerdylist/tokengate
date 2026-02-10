<?php
/**
 * Base Model Class
 * Provides common CRUD operations and query building for database models
 */

require_once __DIR__ . '/Database.php';

abstract class Model
{
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $db;

    private $whereConditions = [];
    private $whereParams = [];
    private $orderByClause = '';
    private $limitClause = '';
    private $offsetClause = '';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Find a record by ID
     * @param int $id
     * @return array|false
     */
    public function find($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->queryOne($sql, [$id]);
    }

    /**
     * Get all records
     * @param string $orderBy Order clause (e.g., 'created_at DESC')
     * @return array
     */
    public function all($orderBy = null)
    {
        $sql = "SELECT * FROM {$this->table}";
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        return $this->db->query($sql);
    }

    /**
     * Create a new record
     * @param array $data
     * @return string Last insert ID
     */
    public function create($data)
    {
        $data = $this->filterFillable($data);

        // Add timestamps if not provided
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $this->db->execute($sql, array_values($data));

        return $this->db->lastInsertId();
    }

    /**
     * Update a record by ID
     * @param int $id
     * @param array $data
     * @return int Number of affected rows
     */
    public function update($id, $data)
    {
        $data = $this->filterFillable($data);

        // Update timestamp
        $data['updated_at'] = date('Y-m-d H:i:s');

        $setClauses = [];
        foreach (array_keys($data) as $column) {
            $setClauses[] = "{$column} = ?";
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses) .
               " WHERE {$this->primaryKey} = ?";

        $params = array_values($data);
        $params[] = $id;

        return $this->db->execute($sql, $params);
    }

    /**
     * Delete a record by ID
     * @param int $id
     * @return int Number of affected rows
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->execute($sql, [$id]);
    }

    /**
     * Add WHERE condition
     * @param string $column
     * @param mixed $operator Operator or value if no operator
     * @param mixed $value Value (optional if operator is value)
     * @return $this
     */
    public function where($column, $operator, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->whereConditions[] = "{$column} {$operator} ?";
        $this->whereParams[] = $value;

        return $this;
    }

    /**
     * Add ORDER BY clause
     * @param string $column
     * @param string $direction
     * @return $this
     */
    public function orderBy($column, $direction = 'ASC')
    {
        $this->orderByClause = "ORDER BY {$column} {$direction}";
        return $this;
    }

    /**
     * Add LIMIT clause
     * @param int $limit
     * @return $this
     */
    public function limit($limit)
    {
        $this->limitClause = "LIMIT {$limit}";
        return $this;
    }

    /**
     * Add OFFSET clause
     * @param int $offset
     * @return $this
     */
    public function offset($offset)
    {
        $this->offsetClause = "OFFSET {$offset}";
        return $this;
    }

    /**
     * Execute the query with conditions
     * @return array
     */
    public function get()
    {
        $sql = "SELECT * FROM {$this->table}";

        if (!empty($this->whereConditions)) {
            $sql .= " WHERE " . implode(' AND ', $this->whereConditions);
        }

        if ($this->orderByClause) {
            $sql .= " {$this->orderByClause}";
        }

        if ($this->limitClause) {
            $sql .= " {$this->limitClause}";
        }

        if ($this->offsetClause) {
            $sql .= " {$this->offsetClause}";
        }

        $results = $this->db->query($sql, $this->whereParams);

        // Reset query builder
        $this->resetQuery();

        return $results;
    }

    /**
     * Get the first result
     * @return array|false
     */
    public function first()
    {
        $this->limit(1);
        $results = $this->get();
        return !empty($results) ? $results[0] : false;
    }

    /**
     * Get count of records
     * @return int
     */
    public function count()
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";

        if (!empty($this->whereConditions)) {
            $sql .= " WHERE " . implode(' AND ', $this->whereConditions);
        }

        $result = $this->db->queryOne($sql, $this->whereParams);

        // Reset query builder
        $this->resetQuery();

        return (int) $result['count'];
    }

    /**
     * Paginate results
     * @param int $perPage
     * @param int $page
     * @return array
     */
    public function paginate($perPage = 10, $page = 1)
    {
        $offset = ($page - 1) * $perPage;

        // Get total count
        $total = $this->count();

        // Get results
        $this->limit($perPage)->offset($offset);
        $data = $this->get();

        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }

    /**
     * Filter data to only fillable fields
     * @param array $data
     * @return array
     */
    protected function filterFillable($data)
    {
        if (empty($this->fillable)) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * Reset query builder conditions
     */
    protected function resetQuery()
    {
        $this->whereConditions = [];
        $this->whereParams = [];
        $this->orderByClause = '';
        $this->limitClause = '';
        $this->offsetClause = '';
    }

    /**
     * Execute raw SQL query
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function raw($sql, $params = [])
    {
        return $this->db->query($sql, $params);
    }

    /**
     * Begin database transaction
     */
    public function beginTransaction()
    {
        $this->db->beginTransaction();
    }

    /**
     * Commit database transaction
     */
    public function commit()
    {
        $this->db->commit();
    }

    /**
     * Rollback database transaction
     */
    public function rollback()
    {
        $this->db->rollback();
    }
}
