import { AccountInfo } from '@azure/msal-browser';

const TEST_API_URL = process.env.NEXT_PUBLIC_API_URL;

export class TestAuthProvider {
  private token: string | null = null;
  private account: AccountInfo = {
    homeAccountId: 'test-account',
    localAccountId: 'test-account',
    environment: 'test',
    tenantId: process.env.NEXT_PUBLIC_AZURE_TENANT_ID || '',
    username: 'test@example.com',
  };

  async getAccessToken(): Promise<string> {
    if (this.token) {
      return this.token;
    }

    try {
      const response = await fetch(`${TEST_API_URL}/api/test-auth-token`);
      if (!response.ok) {
        throw new Error('Failed to get test token');
      }

      const data = await response.json();
      if (!data.access_token) {
        throw new Error('No access token in response');
      }
      this.token = data.access_token;
      return data.access_token;
    } catch (error) {
      console.error('Error getting test token:', error);
      throw error;
    }
  }

  async login(): Promise<void> {
    await this.getAccessToken();
  }

  logout(): void {
    this.token = null;
  }

  getAllAccounts(): AccountInfo[] {
    return [this.account];
  }

  getActiveAccount(): AccountInfo | null {
    return this.account;
  }

  setActiveAccount(): void {
    // No-op in test mode
  }
} 