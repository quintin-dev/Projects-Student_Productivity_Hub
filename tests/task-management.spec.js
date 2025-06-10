// @ts-check
const { test, expect } = require('@playwright/test');

test.describe('Task Management System', () => {
  test.beforeEach(async ({ page }) => {
    // Navigate to the homepage
    await page.goto('https://productivity_hub.local');
  });

  test('should show the tasks page', async ({ page }) => {
    // Click on tasks link
    await page.click('a[href="/tasks"]');
    
    // Check that we're on the tasks page
    await expect(page).toHaveURL(/.*\/tasks/);
    await expect(page.locator('h2.page-title')).toContainText('Tasks');
  });

  test('should create a new task', async ({ page }) => {
    // Go to the create task page
    await page.click('a[href="/tasks"]');
    await page.click('a[href="/tasks/create"]');
    
    // Fill out the form
    await page.fill('#title', 'Test Task');
    await page.fill('#description', 'This is a test task created by Playwright');
    await page.selectOption('#priority', 'medium');
    await page.selectOption('#status', 'pending');
    await page.fill('#due_date', '2025-07-01');
    
    // Submit the form
    await page.click('button[type="submit"]');
    
    // Check that we're redirected to the tasks page
    await expect(page).toHaveURL(/.*\/tasks/);
    
    // Check that the new task appears in the list
    await expect(page.locator('.task-card__title')).toContainText('Test Task');
  });

  test('should edit an existing task', async ({ page }) => {
    // Go to the tasks page
    await page.click('a[href="/tasks"]');
    
    // Find and click on the first edit button
    await page.click('.task-card__actions a[href*="/edit"]:first-child');
    
    // Change the title
    await page.fill('#title', 'Updated Task Title');
    
    // Submit the form
    await page.click('button[type="submit"]');
    
    // Check that we're redirected to the task details page
    await expect(page).toHaveURL(/.*\/tasks\/\d+/);
    
    // Check that the title was updated
    await expect(page.locator('.task-detail__title')).toContainText('Updated Task Title');
  });

  test('should delete a task', async ({ page }) => {
    // Go to the tasks page
    await page.click('a[href="/tasks"]');
    
    // Count the number of tasks
    const initialTaskCount = await page.locator('.task-card').count();
    
    // Find and click the first delete button
    await page.click('.js-delete-task:first-child');
    
    // Confirm the deletion in the dialog
    page.on('dialog', dialog => dialog.accept());
    
    // Wait for the deletion to complete
    await page.waitForResponse(response => response.url().includes('/api/tasks/') && response.status() === 200);
    
    // Check that there's one less task
    const newTaskCount = await page.locator('.task-card').count();
    expect(newTaskCount).toBe(initialTaskCount - 1);
  });

  test('should mark a task as completed', async ({ page }) => {
    // Go to the tasks page
    await page.click('a[href="/tasks"]');
    
    // Click on the first task to view details
    await page.click('.task-card__title a:first-child');
    
    // Click the "Mark as Completed" button
    await page.click('button[data-status="completed"]');
    
    // Check that the status is now "Completed"
    await expect(page.locator('.task-detail__status')).toContainText('Completed');
  });

  test('should filter tasks by category', async ({ page }) => {
    // Go to the tasks page
    await page.click('a[href="/tasks"]');
    
    // Select a category from the dropdown
    await page.selectOption('#category-filter', { index: 1 }); // Select the first category
    
    // Wait for the filter to apply
    await page.waitForResponse(response => response.url().includes('/api/tasks') && response.status() === 200);
    
    // Check that the filtered tasks are displayed
    await expect(page.locator('.task-card')).toBeVisible();
  });

  test('should search for tasks', async ({ page }) => {
    // Go to the tasks page
    await page.click('a[href="/tasks"]');
    
    // Enter a search term
    await page.fill('#task-search', 'Test');
    await page.press('#task-search', 'Enter');
    
    // Wait for the search results
    await page.waitForResponse(response => response.url().includes('/api/tasks') && response.status() === 200);
    
    // Check that search results are displayed
    await expect(page.locator('.task-card__title')).toContainText('Test');
  });
});
