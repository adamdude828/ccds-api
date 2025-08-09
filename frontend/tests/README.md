# E2E Tests

This directory contains end-to-end tests for the Marketing Reviews application.

## Structure

- `pageobject/`: Contains page object models representing UI components and pages
- `spec/`: Contains the actual test specs that use the page objects
- `test-results/`: Generated test results (gitignored)
- `playwright-report/`: Generated HTML reports (gitignored)

## Setup

```bash
# Install dependencies
npm install

# Install browsers
npx playwright install
```

## Running Tests

```bash
# Run all tests
npm test

# Run tests with UI
npm run test:ui

# Run tests in debug mode
npm run test:debug

# Run tests in headed browsers
npm run test:headed
```

## Authentication Setup

This project uses Microsoft OAuth for authentication. The E2E tests implement authentication state persistence to login once and reuse the session across tests.

### Configuration

1. Set the following environment variables in `.env` or `tests/.env`:
   ```bash
   E2E_USER=your-test-user@example.com
   E2E_PASSWORD="your-test-password"  # Use quotes if password contains special characters
   ```
   
2. The authentication flow:
   - `auth.setup.ts` runs before all tests and performs the login
   - Authentication state is saved to `tests/.auth/user.json`
   - All tests use this saved state and start already authenticated

### Running Tests

```bash
# Run all tests (includes authentication setup)
npm test

# Clear auth and run fresh
npm run test:fresh

# Run authentication setup only
npm run test:auth

# Clear saved authentication
npm run clear-auth
```

### Important Notes

- The `.auth/` directory is gitignored for security
- Authentication state includes cookies and sessionStorage
- Sessions may expire - run `npm run clear-auth` if tests fail with auth errors
- Use a dedicated test account, not your personal account

### Troubleshooting

If tests fail with authentication errors:
1. Check that E2E_USER and E2E_PASSWORD are set correctly
2. Run `npm run clear-auth` to clear saved state
3. Ensure the test account has proper permissions
4. Check if the session has expired

## Writing Tests

### Page Objects

Page objects should be created in the `pageobject/` directory and should represent a page or component in the application.

Example:
```typescript
// pageobject/LoginPage.ts
import { Page, Locator, expect } from '@playwright/test';

export class LoginPage {
  readonly page: Page;
  readonly emailInput: Locator;
  readonly passwordInput: Locator;
  readonly loginButton: Locator;
  
  constructor(page: Page) {
    this.page = page;
    this.emailInput = page.getByLabel('Email');
    this.passwordInput = page.getByLabel('Password');
    this.loginButton = page.getByRole('button', { name: 'Login' });
  }

  async login(email: string, password: string) {
    await this.emailInput.fill(email);
    await this.passwordInput.fill(password);
    await this.loginButton.click();
  }
}
```

### Test Specs

Test specs should be created in the `spec/` directory and should use page objects to interact with the application.

Example:
```typescript
// spec/login.spec.ts
import { test, expect } from '@playwright/test';
import { LoginPage } from '../pageobject/LoginPage';
import { HomePage } from '../pageobject/HomePage';

test.describe('Login', () => {
  test('should log in successfully', async ({ page }) => {
    const loginPage = new LoginPage(page);
    const homePage = new HomePage(page);
    
    await page.goto('/login');
    await loginPage.login('test@example.com', 'password123');
    
    // Verify redirect to home page
    await expect(page).toHaveURL('/');
    expect(await homePage.isLoaded()).toBeTruthy();
  });
});
``` 