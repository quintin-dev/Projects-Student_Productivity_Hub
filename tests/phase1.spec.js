// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Student Productivity Hub - Phase 1 Test Suite
 * Tests the foundation and structure of the PWA
 */
test.describe('Student Productivity Hub - Phase 1 Tests', () => {
  
  test.beforeEach(async ({ page }) => {
    // Navigate to the app
    await page.goto('https://productivity_hub.local/');
  });

  test('Homepage loads with correct title and structure', async ({ page }) => {
    // Check title
    await expect(page).toHaveTitle('Student Productivity Hub');
    
    // Check key elements exist
    await expect(page.locator('.header__title')).toHaveText('Productivity Hub');
    await expect(page.locator('.welcome__title')).toHaveText('Welcome to Your Productivity Hub');
    
    // Check navigation links
    const navLinks = page.locator('.nav-link');
    await expect(navLinks).toHaveCount(4);
    
    // Check quick action buttons
    const actionCards = page.locator('.action-card');
    await expect(actionCards).toHaveCount(4);
  });

  test('Database connection is established', async ({ page }) => {
    // Check database connection status
    await expect(page.locator('#db-status')).toHaveText('Connected');
  });

  test('PWA manifest is properly configured', async ({ page, context }) => {
    // Navigate to manifest.json
    const manifestPage = await context.newPage();
    await manifestPage.goto('https://productivity_hub.local/manifest.json');
    
    // Get the content
    const content = await manifestPage.textContent('body');
    const manifest = JSON.parse(content || '{}');
    
    // Check key manifest properties
    expect(manifest.name).toBe('Student Productivity Hub');
    expect(manifest.short_name).toBe('ProductivityHub');
    expect(manifest.display).toBe('standalone');
    expect(manifest.start_url).toBe('/');
    
    // Check icons exist
    expect(manifest.icons.length).toBeGreaterThan(0);
    
    // Close manifest page
    await manifestPage.close();
  });

  test('Service worker registers successfully', async ({ page }) => {
    // Check service worker registration via console messages
    const consoleMessages = [];
    page.on('console', msg => consoleMessages.push(msg.text()));
    
    // Reload to trigger service worker registration
    await page.reload();
    
    // Wait a moment for registration to complete
    await page.waitForTimeout(1000);
    
    // Check for service worker registration message
    const swRegistrationMessage = consoleMessages.some(msg => 
      msg.includes('SW registered')
    );
    
    expect(swRegistrationMessage).toBeTruthy();
  });

  test('Responsive design adapts to mobile viewport', async ({ page }) => {
    // Check desktop navigation
    await expect(page.locator('.header__nav')).toBeVisible();
    
    // Resize to mobile dimensions
    await page.setViewportSize({ width: 375, height: 812 });
    
    // Wait for responsive layout to adjust
    await page.waitForTimeout(500);
    
    // Check mobile navigation (hamburger menu should be visible)
    await expect(page.locator('.header__menu-toggle')).toBeVisible();
    await expect(page.locator('.header__nav')).toBeHidden();
  });

  test('PWA installation prompt appears', async ({ page }) => {
    // Check if installation prompt is visible
    await expect(page.locator('#install-prompt')).toBeVisible();
    
    // Check install buttons
    await expect(page.locator('#install-confirm')).toHaveText('Install');
    await expect(page.locator('#install-dismiss')).toHaveText('Not now');
  });
});
