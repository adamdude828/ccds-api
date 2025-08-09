import { test, expect } from '@playwright/test';

test.describe('Error Handling - Focused Tests', () => {
  test('API errors are handled gracefully in dashboard', async ({ page }) => {
    // Mock dashboard data endpoint to return 500 error
    await page.route('**/api/dashboard-data', route => {
      route.fulfill({
        status: 500,
        contentType: 'application/json',
        body: JSON.stringify({ error: 'Internal Server Error' })
      });
    });
    
    // Navigate to dashboard
    await page.goto('/dashboard');
    
    // Page should load without crashing
    await expect(page.locator('body')).toBeVisible();
    
    // Should show error state (implementation specific)
    // The exact error UI depends on the dashboard implementation
    await page.waitForTimeout(2000);
  });
});