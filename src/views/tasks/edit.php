<?php
/**
 * Task Edit View
 * 
 * Form for editing an existing task
 * 
 * @var array $task Task data
 * @var array $categories Available categories
 * @var array $errors Validation errors
 */
?>

<div class="task-form-page">
    <header class="page-header">
        <h2 class="page-title">Edit Task</h2>
        <div class="page-actions">
            <a href="/tasks" class="btn btn--secondary">
                <span class="btn__icon">←</span>
                Back to Tasks
            </a>
        </div>
    </header>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert--error">
            <h4 class="alert__title">Error</h4>
            <ul class="alert__list">
                <?php foreach ($errors as $field => $fieldErrors): ?>
                    <?php foreach ($fieldErrors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form action="/tasks/<?= htmlspecialchars($task['id'] ?? '') ?>" method="POST" class="form task-form" id="edit-task-form">
        <!-- CSRF Protection -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <input type="hidden" name="_method" value="PUT">
        
        <div class="form-group">
            <label for="title" class="form-label">Title <span class="required">*</span></label>
            <input type="text" id="title" name="title" class="form-input <?= isset($errors['title']) ? 'form-input--error' : '' ?>" 
                   value="<?= htmlspecialchars($_POST['title'] ?? $task['title'] ?? '') ?>" required maxlength="255">
            <?php if (isset($errors['title'])): ?>
                <div class="form-error"><?= htmlspecialchars($errors['title'][0]) ?></div>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" name="description" class="form-textarea <?= isset($errors['description']) ? 'form-textarea--error' : '' ?>" 
                      rows="4"><?= htmlspecialchars($_POST['description'] ?? $task['description'] ?? '') ?></textarea>
            <?php if (isset($errors['description'])): ?>
                <div class="form-error"><?= htmlspecialchars($errors['description'][0]) ?></div>
            <?php endif; ?>
        </div>
        
        <div class="form-row">
            <div class="form-group form-group--half">
                <label for="category_id" class="form-label">Category</label>
                <select id="category_id" name="category_id" class="form-select <?= isset($errors['category_id']) ? 'form-select--error' : '' ?>">
                    <option value="">-- Select Category --</option>
                    <?php foreach ($categories ?? [] as $category): ?>
                        <option value="<?= htmlspecialchars($category['id']) ?>" 
                                <?= ($_POST['category_id'] ?? $task['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['category_id'])): ?>
                    <div class="form-error"><?= htmlspecialchars($errors['category_id'][0]) ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group form-group--half">
                <label for="due_date" class="form-label">Due Date</label>
                <input type="date" id="due_date" name="due_date" class="form-input <?= isset($errors['due_date']) ? 'form-input--error' : '' ?>" 
                       value="<?= htmlspecialchars($_POST['due_date'] ?? $task['due_date'] ?? '') ?>">
                <?php if (isset($errors['due_date'])): ?>
                    <div class="form-error"><?= htmlspecialchars($errors['due_date'][0]) ?></div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group form-group--half">
                <label for="priority" class="form-label">Priority <span class="required">*</span></label>
                <select id="priority" name="priority" class="form-select <?= isset($errors['priority']) ? 'form-select--error' : '' ?>" required>
                    <option value="">-- Select Priority --</option>
                    <option value="low" <?= ($_POST['priority'] ?? $task['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Low</option>
                    <option value="medium" <?= ($_POST['priority'] ?? $task['priority'] ?? '') === 'medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="high" <?= ($_POST['priority'] ?? $task['priority'] ?? '') === 'high' ? 'selected' : '' ?>>High</option>
                    <option value="urgent" <?= ($_POST['priority'] ?? $task['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>Urgent</option>
                </select>
                <?php if (isset($errors['priority'])): ?>
                    <div class="form-error"><?= htmlspecialchars($errors['priority'][0]) ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group form-group--half">
                <label for="status" class="form-label">Status <span class="required">*</span></label>
                <select id="status" name="status" class="form-select <?= isset($errors['status']) ? 'form-select--error' : '' ?>" required>
                    <option value="">-- Select Status --</option>
                    <option value="pending" <?= ($_POST['status'] ?? $task['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="in_progress" <?= ($_POST['status'] ?? $task['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="completed" <?= ($_POST['status'] ?? $task['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="cancelled" <?= ($_POST['status'] ?? $task['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
                <?php if (isset($errors['status'])): ?>
                    <div class="form-error"><?= htmlspecialchars($errors['status'][0]) ?></div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="form-group form-group--actions">
            <button type="submit" class="btn btn--primary">Update Task</button>
            <a href="/tasks" class="btn btn--secondary">Cancel</a>
            <button type="button" class="btn btn--danger js-delete-task" 
                    data-task-id="<?= htmlspecialchars($task['id'] ?? '') ?>">
                Delete Task
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle task deletion
        const deleteButton = document.querySelector('.js-delete-task');
        if (deleteButton) {
            deleteButton.addEventListener('click', function(e) {
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
        }
        
        // Handle form submission with AJAX
        const form = document.getElementById('edit-task-form');
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            const taskId = '<?= htmlspecialchars($task['id'] ?? '') ?>';
            
            // Convert FormData to JSON
            const data = {};
            formData.forEach((value, key) => {
                if (key !== '_method') {
                    data[key] = value;
                }
            });
            
            fetch(`/api/tasks/${taskId}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-Token': window.ProductivityHub.csrfToken,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Redirect to tasks index or show page
                    window.location.href = '/tasks';
                } else {
                    // Display validation errors
                    const errors = data.errors || {};
                    
                    // Reset previous errors
                    document.querySelectorAll('.form-error').forEach(el => el.remove());
                    document.querySelectorAll('.form-input--error, .form-select--error, .form-textarea--error')
                        .forEach(el => el.classList.remove('form-input--error', 'form-select--error', 'form-textarea--error'));
                    
                    // Add new errors
                    Object.keys(errors).forEach(field => {
                        const input = document.getElementById(field);
                        if (input) {
                            input.classList.add(`form-${input.tagName.toLowerCase()}--error`);
                            
                            const errorDiv = document.createElement('div');
                            errorDiv.className = 'form-error';
                            errorDiv.textContent = errors[field][0];
                            input.parentNode.appendChild(errorDiv);
                        }
                    });
                    
                    // Show alert
                    alert('Please fix the errors in the form');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the task');
            });
        });
    });
</script>
