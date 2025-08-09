import { Page, Locator, expect } from '@playwright/test';

export class LoginPage {
  readonly page: Page;
  readonly loginButton: Locator;
  readonly emailInput: Locator;
  readonly passwordInput: Locator;
  readonly submitButton: Locator;

  constructor(page: Page) {
    this.page = page;
    this.loginButton = page.locator('button:has-text("Sign in with Microsoft")');
    this.emailInput = page.locator('input[type="email"]');
    this.passwordInput = page.locator('input[type="password"]');
    this.submitButton = page.locator('input[type="submit"]');
  }

  async goto() {
    await this.page.goto('http://localhost:3000/');
  }

  async initiateLogin() {
    await this.loginButton.click();
    // Wait for navigation to Microsoft login page
    await this.page.waitForURL(/login\.microsoftonline\.com/);
  }

  async enterEmail(email: string) {
    await this.emailInput.fill(email);
    await this.submitButton.click();
    // Wait for the password field to be visible
    await this.passwordInput.waitFor({ state: 'visible' });
  }

  async enterPassword(password: string) {
    await this.passwordInput.fill(password);
    await this.submitButton.click();
  }

  async login(email: string, password: string) {
    await this.initiateLogin();
    await this.enterEmail(email);
    await this.enterPassword(password);
    
    // Wait for redirect after successful login
    await this.page.waitForURL(/dashboard/);
  }

  async loginWithEnvCredentials() {
    const email = process.env.AZURE_TEST_USER;
    const password = process.env.AZURE_TEST_PASSWORD;
    
    if (!email || !password) {
      throw new Error('AZURE_TEST_USER and AZURE_TEST_PASSWORD environment variables must be set');
    }
    
    await this.login(email, password);
  }
} 