# Playwright Microsoft AD Authentication Guide

This document outlines how to implement Playwright testing with Microsoft AD/Azure AD authentication for the Marketing Reviews application.

## Overview

Testing applications that use Microsoft AD authentication presents unique challenges. This guide provides a comprehensive approach for automating these tests both in local development and CI environments.

## Authentication Approaches

There are two main approaches to handling Microsoft AD authentication in Playwright tests:

1. **Authentication State Storage**
   - Perform login once and save the authenticated state
   - Reuse this state for all subsequent tests
   - Faster and more reliable than logging in for each test

2. **Token Injection**
   - Acquire tokens programmatically (via API)
   - Inject tokens directly into the browser storage
   - Bypasses the interactive login flow completely

## Implementation

### 1. Authentication Setup

Create a dedicated setup file that handles authentication and stores the state:

```typescript
// tests/auth.setup.ts
import { test as setup, expect } from '@playwright/test';
import { LoginPage } from './pageobject/LoginPage';
import * as fs from 'fs';
import * as path from 'path';

// Ensure auth directory exists
const authDir = path.join(__dirname, '.auth');
if (!fs.existsSync(authDir)) {
  fs.mkdirSync(authDir, { recursive: true });
}

const authFile = path.join(authDir, 'user.json');

setup('authenticate', async ({ page }) => {
  // Skip authentication if we already have a stored state that's recent
  if (fs.existsSync(authFile)) {
    const stats = fs.statSync(authFile);
    const fileAgeInHours = (Date.now() - stats.mtimeMs) / (1000 * 60 * 60);
    
    // Only reuse auth state if it's less than 1 hour old
    if (fileAgeInHours < 1) {
      console.log('Using existing authentication state.');
      return;
    }
    console.log('Authentication state is too old. Re-authenticating...');
  }

  const loginPage = new LoginPage(page);
  
  // Verify environment variables for test credentials
  const email = process.env.AZURE_TEST_USER;
  const password = process.env.AZURE_TEST_PASSWORD;
  
  if (!email || !password) {
    throw new Error('AZURE_TEST_USER and AZURE_TEST_PASSWORD environment variables must be set');
  }
  
  console.log(`Authenticating with test account: ${email}`);
  
  // Navigate to the app and perform login
  await loginPage.goto();
  await loginPage.login(email, password);
  
  // Verify we're logged in successfully by checking for dashboard URL
  await expect(page).toHaveURL(/dashboard/);
  
  // Save the authentication state to reuse in tests
  await page.context().storageState({ path: authFile });
  console.log(`Authentication state saved to ${authFile}`);
});
```

### 2. Playwright Configuration

Update the Playwright configuration to use the stored authentication state:

```typescript
// playwright.config.ts
import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests',
  // ...other config
  
  projects: [
    // Setup project (runs auth.setup.ts)
    {
      name: 'setup',
      testMatch: /.*\.setup\.ts/
    },
    
    // Project for authenticated tests
    {
      name: 'authenticated',
      use: { 
        ...devices['Desktop Chrome'],
        // Use prepared auth state
        storageState: './tests/.auth/user.json',
      },
      dependencies: ['setup']
    },
    
    // You can add other projects for non-authenticated tests
  ],
  
  // ...other config
});
```

### 3. Security and .gitignore

Add the auth state directory to .gitignore to prevent credentials from being committed:

```
# Add to .gitignore
tests/.auth/
```

## CI Environment Setup

### GitHub Actions Configuration

```yaml
name: Playwright Tests
on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: actions/setup-node@v3
        with:
          node-version: 18
      
      - name: Install dependencies
        run: npm ci
      
      - name: Install Playwright browsers
        run: npx playwright install --with-deps
      
      - name: Run Playwright tests
        run: npx playwright test
        env:
          AZURE_TEST_USER: ${{ secrets.AZURE_TEST_USER }}
          AZURE_TEST_PASSWORD: ${{ secrets.AZURE_TEST_PASSWORD }}
      
      - name: Upload test results
        if: always()
        uses: actions/upload-artifact@v3
        with:
          name: playwright-report
          path: playwright-report/
          retention-days: 7
```

## Microsoft AD Account Setup

### Test Account Requirements

1. Create a dedicated test account in Microsoft AD/Azure AD
2. Disable MFA for this account
   - This can be done by an administrator through conditional access policies
   - Exclude the test account from MFA policies
3. Assign only the permissions needed for testing
4. Use a strong password and rotate it regularly
5. Consider restricting account usage to specific IP ranges

### Environment Variables

Local development:
```bash
# Add to your .env file (do not commit)
AZURE_TEST_USER=testuser@example.com
AZURE_TEST_PASSWORD=your-secure-password
```

CI environment:
- Store these as secrets in your CI system

## Testing Token Injection (Alternative Approach)

If the authentication state approach doesn't work for your scenario, you can use token injection:

```typescript
// Example token injection utility
export const injectTokens = async (page) => {
  // Get tokens programmatically
  const tokenResponse = await getTokensFromApi();
  
  // Inject tokens into browser storage
  await page.goto('/');
  await page.evaluate((tokens) => {
    window.localStorage.setItem('msal.token.keys', JSON.stringify(tokens.keyData));
    window.localStorage.setItem('msal.access.token', JSON.stringify(tokens.accessToken));
    // ... set other required tokens
  }, tokenResponse);
};
```

## Best Practices

1. **Dedicated Test Account**
   - Never use production accounts for testing
   - Create accounts specifically for automated testing

2. **MFA Considerations**
   - Disable MFA for test accounts only
   - Consider a separate test tenant if possible

3. **Token Management**
   - Be aware of token expiration (typically 1 hour)
   - Implement refresh mechanisms for long test runs

4. **Error Handling**
   - Add robust error handling for authentication failures
   - Consider conditional handling for first-time consent screens

5. **Security**
   - Never commit credentials or tokens
   - Rotate passwords regularly
   - Monitor test account usage

## Troubleshooting

- **Authentication Failures**
  - Check credential environment variables
  - Ensure test account has proper permissions
  - Verify account isn't locked out or expired

- **Token Expiration**
  - Add logic to check token age and refresh when needed
  - For longer test runs, implement token refresh mechanisms

- **Consent Screens**
  - First-time app access may show consent screens
  - Add handling for these in your auth setup

- **CI-Specific Issues**
  - Headless browsers may behave differently
  - Add more verbose logging for CI environments
  - Consider longer timeouts for authentication steps

## Resources

- [Playwright Authentication Documentation](https://playwright.dev/docs/auth)
- [Microsoft Authentication Library (MSAL) Documentation](https://learn.microsoft.com/en-us/azure/active-directory/develop/msal-overview)
- [Azure AD Conditional Access](https://learn.microsoft.com/en-us/azure/active-directory/conditional-access/overview)