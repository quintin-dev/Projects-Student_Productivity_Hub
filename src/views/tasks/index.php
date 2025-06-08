<?php
/**
 * Tasks Index View
 * 
 * Displays a list of all tasks
 * 
 * @var array $tasks Array of task data
 */
?>

<div class="tasks-page">
    <header class="page-header">
        <h2 class="page-title">Tasks</h2>
        <div class="page-actions">
            <a href="/tasks/create" class="btn btn--primary">
                <span class="btn__icon">+</span>
                Add New Task
            </a>
        </div>
    </header>

    <div class="filters-bar">
        <div class="filter-group">
            <label for="category-filter" class="filter-label">Category:</label>
            <select id="category-filter" class="form-select">
                <option value="">All Categories</option>
                <?php foreach ($categories ?? [] as $category): ?>
                <option value="<?= htmlspecialchars($category['id']) ?>">
                    <?= htmlspecialchars($category['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-group">
            <label for="priority-filter" class="filter-label">Priority:</label>
            <select id="priority-filter" class="form-select">
                <option value="">All Priorities</option>
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
            </select>
        </div>

        <div class="filter-group">
            <label for="status-filter" class="filter-label">Status:</label>
            <select id="status-filter" class="form-select">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>

        <div class="filter-group filter-group--search">
            <input type="text" id="task-search" class="form-input" placeholder="Search tasks...">
            <button type="button" class="btn btn--icon" aria-label="Search">
                <span class="icon">🔍</span>
            </button>
        </div>
    </div>

    <div class="tasks-container">
        <?php if (empty($tasks)): ?>
            <div class="empty-state">
                <div class="empty-state__icon">📝</div>
                <h3 class="empty-state__title">No Tasks Found</h3>
                <p class="empty-state__message">You don't have any tasks yet. Create your first task to get started.</p>
                <a href="/tasks/create" class="btn btn--primary">Add Your First Task</a>
            </div>
        <?php else: ?>
            <div class="task-list" id="task-list">
                <?php foreach ($tasks as $task): ?>
                    <div class="task-card" data-task-id="<?= htmlspecialchars($task['id']) ?>">
                        <div class="task-card__header">
                            <div class="task-card__priority priority-<?= htmlspecialchars($task['priority']) ?>">
                                <?= ucfirst(htmlspecialchars($task['priority'])) ?>
                            </div>
                            <div class="task-card__status status-<?= htmlspecialchars($task['status']) ?>">
                                <?= ucfirst(str_replace('_', ' ', htmlspecialchars($task['status']))) ?>
                            </div>
                        </div>
                        
                        <h3 class="task-card__title">
                            <a href="/tasks/<?= htmlspecialchars($task['id']) ?>">
                                <?= htmlspecialchars($task['title']) ?>
                            </a>
                        </h3>
                        
                        <?php if (!empty($task['description'])): ?>
                        <div class="task-card__description">
                            <?= htmlspecialchars(substr($task['description'], 0, 100)) ?>
                            <?= (strlen($task['description']) > 100) ? '...' : '' ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="task-card__meta">
                            <?php if (isset($task['category_name'])): ?>
                            <div class="task-card__category">
                                <span class="icon">🏷️</span>
                                <?= htmlspecialchars($task['category_name']) ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (isset($task['due_date']) && $task['due_date']): ?>
                            <div class="task-card__due-date">
                                <span class="icon">📅</span>
                                <?= htmlspecialchars(date('M j, Y', strtotime($task['due_date']))) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="task-card__actions">
                            <a href="/tasks/<?= htmlspecialchars($task['id']) ?>/edit" class="btn btn--small btn--icon" aria-label="Edit task">
                                <span class="icon">✏️</span>
                            </a>
                            <button type="button" class="btn btn--small btn--icon js-delete-task" 
                                    data-task-id="<?= htmlspecialchars($task['id']) ?>"
                                    aria-label="Delete task">
                                <span class="icon">🗑️</span>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle task deletion
        document.querySelectorAll('.js-delete-task').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const taskId = this.dataset.taskId;
                
                if (confirm('Are you sure you want to delete this task?')) {
                    fetch(`/api/tasks/${taskId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-Token': window.ProductivityHub.csrfToken,
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Remove the task card from the DOM
                            document.querySelector(`.task-card[data-task-id="${taskId}"]`).remove();
                            
                            // Show notification
                            alert('Task deleted successfully');
                            
                            // If no tasks left, reload to show empty state
                            if (document.querySelectorAll('.task-card').length === 0) {
                                window.location.reload();
                            }
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while deleting the task');
                    });
                }
            });
        });
        
        // Filter functionality will be implemented here
    });
</script>
