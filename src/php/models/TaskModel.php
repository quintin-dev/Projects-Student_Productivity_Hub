<?php
/**
 * Task Model
 * 
 * Model for task management functionality
 * 
 * @package ProductivityHub\Models
 * @author Edwardking (Edd)
 * @version 1.0
 */

declare(strict_types=1);

namespace ProductivityHub\Models;

use ProductivityHub\Core\AbstractModel;

class TaskModel extends AbstractModel
{
    /**
     * Database table name
     * 
     * @var string
     */
    protected string $table = 'tasks';
    
    /**
     * Validation rules
     * 
     * @var array
     */
    protected array $rules = [
        'title' => 'required|max:255',
        'description' => 'max:1000',
        'category_id' => 'numeric',
        'priority' => 'in:low,medium,high,urgent',
        'status' => 'in:pending,in_progress,completed,cancelled',
        'due_date' => 'date'
    ];
    
    /**
     * Get task status options
     * 
     * @return array Status options
     */
    public static function getStatusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled'
        ];
    }
    
    /**
     * Get task priority options
     * 
     * @return array Priority options
     */
    public static function getPriorityOptions(): array
    {
        return [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'urgent' => 'Urgent'
        ];
    }
    
    /**
     * Get category name
     * 
     * @return string|null Category name
     */
    public function getCategoryName(): ?string
    {
        if (!isset($this->attributes['category_id']) || empty($this->attributes['category_id'])) {
            return null;
        }
        
        $categoryModel = CategoryModel::find((int) $this->attributes['category_id']);
        
        return $categoryModel ? $categoryModel->name : null;
    }
    
    /**
     * Mark task as completed
     * 
     * @return bool True if successful
     */
    public function markAsCompleted(): bool
    {
        $this->attributes['status'] = 'completed';
        $this->attributes['completed_at'] = date('Y-m-d H:i:s');
        
        return $this->save();
    }
    
    /**
     * Mark task as in progress
     * 
     * @return bool True if successful
     */
    public function markAsInProgress(): bool
    {
        $this->attributes['status'] = 'in_progress';
        
        return $this->save();
    }
    
    /**
     * Check if task is overdue
     * 
     * @return bool True if task is overdue
     */
    public function isOverdue(): bool
    {
        if (!isset($this->attributes['due_date']) || empty($this->attributes['due_date'])) {
            return false;
        }
        
        if ($this->attributes['status'] === 'completed' || $this->attributes['status'] === 'cancelled') {
            return false;
        }
        
        $dueDate = strtotime($this->attributes['due_date']);
        $now = time();
        
        return $dueDate < $now;
    }
    
    /**
     * Get due date formatted
     * 
     * @param string $format Date format
     * @return string|null Formatted date
     */
    public function getDueDate(string $format = 'Y-m-d'): ?string
    {
        if (!isset($this->attributes['due_date']) || empty($this->attributes['due_date'])) {
            return null;
        }
        
        return date($format, strtotime($this->attributes['due_date']));
    }
    
    /**
     * Get created date formatted
     * 
     * @param string $format Date format
     * @return string|null Formatted date
     */
    public function getCreatedAt(string $format = 'Y-m-d H:i'): ?string
    {
        if (!isset($this->attributes['created_at']) || empty($this->attributes['created_at'])) {
            return null;
        }
        
        return date($format, strtotime($this->attributes['created_at']));
    }
    
    /**
     * Get completed date formatted
     * 
     * @param string $format Date format
     * @return string|null Formatted date
     */
    public function getCompletedAt(string $format = 'Y-m-d H:i'): ?string
    {
        if (!isset($this->attributes['completed_at']) || empty($this->attributes['completed_at'])) {
            return null;
        }
        
        return date($format, strtotime($this->attributes['completed_at']));
    }
    
    /**
     * Override save method to set updated_at
     * 
     * @return bool True if save was successful
     */
    public function save(): bool
    {
        $this->attributes['updated_at'] = date('Y-m-d H:i:s');
        
        return parent::save();
    }
}
