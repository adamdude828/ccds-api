import { Page } from '@playwright/test';
import fs from 'fs';
import path from 'path';

export interface CustomStorageState {
  cookies: any[];
  origins: any[];
  sessionStorage: Record<string, Record<string, string>>;
}

/**
 * Save storage state including sessionStorage data manually
 */
export async function saveStorageState(page: Page, filePath: string): Promise<void> {
  // Get standard storage state
  const storageState = await page.context().storageState();
  
  // Get sessionStorage data from the current origin
  const origin = page.url();
  const sessionStorageData = await page.evaluate(() => {
    const data: Record<string, string> = {};
    for (let i = 0; i < sessionStorage.length; i++) {
      const key = sessionStorage.key(i);
      if (key) {
        data[key] = sessionStorage.getItem(key) || '';
      }
    }
    return data;
  });
  
  // Create custom storage state with sessionStorage
  const customState: CustomStorageState = {
    ...storageState,
    sessionStorage: {
      [new URL(origin).origin]: sessionStorageData
    }
  };
  
  // Save to file
  fs.writeFileSync(filePath, JSON.stringify(customState, null, 2));
}

/**
 * Load storage state including sessionStorage data
 */
export async function loadStorageState(page: Page, filePath: string): Promise<void> {
  if (!fs.existsSync(filePath)) {
    throw new Error(`Storage state file not found: ${filePath}`);
  }
  
  const customState: CustomStorageState = JSON.parse(fs.readFileSync(filePath, 'utf-8'));
  
  // First, load the standard storage state (cookies)
  await page.context().addCookies(customState.cookies);
  
  // Then, restore sessionStorage for each origin
  for (const [origin, data] of Object.entries(customState.sessionStorage || {})) {
    await page.goto(origin);
    await page.evaluate((storageData) => {
      sessionStorage.clear();
      for (const [key, value] of Object.entries(storageData)) {
        sessionStorage.setItem(key, value);
      }
    }, data);
  }
}