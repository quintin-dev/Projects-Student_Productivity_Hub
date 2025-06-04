<?php
/**
 * Abstract Model Class
 * 
 * Base model class for all models in the application
 * Provides common functionality for data validation and persistence
 * 
 * @package ProductivityHub\Core
 * @author Edwardking (Edd)
 * @version 1.0
 */

declare(strict_types=1);

namespace ProductivityHub\Core;

use ProductivityHub\Config\Database;

abstract class AbstractModel
{
    /**
     * Model attributes
     * 
     * @var array
     */
    protected array $attributes = [];
    
    /**
     * Validation rules
     * 
     * @var array
     */
    protected array $rules = [];
    
    /**
     * Validation errors
     * 
     * @var array
     */
    protected array $errors = [];
    
    /**
     * Database table name
     * 
     * @var string
     */
    protected string $table = '';
    
    /**
     * Primary key column name
     * 
     * @var string
     */
    protected string $primaryKey = 'id';
    
    /**
     * Database connection
     * 
     * @var \PDO
     */
    protected \PDO $db;
    
    /**
     * Constructor
     * 
     * @param array $attributes Initial attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->db = Database::getInstance()->getConnection();
        $this->fill($attributes);
    }
    
    /**
     * Fill model with attributes
     * 
     * @param array $attributes Attributes to fill
     * @return self
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
        
        return $this;
    }
    
    /**
     * Set a model attribute
     * 
     * @param string $key Attribute key
     * @param mixed $value Attribute value
     * @return self
     */
    public function setAttribute(string $key, $value): self
    {
        $this->attributes[$key] = $value;
        return $this;
    }
    
    /**
     * Get a model attribute
     * 
     * @param string $key Attribute key
     * @param mixed $default Default value if attribute not set
     * @return mixed Attribute value or default
     */
    public function getAttribute(string $key, $default = null)
    {
        return $this->attributes[$key] ?? $default;
    }
    
    /**
     * Get all model attributes
     * 
     * @return array All attributes
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
    
    /**
     * Magic getter
     * 
     * @param string $key Attribute key
     * @return mixed Attribute value
     */
    public function __get(string $key)
    {
        return $this->getAttribute($key);
    }
    
    /**
     * Magic setter
     * 
     * @param string $key Attribute key
     * @param mixed $value Attribute value
     */
    public function __set(string $key, $value)
    {
        $this->setAttribute($key, $value);
    }
    
    /**
     * Check if attribute exists
     * 
     * @param string $key Attribute key
     * @return bool
     */
    public function __isset(string $key)
    {
        return isset($this->attributes[$key]);
    }
    
    /**
     * Validate model data against rules
     * 
     * @return bool True if validation passes
     */
    public function validate(): bool
    {
        $this->errors = [];
        
        foreach ($this->rules as $field => $rule) {
            $ruleArray = explode('|', $rule);
            
            foreach ($ruleArray as $singleRule) {
                // Check if rule has parameters
                if (strpos($singleRule, ':') !== false) {
                    [$ruleName, $ruleParam] = explode(':', $singleRule, 2);
                } else {
                    $ruleName = $singleRule;
                    $ruleParam = null;
                }
                
                // Apply rule
                switch ($ruleName) {
                    case 'required':
                        if (!isset($this->attributes[$field]) || trim((string) $this->attributes[$field]) === '') {
                            $this->errors[$field][] = "$field is required";
                        }
                        break;
                    
                    case 'min':
                        if (isset($this->attributes[$field]) && strlen((string) $this->attributes[$field]) < (int) $ruleParam) {
                            $this->errors[$field][] = "$field must be at least $ruleParam characters";
                        }
                        break;
                        
                    case 'max':
                        if (isset($this->attributes[$field]) && strlen((string) $this->attributes[$field]) > (int) $ruleParam) {
                            $this->errors[$field][] = "$field must be less than $ruleParam characters";
                        }
                        break;
                        
                    case 'email':
                        if (isset($this->attributes[$field]) && !filter_var($this->attributes[$field], FILTER_VALIDATE_EMAIL)) {
                            $this->errors[$field][] = "$field must be a valid email address";
                        }
                        break;
                        
                    case 'date':
                        if (isset($this->attributes[$field]) && !strtotime($this->attributes[$field])) {
                            $this->errors[$field][] = "$field must be a valid date";
                        }
                        break;
                        
                    case 'numeric':
                        if (isset($this->attributes[$field]) && !is_numeric($this->attributes[$field])) {
                            $this->errors[$field][] = "$field must be numeric";
                        }
                        break;
                        
                    case 'in':
                        $allowedValues = explode(',', $ruleParam);
                        if (isset($this->attributes[$field]) && !in_array($this->attributes[$field], $allowedValues)) {
                            $this->errors[$field][] = "$field must be one of: " . implode(', ', $allowedValues);
                        }
                        break;
                }
            }
        }
        
        return empty($this->errors);
    }
    
