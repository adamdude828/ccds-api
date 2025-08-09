import { defineConfig, devices } from '@playwright/test';
import path from 'path';
import dotenv from 'dotenv';

// Load environment variables from .env file in the tests directory
dotenv.config({ path: path.join(__dirname, '.env') });

// Also load from parent directory .env if needed
dotenv.config({ path: path.join(__dirname, '../.env') });

export default defineConfig({
  testDir: './',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: 'html',
  outputDir: 'test-results',
  use: {
    baseURL: 'http://localhost:3000',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
  },
  projects: [
    // Setup project - runs authentication once and saves state
    {
      name: 'setup',
      testMatch: /.*\.setup\.ts/,
    },
    
    // Test projects that depend on setup
    {
      name: 'chromium',
      testDir: './spec',
      use: { 
        ...devices['Desktop Chrome'],
        // Use stored authentication state
        storageState: path.join(__dirname, '.auth', 'user.json'),
      },
      dependencies: ['setup'],
    },
  ],
}); 