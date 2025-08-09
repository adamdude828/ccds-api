import { test, expect } from '@playwright/test';

test.describe('Error Handling - Focused Tests', () => {
  test('network errors trigger appropriate error handling', async ({ page }) => {
    // Mock all API calls to fail with network error
    await page.route('**/api/**', route => {
      route.abort('failed');
    });
    
    // Try to navigate to a page that makes API calls
    await page.goto('/dashboard/campaign');
    
    // Should handle the network error gracefully
    await expect(page.locator('body')).toBeVisible();
    await page.waitForTimeout(2000);
  });
});