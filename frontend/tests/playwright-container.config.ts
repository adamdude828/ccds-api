import { defineConfig } from '@playwright/test';
import path from 'path';
import dotenv from 'dotenv';

// Load environment variables from .env file in the tests directory
dotenv.config({ path: path.join(__dirname, '.env') });

// Also load from parent directory .env if needed
dotenv.config({ path: path.join(__dirname, '../.env') });

export default defineConfig({
  testDir: './',
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: 0,
  workers: 1,
  reporter: 'list',
  outputDir: 'test-results',
  use: {
    baseURL: 'http://localhost:3000',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    // Use system Chrome
    channel: 'chrome',
    headless: true,
    launchOptions: {
      executablePath: '/usr/bin/google-chrome',
    },
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
        // Use stored authentication state
        storageState: path.join(__dirname, '.auth', 'user.json'),
      },
      dependencies: ['setup'],
    },
  ],
});