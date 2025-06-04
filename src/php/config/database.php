<?php
/**
 * Database Configuration for Student Productivity Hub
 * 
 * Local development configuration for Apache + MySQL setup
 * Environment: https://productivity_hub.local
 * 
 * @package ProductivityHub
 * @author Edwardking (Edd)
 * @version 1.0
 */

declare(strict_types=1);

namespace ProductivityHub\Config;

/**
 * Database configuration class
 * Handles connection to MySQL database with proper error handling
 */
class Database
{
    private static ?Database $instance = null;
    private ?\PDO $connection = null;
    
    // Database configuration for local environment
    private const DB_HOST = 'localhost';
    private const DB_NAME = 'productivity_hub';
    private const DB_USER = 'root';
    private const DB_PASS = 'MySql1234';
    private const DB_CHARSET = 'utf8mb4';
    private const DB_OPTIONS = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false,
        \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci'
    ];

    /**
     * Private constructor for singleton pattern
     */
    private function __construct()
    {
        $this->connect();
    }

    /**
     * Get database instance (singleton pattern)
     * 
     * @return Database
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }

    /**
     * Get PDO connection
     * 
     * @return \PDO
     */
    public function getConnection(): \PDO
    {
        if ($this->connection === null) {
            $this->connect();
        }
        
        return $this->connection;
    }

    /**
     * Establish database connection
     * 
     * @throws \PDOException
     */
    private function connect(): void
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                self::DB_HOST,
                self::DB_NAME,
                self::DB_CHARSET
            );
            
            $this->connection = new \PDO($dsn, self::DB_USER, self::DB_PASS, self::DB_OPTIONS);
            
            // Log successful connection (remove in production)
            error_log("Database connected successfully to " . self::DB_NAME);
            
        } catch (\PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new \PDOException("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Test database connection
     * 
     * @return bool
     */
    public function testConnection(): bool
    {
        try {
            $stmt = $this->getConnection()->query('SELECT 1');
            return $stmt !== false;
        } catch (\PDOException $e) {
            error_log("Database connection test failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Begin transaction
     * 
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->getConnection()->beginTransaction();
    }

    /**
     * Commit transaction
     * 
     * @return bool
     */
    public function commit(): bool
    {
        return $this->getConnection()->commit();
    }

    /**
     * Rollback transaction
     * 
     * @return bool
     */
    public function rollback(): bool
    {
        return $this->getConnection()->rollBack();
    }

    /**
     * Prevent cloning of singleton
     */
    private function __clone() {}

    /**
     * Prevent unserialization of singleton
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
