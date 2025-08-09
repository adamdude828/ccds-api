import { test as setup, expect } from '@playwright/test';
import path from 'path';

// Path to store authentication state
const authFile = path.join(__dirname, '.auth', 'user.json');

setup('authenticate', async ({ page }) => {
  console.log('🔐 Starting authentication...');
  
  // Navigate to the app
  await page.goto('http://localhost:3000/');
  
  // Click sign in with Microsoft
  await page.locator('button:has-text("Sign in with Microsoft")').click();
  
  // Get credentials from environment variables
  const email = process.env.E2E_USER || process.env.AZURE_TEST_USER;
  const password = process.env.E2E_PASSWORD || process.env.AZURE_TEST_PASSWORD;
  
  if (!email || !password) {
    throw new Error('E2E_USER and E2E_PASSWORD environment variables are required');
  }

  // Fill email
  await page.locator('input[type="email"]').fill(email);
  await page.locator('input[type="submit"]').click();
  
  // Fill password
  await page.locator('input[type="password"]').fill(password);
  await page.locator('input[type="submit"][value="Sign in"]').click();
  
  // Handle "Stay signed in?" if it appears
  try {
    const yesButton = page.locator('input[value="Yes"]');
    await yesButton.click({ timeout: 5000 });
  } catch {
    // If no "Stay signed in?" prompt, continue
  }
  
  // Wait for dashboard
  await page.waitForURL('**/dashboard', { timeout: 30000 });
  
  // Wait for network to settle
  await page.waitForLoadState('networkidle');
  
  // Save storage state (includes cookies, localStorage, sessionStorage)
  await page.context().storageState({ path: authFile });
  
  console.log('✅ Authentication successful');
  console.log('📁 Auth state saved to:', authFile);
}); 