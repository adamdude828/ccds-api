import { test, expect } from '@playwright/test';

test.describe('Error Handling - Focused Tests', () => {
  test('retry count increments on application-down page', async ({ page }) => {
    // Mock health check to always fail
    await page.route('**/api/health', route => {
      route.fulfill({
        status: 503,
        contentType: 'application/json',
        body: JSON.stringify({ status: 'unhealthy' })
      });
    });
    
    // Navigate to application-down page
    await page.goto('/application-down');
    
    // Initially no retry count should be visible
    await expect(page.locator('text=/Retry attempts: \\d+/')).not.toBeVisible();
    
    // Click retry button
    await page.locator('button:has-text("Retry Now")').click();
    await page.waitForTimeout(1000);
    
    // Now retry count should be visible
    await expect(page.locator('text=/Retry attempts: \\d+/')).toBeVisible();
  });
});