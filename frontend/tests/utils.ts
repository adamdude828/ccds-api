import { Page, APIRequestContext } from '@playwright/test';
import { makeAuthenticatedRequest } from '@/lib/auth';

export async function setupTestData() {
  // Clear existing data and set up test data
  const clearResponse = await makeAuthenticatedRequest('/api/campuses', {
    method: 'DELETE'
  });
  if (!clearResponse.ok) {
    console.warn('Failed to clear test data');
  }

  // Create test campus
  const createResponse = await makeAuthenticatedRequest('/api/campuses', {
    method: 'POST',
    body: JSON.stringify({
      name: 'Test Campus',
      address: '123 Test St',
      city: 'Test City',
      state: 'TS',
      zip: '12345'
    })
  });
  if (!createResponse.ok) {
    throw new Error('Failed to create test data');
  }
}

export async function getTestToken(request: APIRequestContext): Promise<string> {
  const response = await request.get(`${process.env.API_URL}/api/test-auth-token`);
  const data = await response.json();
  return data.access_token;
}

export async function setupAuth(page: Page, token: string): Promise<void> {
  // Set the auth token in localStorage
  await page.goto('/');
  await page.evaluate((token) => {
    localStorage.setItem('testAuthToken', token);
    // Also set a flag to use test auth
    localStorage.setItem('useTestAuth', 'true');
  }, token);
}

export async function setupAuthenticatedPage(page: Page, request: APIRequestContext): Promise<void> {
  const token = await getTestToken(request);
  await setupAuth(page, token);
  await setupTestData();
} 