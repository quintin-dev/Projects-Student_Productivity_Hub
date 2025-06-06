<?php
/**
 * Task Controller
 * 
 * Controller for task management functionality
 * 
 * @package ProductivityHub\Controllers
 * @author Edwardking (Edd)
 * @version 1.0
 */

declare(strict_types=1);

namespace ProductivityHub\Controllers;

use ProductivityHub\Core\AbstractController;
use ProductivityHub\Models\TaskModel;
use ProductivityHub\Models\CategoryModel;
use ProductivityHub\Repositories\TaskRepository;
use ProductivityHub\Core\Security;

class TaskController extends AbstractController
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
        $this->taskRepository = new TaskRepository();
    }    /**
     * Display task list
     * 
     * @return string Rendered HTML
     */
    public function index(): string
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
        
        // Get tasks
        $tasks = $this->taskRepository->findAll();
        
        // Get categories for filter dropdown
        $categories = $this->taskRepository->query("SELECT * FROM categories WHERE is_deleted = 0");
        
        // Render view
        $content = $this->render('tasks/index', [
            'tasks' => $tasks,
            'categories' => $categories
        ]);
        
        // Render with layout
        return $this->render('layouts/main', [
            'title' => 'Tasks | Student Productivity Hub',
            'content' => $content,
            'connectionStatus' => 'Connected'
        ]);
    }
      /**
     * Display task details
     * 
     * @param int $id Task ID
     * @return string Rendered HTML
     */
    public function show(int $id): string
    {
        $task = $this->taskRepository->findById($id);
        
        if (!$task) {
            // Redirect to task list
            $this->redirect('/tasks');
        }
        
        // Render view
        $content = $this->render('tasks/show', [
            'task' => $task
        ]);
        
        // Render with layout
        return $this->render('layouts/main', [
            'title' => 'Task Details | Student Productivity Hub',
            'content' => $content,
            'connectionStatus' => 'Connected'
        ]);
    }
      /**
     * Display task creation form
     * 
     * @return string Rendered HTML
     */
    public function create(): string
    {
        // Get categories for dropdown
        $categories = $this->taskRepository->query("SELECT * FROM categories WHERE is_deleted = 0");
        
        // Render view
        $content = $this->render('tasks/create', [
            'categories' => $categories,
            'errors' => []
        ]);
        
        // Render with layout
        return $this->render('layouts/main', [
            'title' => 'Create Task | Student Productivity Hub',
            'content' => $content,
            'connectionStatus' => 'Connected'
        ]);
    }    /**
     * Store a new task
     * 
     * @return string|null Rendered HTML or redirect
     */
    public function store()
    {
        // Validate CSRF token
        $csrfToken = $this->getParam('csrf_token');
        
        if (!Security::validateCsrfToken($csrfToken)) {
            // Redirect to task list
            $this->redirect('/tasks');
        }
        
        // Get request data
        $data = $this->getRequestData();
        
        // Create task model and validate
        $taskModel = new TaskModel();
        $taskModel->fill($data);
        
        // Validate the task data
        if (!$taskModel->validate()) {
            // Get categories for dropdown
            $categories = $this->taskRepository->query("SELECT * FROM categories WHERE is_deleted = 0");
            
            // Render form with errors
            $content = $this->render('tasks/create', [
                'categories' => $categories,
                'errors' => $taskModel->getErrors()
            ]);
            
            // Render with layout
            return $this->render('layouts/main', [
                'title' => 'Create Task | Student Productivity Hub',
                'content' => $content,
                'connectionStatus' => 'Connected'
            ]);
        }
        
        // Save the task
        $this->taskRepository->create($taskModel->getAttributes());
        
        // Redirect to tasks list
        $this->redirect('/tasks');
        return null;
    }
      /**
     * Display task edit form
     * 
     * @param int $id Task ID
     * @return string Rendered HTML
     */
    public function edit(int $id): string
    {
        $task = $this->taskRepository->findById($id);
        
        if (!$task) {
            // Redirect to task list
            $this->redirect('/tasks');
        }
        
        // Get categories for dropdown
        $categories = $this->taskRepository->query("SELECT * FROM categories WHERE is_deleted = 0");
        
        // Render view
        $content = $this->render('tasks/edit', [
            'task' => $task,
            'categories' => $categories,
            'errors' => []
        ]);
        
        // Render with layout
        return $this->render('layouts/main', [
            'title' => 'Edit Task | Student Productivity Hub',
            'content' => $content,
            'connectionStatus' => 'Connected'
        ]);
    }
    
    /**
     * Update an existing task
     * 
     * @param int $id Task ID
     * @return string|null Rendered HTML or redirect
     */
    public function update(int $id)
    {
        // Validate CSRF token
        $csrfToken = $this->getParam('csrf_token');
        
        if (!Security::validateCsrfToken($csrfToken)) {
            // Redirect to task list
            $this->redirect('/tasks');
        }
        
        // Get existing task
        $task = $this->taskRepository->findById($id);
        
        if (!$task) {
            // Redirect to task list
            $this->redirect('/tasks');
        }
        
        // Get request data
        $data = $this->getRequestData();
        
        // Create task model and validate
        $taskModel = new TaskModel();
        $taskModel->fill($task); // Fill with existing data first
        $taskModel->fill($data); // Override with new data
        
        // Validate the task data
        if (!$taskModel->validate()) {
            // Get categories for dropdown
            $categories = $this->taskRepository->query("SELECT * FROM categories WHERE is_deleted = 0");
            
            // Render form with errors
            $content = $this->render('tasks/edit', [
                'task' => $task,
                'categories' => $categories,
                'errors' => $taskModel->getErrors()
            ]);
            
            // Render with layout
            return $this->render('layouts/main', [
                'title' => 'Edit Task | Student Productivity Hub',
                'content' => $content,
                'connectionStatus' => 'Connected'
            ]);
        }
        
        // Update the task
        $this->taskRepository->update($id, $taskModel->getAttributes());
        
        // Redirect to task details
        $this->redirect('/tasks/' . $id);
        return null;
    }
    
    /**
     * Delete a task
     * 
     * @param int $id Task ID
     * @return void
     */
    public function destroy(int $id): void
    {
        // Validate CSRF token if not AJAX
        if (!$this->isAjax()) {
            $csrfToken = $this->getParam('csrf_token');
            
            if (!Security::validateCsrfToken($csrfToken)) {
                // Redirect to task list
                $this->redirect('/tasks');
            }
        }
        
        // Delete the task
        $success = $this->taskRepository->delete($id);
        
        // Check if AJAX request
        if ($this->isAjax()) {
            $this->json([
                'status' => $success ? 'success' : 'error',
                'message' => $success ? 'Task deleted successfully' : 'Failed to delete task'
            ]);
        }
        
        // Redirect to tasks list
        $this->redirect('/tasks');
    }
    
    /**
     * Display task statistics
     * 
     * @return void
     */
    public function stats(): void
    {
        $stats = $this->taskRepository->getTaskStatistics();
        
        if ($this->isAjax()) {
            $this->json([
                'success' => true,
                'data' => $stats
            ]);
        }
        
        // Render view
        $content = $this->render('tasks/stats', [
            'stats' => $stats
        ]);
        
        echo $content;
    }
    
    /**
     * Mark task as completed
     * 
     * @param int $id Task ID
     * @return void
     */
    public function complete(int $id): void
    {
        // Validate CSRF token
        $csrfToken = $this->getParam('csrf_token');
        
        if (!Security::validateCsrfToken($csrfToken)) {
            if ($this->isAjax()) {
                $this->json([
                    'success' => false,
                    'message' => 'Invalid CSRF token'
                ], 403);
            }
            
            // Redirect to task list
            $this->redirect('/tasks');
        }
        
        $task = TaskModel::find($id);
        
        if (!$task) {
            if ($this->isAjax()) {
                $this->json([
                    'success' => false,
                    'message' => 'Task not found'
                ], 404);
            }
            
            // Redirect to task list
            $this->redirect('/tasks');
        }
        
        // Mark as completed
        if (!$task->markAsCompleted()) {
            if ($this->isAjax()) {
                $this->json([
                    'success' => false,
                    'message' => 'Failed to mark task as completed',
                    'errors' => $task->getErrors()
                ], 500);
            }
            
            // Redirect to task details
            $this->redirect("/tasks/{$task->id}");
        }
        
        // Log task completion
        $this->taskRepository->logTaskActivity($task->id, 'complete', 'Task marked as completed');
        
        if ($this->isAjax()) {
            $this->json([
                'success' => true,
                'message' => 'Task marked as completed',
                'data' => $task->getAttributes()
            ]);
        }
        
        // Redirect to task list
        $this->redirect('/tasks');
    }
}
