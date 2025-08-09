import { chromium } from '@playwright/test';
import path from 'path';
import fs from 'fs';

const authFile = path.join(__dirname, '../.auth/user.json');

/**
 * Clear the stored authentication state
 */
export async function clearAuthState() {
  if (fs.existsSync(authFile)) {
    fs.unlinkSync(authFile);
    console.log('✅ Authentication state cleared');
  }
}

/**
 * Check if authentication state exists
 */
export function authStateExists(): boolean {
  return fs.existsSync(authFile);
}

/**
 * Validate the stored authentication state by checking if we can access protected routes
 */
export async function validateAuthState(): Promise<boolean> {
  if (!authStateExists()) {
    return false;
  }

  const browser = await chromium.launch();
  const context = await browser.newContext({
    storageState: authFile
  });
  const page = await context.newPage();
  
  try {
    await page.goto('http://localhost:3000/dashboard', { 
      waitUntil: 'networkidle',
      timeout: 10000 
    });
    
    // If we're still on the dashboard, auth is valid
    const isValid = page.url().includes('/dashboard');
    
    await browser.close();
    return isValid;
  } catch (error) {
    await browser.close();
    return false;
  }
}