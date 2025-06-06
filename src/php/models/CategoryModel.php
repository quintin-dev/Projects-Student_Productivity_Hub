<?php
/**
 * Category Model
 * 
 * Model for task categories
 * 
 * @package ProductivityHub\Models
 * @author Edwardking (Edd)
 * @version 1.0
 */

declare(strict_types=1);

namespace ProductivityHub\Models;

use ProductivityHub\Core\AbstractModel;

class CategoryModel extends AbstractModel
{
    /**
     * Database table name
     * 
     * @var string
     */
    protected string $table = 'categories';
    
    /**
     * Validation rules
     * 
     * @var array
     */
    protected array $rules = [
        'name' => 'required|max:100',
        'color' => 'max:7',
        'icon' => 'max:50',
        'description' => 'max:255'
    ];
    
    /**
     * Get default categories
     * 
     * @return array Default categories
     */
    public static function getDefaultCategories(): array
    {
        return [
            [
                'name' => 'Work',
                'color' => '#e74c3c',
                'icon' => 'briefcase',
                'description' => 'Work-related tasks'
            ],
            [
                'name' => 'Study',
                'color' => '#3498db',
                'icon' => 'book',
                'description' => 'Study and education tasks'
            ],
            [
                'name' => 'Personal',
                'color' => '#2ecc71',
                'icon' => 'user',
                'description' => 'Personal tasks and goals'
            ],
            [
                'name' => 'Health',
                'color' => '#9b59b6',
                'icon' => 'heart',
                'description' => 'Health and wellness tasks'
            ],
            [
                'name' => 'Errands',
                'color' => '#f1c40f',
                'icon' => 'shopping-cart',
                'description' => 'Errands and shopping tasks'
            ]
        ];
    }
    
    /**
     * Create default categories if not exist
     * 
     * @return bool True if successful
     */
    public static function createDefaultCategories(): bool
    {
        $defaultCategories = self::getDefaultCategories();
        $success = true;
        
        foreach ($defaultCategories as $categoryData) {
            // Check if category exists
            $existingCategories = self::findByName($categoryData['name']);
            
            if (empty($existingCategories)) {
                $category = new self($categoryData);
                
                if (!$category->save()) {
                    $success = false;
                }
            }
        }
        
        return $success;
    }
    
    /**
     * Find categories by name
     * 
     * @param string $name Category name
     * @return array Categories
     */
    public static function findByName(string $name): array
    {
        $instance = new self();
        
        $sql = "SELECT * FROM {$instance->table} WHERE name = :name AND is_active = 1";
        $stmt = $instance->db->prepare($sql);
        $stmt->bindValue(':name', $name);
        $stmt->execute();
        
        $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $categories = [];
        
        foreach ($records as $record) {
            $categories[] = (new self())->fill($record);
        }
        
        return $categories;
    }
    
    /**
     * Get task count for this category
     * 
     * @return int Task count
     */
    public function getTaskCount(): int
    {
        if (!isset($this->attributes[$this->primaryKey])) {
            return 0;
        }
        
        $sql = "SELECT COUNT(*) FROM tasks WHERE category_id = :category_id AND is_deleted = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':category_id', $this->attributes[$this->primaryKey]);
        $stmt->execute();
        
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Get tasks for this category
     * 
     * @return array Tasks
     */
    public function getTasks(): array
    {
        if (!isset($this->attributes[$this->primaryKey])) {
            return [];
        }
        
        $sql = "SELECT * FROM tasks WHERE category_id = :category_id AND is_deleted = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':category_id', $this->attributes[$this->primaryKey]);
        $stmt->execute();
        
        $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $tasks = [];
        
        foreach ($records as $record) {
            $tasks[] = (new TaskModel())->fill($record);
        }
        
        return $tasks;
    }
}
