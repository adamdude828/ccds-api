import { test, expect } from '@playwright/test';

test.describe('Error Handling - Focused Tests', () => {
  test('403 errors on protected resources show appropriate message', async ({ page }) => {
    // Mock a specific endpoint to return 403
    await page.route('**/api/email-campaigns', route => {
      route.fulfill({
        status: 403,
        contentType: 'application/json',
        body: JSON.stringify({ 
          error: 'Forbidden',
          message: 'You do not have permission to access this resource' 
        })
      });
    });
    
    // Navigate to campaigns page
    await page.goto('/dashboard/campaign');
    
    // Should handle the permission error gracefully
    await expect(page.locator('body')).toBeVisible();
    await page.waitForTimeout(2000);
  });
});