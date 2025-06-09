<?php
/**
 * Task Show View
 * 
 * Displays details of a specific task
 * 
 * @var array $task Task data
 */
?>

<div class="task-detail-page">
    <header class="page-header">
        <h2 class="page-title">Task Details</h2>
        <div class="page-actions">
            <a href="/tasks" class="btn btn--secondary">
                <span class="btn__icon">←</span>
                Back to Tasks
            </a>
            <a href="/tasks/<?= htmlspecialchars($task['id'] ?? '') ?>/edit" class="btn btn--primary">
                <span class="btn__icon">✏️</span>
                Edit Task
            </a>
        </div>
    </header>
    
    <div class="task-detail">
        <div class="task-detail__header">
            <div class="task-detail__status status-<?= htmlspecialchars($task['status'] ?? '') ?>">
                <?= ucfirst(str_replace('_', ' ', htmlspecialchars($task['status'] ?? ''))) ?>
            </div>
            <div class="task-detail__priority priority-<?= htmlspecialchars($task['priority'] ?? '') ?>">
                <?= ucfirst(htmlspecialchars($task['priority'] ?? '')) ?>
            </div>
        </div>
        
        <h3 class="task-detail__title"><?= htmlspecialchars($task['title'] ?? '') ?></h3>
        
        <div class="task-detail__meta">
            <?php if (isset($task['category_name']) && $task['category_name']): ?>
            <div class="task-detail__category">
                <span class="meta-label">Category:</span>
                <span class="meta-value"><?= htmlspecialchars($task['category_name']) ?></span>
            </div>
            <?php endif; ?>
            
            <?php if (isset($task['due_date']) && $task['due_date']): ?>
            <div class="task-detail__due-date">
                <span class="meta-label">Due Date:</span>
                <span class="meta-value"><?= htmlspecialchars(date('F j, Y', strtotime($task['due_date']))) ?></span>
            </div>
            <?php endif; ?>
            
            <div class="task-detail__created">
                <span class="meta-label">Created:</span>
                <span class="meta-value"><?= htmlspecialchars(date('F j, Y', strtotime($task['created_at'] ?? 'now'))) ?></span>
            </div>
            
            <?php if (isset($task['updated_at']) && $task['updated_at']): ?>
            <div class="task-detail__updated">
                <span class="meta-label">Last Updated:</span>
                <span class="meta-value"><?= htmlspecialchars(date('F j, Y', strtotime($task['updated_at']))) ?></span>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if (isset($task['description']) && $task['description']): ?>
        <div class="task-detail__description">
            <h4 class="section-title">Description</h4>
            <div class="description-content">
                <?= nl2br(htmlspecialchars($task['description'])) ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="task-detail__actions">
            <button class="btn js-status-update" data-status="completed" 
                    <?= ($task['status'] ?? '') === 'completed' ? 'disabled' : '' ?>>
                Mark as Completed
            </button>
            <button class="btn js-status-update" data-status="in_progress"
                    <?= ($task['status'] ?? '') === 'in_progress' ? 'disabled' : '' ?>>
                Mark as In Progress
            </button>
            <button class="btn btn--danger js-delete-task">Delete Task</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const taskId = '<?= htmlspecialchars($task['id'] ?? '') ?>';
        
        // Handle status updates
        document.querySelectorAll('.js-status-update').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const newStatus = this.dataset.status;
                
                fetch(`/api/tasks/${taskId}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-Token': window.ProductivityHub.csrfToken,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        status: newStatus,
                        csrf_token: window.ProductivityHub.csrfToken
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Reload the page to show updated status
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the task status');
                });
            });
        });
        
        // Handle task deletion
        document.querySelector('.js-delete-task').addEventListener('click', function(e) {
            e.preventDefault();
            
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
                        // Redirect to tasks index
                        window.location.href = '/tasks';
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
</script>
