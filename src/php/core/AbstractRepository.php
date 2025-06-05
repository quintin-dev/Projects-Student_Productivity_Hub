<?php
/**
 * Abstract Repository Class
 * 
 * Base repository class for all repositories in the application
 * Implements repository pattern for data access
 * 
 * @package ProductivityHub\Core
 * @author Edwardking (Edd)
 * @version 1.0
 */

declare(strict_types=1);

namespace ProductivityHub\Core;

use ProductivityHub\Config\Database;

abstract class AbstractRepository
{
    /**
     * Database connection
     * 
     * @var \PDO
     */
    protected \PDO $db;
    
    /**
     * Table name
     * 
     * @var string
     */
    protected string $table = '';
    
    /**
     * Primary key column
     * 
     * @var string
     */
    protected string $primaryKey = 'id';
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Find all records
     * 
     * @param array $options Query options (where, order, limit, etc.)
     * @return array Array of records
     */
    public function findAll(array $options = []): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_deleted = 0";
        $params = [];
        
        // Add where conditions
        if (isset($options['where']) && !empty($options['where'])) {
            foreach ($options['where'] as $column => $value) {
                $sql .= " AND $column = :$column";
                $params[":$column"] = $value;
            }
        }
        
        // Add order by
        if (isset($options['order']) && !empty($options['order'])) {
            $sql .= " ORDER BY {$options['order']}";
        }
        
        // Add limit
        if (isset($options['limit']) && is_numeric($options['limit'])) {
            $sql .= " LIMIT {$options['limit']}";
            
            // Add offset
            if (isset($options['offset']) && is_numeric($options['offset'])) {
                $sql .= " OFFSET {$options['offset']}";
            }
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Database error in findAll: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Find a record by ID
     * 
     * @param int $id Record ID
     * @return array|null Record data or null if not found
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id AND is_deleted = 0";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            
            $record = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            return $record ?: null;
        } catch (\PDOException $e) {
            error_log("Database error in findById: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create a new record
     * 
     * @param array $data Record data
     * @return int|null New record ID or null on failure
     */
    public function create(array $data): ?int
    {
        $columns = array_keys($data);
        $placeholders = array_map(function ($column) {
            return ":$column";
        }, $columns);
        
        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
        
        try {
            $stmt = $this->db->prepare($sql);
            
            foreach ($data as $column => $value) {
                $stmt->bindValue(":$column", $value);
            }
            
            $result = $stmt->execute();
            
            return $result ? (int) $this->db->lastInsertId() : null;
        } catch (\PDOException $e) {
            error_log("Database error in create: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update a record
     * 
     * @param int $id Record ID
     * @param array $data Record data
     * @return bool True if update was successful
     */
    public function update(int $id, array $data): bool
    {
        $setStatements = array_map(function ($column) {
            return "$column = :$column";
        }, array_keys($data));
        
        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s = :id",
            $this->table,
            implode(', ', $setStatements),
            $this->primaryKey
        );
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id);
            
            foreach ($data as $column => $value) {
                $stmt->bindValue(":$column", $value);
            }
            
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Database error in update: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a record (soft delete)
     * 
     * @param int $id Record ID
     * @return bool True if delete was successful
     */
    public function delete(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET is_deleted = 1, updated_at = CURRENT_TIMESTAMP WHERE {$this->primaryKey} = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id);
            
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Database error in delete: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Count records
     * 
     * @param array $where Where conditions
     * @return int Record count
     */
    public function count(array $where = []): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE is_deleted = 0";
        $params = [];
        
        // Add where conditions
        if (!empty($where)) {
            foreach ($where as $column => $value) {
                $sql .= " AND $column = :$column";
                $params[":$column"] = $value;
            }
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }
            
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Database error in count: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Execute a custom query
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @param bool $fetchAll Whether to fetch all records or just one
     * @return array|null Query result
     */
    public function query(string $sql, array $params = [], bool $fetchAll = true): ?array
    {
        try {
            $stmt = $this->db->prepare($sql);
            
            foreach ($params as $param => $value) {
                $stmt->bindValue(is_numeric($param) ? $param + 1 : $param, $value);
            }
            
            $stmt->execute();
            
            return $fetchAll ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Database error in query: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Begin a transaction
     * 
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }
    
    /**
     * Commit a transaction
     * 
     * @return bool
     */
    public function commit(): bool
    {
        return $this->db->commit();
    }
    
    /**
     * Rollback a transaction
     * 
     * @return bool
     */
    public function rollback(): bool
    {
        return $this->db->rollBack();
    }
}
