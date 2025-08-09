import { test, expect } from '@playwright/test';

test.describe('Error Handling - Focused Tests', () => {
  test('access-denied page displays correctly', async ({ page }) => {
    // Navigate directly to access-denied page
    await page.goto('/access-denied');
    
    // Verify page elements
    await expect(page.locator('h1')).toContainText('Access Denied');
    await expect(page.locator('text=do not have permission')).toBeVisible();
    
    // Verify return to home button
    const returnButton = page.locator('a:has-text("Return to Home")');
    await expect(returnButton).toBeVisible();
    await expect(returnButton).toHaveAttribute('href', '/');
  });
});