import { getAuthToken, makeAuthenticatedRequest } from './auth';

// Mock MSAL instance
const mockMsalInstance = {
  getAllAccounts: jest.fn(),
  acquireTokenSilent: jest.fn(),
};

jest.mock('@/app/providers/AuthProvider', () => ({
  getMsalInstance: jest.fn(() => mockMsalInstance),
  getMsalInitPromise: jest.fn(() => Promise.resolve()),
}));

describe('auth', () => {
  let consoleErrorSpy: jest.SpyInstance;

  beforeEach(() => {
    // Clear all mocks before each test
    jest.clearAllMocks();
    // Reset environment variables
    process.env.NEXT_PUBLIC_API_URL = 'http://test-api.com';
    // Spy on console.error
    consoleErrorSpy = jest.spyOn(console, 'error').mockImplementation(() => {});
  });

  afterEach(() => {
    // Restore console.error
    consoleErrorSpy.mockRestore();
  });

  describe('getAuthToken', () => {
    it('should get token from MSAL when account exists', async () => {
      const mockAccount = { username: 'test@example.com' };
      const mockToken = 'test-token';

      // Setup mocks
      (mockMsalInstance.getAllAccounts as jest.Mock).mockReturnValue([mockAccount]);
      (mockMsalInstance.acquireTokenSilent as jest.Mock).mockResolvedValue({ accessToken: mockToken });

      const token = await getAuthToken();

      expect(token).toBe(mockToken);
      expect(mockMsalInstance.getAllAccounts).toHaveBeenCalled();
      expect(mockMsalInstance.acquireTokenSilent).toHaveBeenCalledWith({
        account: mockAccount,
        scopes: ['User.Read'],
      });
    });

    it('should throw error when no accounts exist', async () => {
      (mockMsalInstance.getAllAccounts as jest.Mock).mockReturnValue([]);

      await expect(getAuthToken()).rejects.toThrow('No active account');
    });

    it('should throw error when token acquisition fails', async () => {
      const mockAccount = { username: 'test@example.com' };
      
      (mockMsalInstance.getAllAccounts as jest.Mock).mockReturnValue([mockAccount]);
      (mockMsalInstance.acquireTokenSilent as jest.Mock).mockRejectedValue(new Error('Token acquisition failed'));

      await expect(getAuthToken()).rejects.toThrow('Token acquisition failed');
    });
  });

  describe('makeAuthenticatedRequest', () => {
    beforeEach(() => {
      // Mock fetch
      global.fetch = jest.fn();
    });

    it('should make authenticated request with correct headers and URL', async () => {
      const mockToken = 'test-token';
      const mockResponse = { ok: true, json: () => Promise.resolve({ data: 'test' }) };
      
      // Setup mocks
      (mockMsalInstance.getAllAccounts as jest.Mock).mockReturnValue([{ username: 'test@example.com' }]);
      (mockMsalInstance.acquireTokenSilent as jest.Mock).mockResolvedValue({ accessToken: mockToken });
      (global.fetch as jest.Mock).mockResolvedValue(mockResponse);

      await makeAuthenticatedRequest('/test-path', {
        method: 'POST',
        body: JSON.stringify({ test: true }),
      });

      expect(global.fetch).toHaveBeenCalledWith(
        'http://test-api.com/test-path',
        {
          method: 'POST',
          body: JSON.stringify({ test: true }),
          headers: {
            'Authorization': `Bearer ${mockToken}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
        }
      );
    });

    it('should handle request options correctly', async () => {
      const mockToken = 'test-token';
      const mockResponse = { ok: true, json: () => Promise.resolve({ data: 'test' }) };
      
      // Setup mocks
      (mockMsalInstance.getAllAccounts as jest.Mock).mockReturnValue([{ username: 'test@example.com' }]);
      (mockMsalInstance.acquireTokenSilent as jest.Mock).mockResolvedValue({ accessToken: mockToken });
      (global.fetch as jest.Mock).mockResolvedValue(mockResponse);

      await makeAuthenticatedRequest('/test-path', {
        method: 'GET',
        headers: {
          'Custom-Header': 'test',
        },
      });

      expect(global.fetch).toHaveBeenCalledWith(
        'http://test-api.com/test-path',
        {
          method: 'GET',
          headers: {
            'Authorization': `Bearer ${mockToken}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Custom-Header': 'test',
          },
        }
      );
    });
  });
}); 