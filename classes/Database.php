<?php
/**
 * Database Singleton Manager
 * Provides a single database connection instance across the application
 */

class Database
{
    private static $instance = null;
    private $pdo;

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct()
    {
        $dbName = defined('DB_NAME') ? DB_NAME : 'g8.db';
        $dbPath = __DIR__ . '/../database/' . $dbName;

        try {
            $this->pdo = new PDO('sqlite:' . $dbPath);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->exec('PRAGMA foreign_keys = ON');
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Get the singleton instance
     * @return Database
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get the PDO connection
     * @return PDO
     */
    public function getConnection()
    {
        return $this->pdo;
    }

    /**
     * Execute a query and return results
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @return array Query results
     */
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception('Query failed: ' . $e->getMessage());
        }
    }

    /**
     * Execute a query and return a single row
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @return array|false Single row or false
     */
    public function queryOne($sql, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception('Query failed: ' . $e->getMessage());
        }
    }

    /**
     * Execute a query and return the number of affected rows
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @return int Number of affected rows
     */
    public function execute($sql, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception('Query execution failed: ' . $e->getMessage());
        }
    }

    /**
     * Get the last inserted ID
     * @return string Last insert ID
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Begin a transaction
     */
    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    /**
     * Commit a transaction
     */
    public function commit()
    {
        $this->pdo->commit();
    }

    /**
     * Rollback a transaction
     */
    public function rollback()
    {
        $this->pdo->rollBack();
    }

    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}

    /**
     * Prevent unserialization of the instance
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}
