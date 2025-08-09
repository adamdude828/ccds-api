import { test, expect } from '@playwright/test';

test.describe('Error Handling - Focused Tests', () => {
  test('application-down page displays correctly and has retry functionality', async ({ page }) => {
    // Navigate directly to application-down page
    await page.goto('/application-down');
    
    // Verify page elements
    await expect(page.locator('h1')).toContainText('Application Temporarily Unavailable');
    await expect(page.locator('text=experiencing technical difficulties')).toBeVisible();
    await expect(page.locator('text=automatically check for availability every 30 seconds')).toBeVisible();
    
    // Verify retry button exists and is functional
    const retryButton = page.locator('button:has-text("Retry Now")');
    await expect(retryButton).toBeVisible();
    await expect(retryButton).toBeEnabled();
    
    // Mock health check for retry test
    let healthCheckCalled = false;
    await page.route('**/api/health', route => {
      healthCheckCalled = true;
      route.fulfill({
        status: 503,
        contentType: 'application/json',
        body: JSON.stringify({ status: 'unhealthy' })
      });
    });
    
    // Click retry button
    await retryButton.click();
    
    // Verify health check was called
    await page.waitForTimeout(1000);
    expect(healthCheckCalled).toBe(true);
  });
});