    /**
     * Get validation errors
     * 
     * @return array Validation errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    /**
     * Save model to database
     * 
     * @return bool True if save was successful
     */
    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }
        
        if (isset($this->attributes[$this->primaryKey]) && !empty($this->attributes[$this->primaryKey])) {
            return $this->update();
        }
        
        return $this->insert();
    }
    
    /**
     * Insert a new record
     * 
     * @return bool True if insert was successful
     */
    protected function insert(): bool
    {
        $attributes = $this->attributes;
        
        // Remove primary key if it's empty
        if (isset($attributes[$this->primaryKey]) && empty($attributes[$this->primaryKey])) {
            unset($attributes[$this->primaryKey]);
        }
        
        $columns = array_keys($attributes);
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
            
            foreach ($attributes as $column => $value) {
                $stmt->bindValue(":$column", $value);
            }
            
            $result = $stmt->execute();
            
            if ($result) {
                $this->attributes[$this->primaryKey] = (int) $this->db->lastInsertId();
            }
            
            return $result;
        } catch (\PDOException $e) {
            $this->errors['database'][] = $e->getMessage();
            return false;
        }
    }
    
    /**
     * Update an existing record
     * 
     * @return bool True if update was successful
     */
    protected function update(): bool
    {
        $attributes = $this->attributes;
        
        // Get primary key value
        $id = $attributes[$this->primaryKey];
        unset($attributes[$this->primaryKey]);
        
        $setStatements = array_map(function ($column) {
            return "$column = :$column";
        }, array_keys($attributes));
        
        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s = :id",
            $this->table,
            implode(', ', $setStatements),
            $this->primaryKey
        );
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id);
            
            foreach ($attributes as $column => $value) {
                $stmt->bindValue(":$column", $value);
            }
            
            return $stmt->execute();
        } catch (\PDOException $e) {
            $this->errors['database'][] = $e->getMessage();
            return false;
        }
    }
    
    /**
     * Find a record by ID
     * 
     * @param int $id Record ID
     * @return self|null Model instance or null if not found
     */
    public static function find(int $id): ?self
    {
        $instance = new static();
        
        $sql = sprintf(
            "SELECT * FROM %s WHERE %s = :id AND is_deleted = 0 LIMIT 1",
            $instance->table,
            $instance->primaryKey
        );
        
        try {
            $stmt = $instance->db->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            
            $record = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$record) {
                return null;
            }
            
            return $instance->fill($record);
        } catch (\PDOException $e) {
            $instance->errors['database'][] = $e->getMessage();
            return null;
        }
    }
    
    /**
     * Get all records
     * 
     * @return array Array of model instances
     */
    public static function all(): array
    {
        $instance = new static();
        
        $sql = sprintf(
            "SELECT * FROM %s WHERE is_deleted = 0",
            $instance->table
        );
        
        try {
            $stmt = $instance->db->prepare($sql);
            $stmt->execute();
            
            $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $models = [];
            
            foreach ($records as $record) {
                $models[] = (new static())->fill($record);
            }
            
            return $models;
        } catch (\PDOException $e) {
            $instance->errors['database'][] = $e->getMessage();
            return [];
        }
    }
    
    /**
     * Delete a record (soft delete)
     * 
     * @return bool True if delete was successful
     */
    public function delete(): bool
    {
        if (!isset($this->attributes[$this->primaryKey])) {
            return false;
        }
        
        $sql = sprintf(
            "UPDATE %s SET is_deleted = 1, updated_at = CURRENT_TIMESTAMP WHERE %s = :id",
            $this->table,
            $this->primaryKey
        );
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $this->attributes[$this->primaryKey]);
            
            return $stmt->execute();
        } catch (\PDOException $e) {
            $this->errors['database'][] = $e->getMessage();
            return false;
        }
    }
    
    /**
     * Begin a database transaction
     * 
     * @return bool
     */
    public static function beginTransaction(): bool
    {
        $instance = new static();
        return $instance->db->beginTransaction();
    }
    
    /**
     * Commit a database transaction
     * 
     * @return bool
     */
    public static function commit(): bool
    {
        $instance = new static();
        return $instance->db->commit();
    }
    
    /**
     * Rollback a database transaction
     * 
     * @return bool
     */
    public static function rollback(): bool
    {
        $instance = new static();
        return $instance->db->rollBack();
    }
}
