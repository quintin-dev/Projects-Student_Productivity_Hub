<?php
/**
 * Task API Controller
 * 
 * Controller for task management API endpoints
 * 
 * @package ProductivityHub\Controllers\Api
 * @author Edwardking (Edd)
 * @version 1.0
 */

declare(strict_types=1);

namespace ProductivityHub\Controllers\Api;

use ProductivityHub\Models\TaskModel;
use ProductivityHub\Repositories\TaskRepository;
use ProductivityHub\Core\Security;

class TaskApiController extends ApiController
{
    /**
     * Task repository
     * 
     * @var TaskRepository
     */
    private TaskRepository $taskRepository;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->taskRepository = new TaskRepository();
    }
    
    /**
     * Get all tasks
     * 
     * @return void
     */
    public function index(): void
    {
        $page = (int) ($this->getParam('page', 1));
        $perPage = (int) ($this->getParam('per_page', 10));
        
        // Get filters from request
        $filters = [
            'status' => $this->getParam('status'),
            'priority' => $this->getParam('priority'),
            'category_id' => $this->getParam('category_id')
        ];
        
        // Filter out empty values
        $filters = array_filter($filters, function ($value) {
            return $value !== null && $value !== '';
        });
        
        // Get paginated tasks
        $result = $this->taskRepository->getPaginatedTasks($page, $perPage, $filters);
        
        $this->success([
            'tasks' => $result['items'],
            'pagination' => $result['pagination']
        ]);
    }
    
    /**
     * Get task by ID
     * 
     * @param int $id Task ID
     * @return void
     */
    public function show(int $id): void
    {
        $task = TaskModel::find($id);
        
        if (!$task) {
            $this->error('Task not found', null, 404);
            return;
        }
        
        $this->success($task->getAttributes());
    }
    
    /**
     * Create a new task
     * 
     * @return void
     */
    public function store(): void
    {
        // Get JSON data
        $data = $this->getJsonData();
        
        // Validate request data
        $rules = [
            'title' => 'required|max:255',
            'description' => 'max:1000',
            'category_id' => 'numeric',
            'priority' => 'in:low,medium,high,urgent',
            'status' => 'in:pending,in_progress,completed,cancelled',
            'due_date' => 'date'
        ];
        
        if (!$this->validateRequest($data, $rules)) {
            return;
        }
        
        // Create new task
        $task = new TaskModel($data);
        
        if (!$task->save()) {
            $this->error('Failed to create task', $task->getErrors(), 500);
            return;
        }
        
        // Log task creation
        $this->taskRepository->logTaskActivity($task->id, 'create', 'Task created via API');
        
        $this->success($task->getAttributes(), 'Task created successfully', 201);
    }
    
    /**
     * Update a task
     * 
     * @param int $id Task ID
     * @return void
     */
    public function update(int $id): void
    {
        $task = TaskModel::find($id);
        
        if (!$task) {
            $this->error('Task not found', null, 404);
            return;
        }
        
        // Get JSON data
        $data = $this->getJsonData();
        
        // Validate request data
        $rules = [
            'title' => 'max:255',
            'description' => 'max:1000',
            'category_id' => 'numeric',
            'priority' => 'in:low,medium,high,urgent',
            'status' => 'in:pending,in_progress,completed,cancelled',
            'due_date' => 'date'
        ];
        
        if (!$this->validateRequest($data, $rules)) {
            return;
        }
        
        // Check if status changed to completed
        if (isset($data['status']) && $data['status'] === 'completed' && $task->status !== 'completed') {
            $data['completed_at'] = date('Y-m-d H:i:s');
        }
        
        // Update task
        $task->fill($data);
        
        if (!$task->save()) {
            $this->error('Failed to update task', $task->getErrors(), 500);
            return;
        }
        
        // Log task update
        $this->taskRepository->logTaskActivity($task->id, 'update', 'Task updated via API');
        
        $this->success($task->getAttributes(), 'Task updated successfully');
    }
    
    /**
     * Delete a task
     * 
     * @param int $id Task ID
     * @return void
     */
    public function delete(int $id): void
    {
        $task = TaskModel::find($id);
        
        if (!$task) {
            $this->error('Task not found', null, 404);
            return;
        }
        
        if (!$task->delete()) {
            $this->error('Failed to delete task', $task->getErrors(), 500);
            return;
        }
        
        // Log task deletion
        $this->taskRepository->logTaskActivity($task->id, 'delete', 'Task deleted via API');
        
        $this->success(null, 'Task deleted successfully');
    }
    
    /**
     * Get task statistics
     * 
     * @return void
     */
    public function stats(): void
    {
        $stats = $this->taskRepository->getTaskStatistics();
        $this->success($stats);
    }
    
    /**
     * Mark task as completed
     * 
     * @param int $id Task ID
     * @return void
     */
    public function complete(int $id): void
    {
        $task = TaskModel::find($id);
        
        if (!$task) {
            $this->error('Task not found', null, 404);
            return;
        }
        
        if (!$task->markAsCompleted()) {
            $this->error('Failed to mark task as completed', $task->getErrors(), 500);
            return;
        }
        
        // Log task completion
        $this->taskRepository->logTaskActivity($task->id, 'complete', 'Task marked as completed via API');
        
        $this->success($task->getAttributes(), 'Task marked as completed');
    }
    
    /**
     * Get overdue tasks
     * 
     * @return void
     */
    public function overdue(): void
    {
        $tasks = $this->taskRepository->findOverdueTasks();
        $this->success($tasks);
    }
    
    /**
     * Get tasks due today
     * 
     * @return void
     */
    public function today(): void
    {
        $tasks = $this->taskRepository->findTasksDueToday();
        $this->success($tasks);
    }
    
    /**
     * Get upcoming tasks
     * 
     * @return void
     */
    public function upcoming(): void
    {
        $days = (int) ($this->getParam('days', 7));
        $tasks = $this->taskRepository->findUpcomingTasks($days);
        $this->success($tasks);
    }
    
    /**
     * Search tasks
     * 
     * @return void
     */
    public function search(): void
    {
        $keyword = $this->getParam('q', '');
        
        if (empty($keyword)) {
            $this->error('Search keyword is required');
            return;
        }
        
        $tasks = $this->taskRepository->searchTasks($keyword);
        $this->success($tasks);
    }
}
