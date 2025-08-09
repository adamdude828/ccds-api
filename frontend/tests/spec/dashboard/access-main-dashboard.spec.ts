import { test, expect } from '@playwright/test';

test.describe('Dashboard Navigation', () => {
  test.beforeEach(async ({ page }) => {
    // Navigate to dashboard - we're already authenticated
    await page.goto('/dashboard');
  });

  test('should access main dashboard', async ({ page }) => {
    // We should be on dashboard
    await expect(page).toHaveURL(/dashboard/);
    
    // Verify we see dashboard content
    await expect(page.locator('text=Campaign Metrics')).toBeVisible();
    await expect(page.locator('text=Total Sent')).toBeVisible();
  });
});