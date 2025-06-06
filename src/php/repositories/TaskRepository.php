<?php
/**
 * Task Repository
 * 
 * Repository for task data access
 * 
 * @package ProductivityHub\Repositories
 * @author Edwardking (Edd)
 * @version 1.0
 */

declare(strict_types=1);

namespace ProductivityHub\Repositories;

use ProductivityHub\Core\AbstractRepository;

class TaskRepository extends AbstractRepository
{
    /**
     * Database table name
     * 
     * @var string
     */
    protected string $table = 'tasks';
    
    /**
     * Find tasks by category
     * 
     * @param int $categoryId Category ID
     * @return array Task records
     */
    public function findByCategory(int $categoryId): array
    {
        return $this->findAll([
            'where' => ['category_id' => $categoryId]
        ]);
    }
    
    /**
     * Find tasks by status
     * 
     * @param string $status Task status
     * @return array Task records
     */
    public function findByStatus(string $status): array
    {
        return $this->findAll([
            'where' => ['status' => $status]
        ]);
    }
    
    /**
     * Find tasks by priority
     * 
     * @param string $priority Task priority
     * @return array Task records
     */
    public function findByPriority(string $priority): array
    {
        return $this->findAll([
            'where' => ['priority' => $priority]
        ]);
    }
    
    /**
     * Find overdue tasks
     * 
     * @return array Task records
     */
    public function findOverdueTasks(): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE due_date < CURRENT_DATE 
                AND status NOT IN ('completed', 'cancelled') 
                AND is_deleted = 0";
                
        return $this->query($sql) ?? [];
    }
    
    /**
     * Find upcoming tasks
     * 
     * @param int $days Number of days ahead
     * @return array Task records
     */
    public function findUpcomingTasks(int $days = 7): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE due_date BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL :days DAY) 
                AND status NOT IN ('completed', 'cancelled') 
                AND is_deleted = 0";
                
        return $this->query($sql, [':days' => $days]) ?? [];
    }
    
    /**
     * Find tasks due today
     * 
     * @return array Task records
     */
    public function findTasksDueToday(): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE DATE(due_date) = CURRENT_DATE 
                AND status NOT IN ('completed', 'cancelled') 
                AND is_deleted = 0";
                
        return $this->query($sql) ?? [];
    }
    
    /**
     * Search tasks by keyword
     * 
     * @param string $keyword Search keyword
     * @return array Task records
     */
    public function searchTasks(string $keyword): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE (title LIKE :keyword OR description LIKE :keyword) 
                AND is_deleted = 0";
                
        return $this->query($sql, [':keyword' => "%{$keyword}%"]) ?? [];
    }
    
    /**
     * Get tasks with pagination
     * 
     * @param int $page Page number
     * @param int $perPage Items per page
     * @param array $filters Additional filters
     * @return array Task records and pagination info
     */
    public function getPaginatedTasks(int $page = 1, int $perPage = 10, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table} WHERE is_deleted = 0";
        $countSql = "SELECT COUNT(*) FROM {$this->table} WHERE is_deleted = 0";
        $params = [];
        
        // Add filters
        if (!empty($filters)) {
            foreach ($filters as $column => $value) {
                if ($value !== null && $value !== '') {
                    $sql .= " AND $column = :$column";
                    $countSql .= " AND $column = :$column";
                    $params[":$column"] = $value;
                }
            }
        }
        
        // Add ordering
        $sql .= " ORDER BY due_date ASC, priority DESC";
        
        // Add pagination
        $sql .= " LIMIT :limit OFFSET :offset";
        
        // Execute count query
        $stmt = $this->db->prepare($countSql);
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        $stmt->execute();
        $totalItems = (int) $stmt->fetchColumn();
        
        // Execute paginated query
        $stmt = $this->db->prepare($sql);
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Calculate pagination info
        $totalPages = ceil($totalItems / $perPage);
        
        return [
            'items' => $items,
            'pagination' => [
                'total' => $totalItems,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => $totalPages,
                'has_more' => $page < $totalPages
            ]
        ];
    }
    
    /**
     * Get task statistics
     * 
     * @return array Task statistics
     */
    public function getTaskStatistics(): array
    {
        $stats = [
            'total' => 0,
            'pending' => 0,
            'in_progress' => 0,
            'completed' => 0,
            'cancelled' => 0,
            'overdue' => 0,
            'due_today' => 0,
            'by_priority' => [
                'low' => 0,
                'medium' => 0,
                'high' => 0,
                'urgent' => 0
            ]
        ];
        
        // Get total counts by status
        $sql = "SELECT status, COUNT(*) as count FROM {$this->table} WHERE is_deleted = 0 GROUP BY status";
        $statusStats = $this->query($sql) ?? [];
        
        foreach ($statusStats as $stat) {
            $stats[$stat['status']] = (int) $stat['count'];
            $stats['total'] += (int) $stat['count'];
        }
        
        // Get overdue count
        $sql = "SELECT COUNT(*) FROM {$this->table} 
                WHERE due_date < CURRENT_DATE 
                AND status NOT IN ('completed', 'cancelled') 
                AND is_deleted = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['overdue'] = (int) $stmt->fetchColumn();
        
        // Get due today count
        $sql = "SELECT COUNT(*) FROM {$this->table} 
                WHERE DATE(due_date) = CURRENT_DATE 
                AND status NOT IN ('completed', 'cancelled') 
                AND is_deleted = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['due_today'] = (int) $stmt->fetchColumn();
        
        // Get counts by priority
        $sql = "SELECT priority, COUNT(*) as count FROM {$this->table} 
                WHERE is_deleted = 0 GROUP BY priority";
        $priorityStats = $this->query($sql) ?? [];
        
        foreach ($priorityStats as $stat) {
            $stats['by_priority'][$stat['priority']] = (int) $stat['count'];
        }
        
        return $stats;
    }
    
    /**
     * Log task activity
     * 
     * @param int $taskId Task ID
     * @param string $action Action performed
     * @param string $details Action details
     * @return bool True if successful
     */
    public function logTaskActivity(int $taskId, string $action, string $details = ''): bool
    {
        $sql = "INSERT INTO audit_logs (table_name, record_id, action, details, created_at)
                VALUES (:table_name, :record_id, :action, :details, CURRENT_TIMESTAMP)";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':table_name', $this->table);
        $stmt->bindValue(':record_id', $taskId);
        $stmt->bindValue(':action', $action);
        $stmt->bindValue(':details', $details);
        
        return $stmt->execute();
    }
}
