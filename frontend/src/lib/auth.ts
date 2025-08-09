import { getMsalInstance, getMsalInitPromise } from '@/app/providers/AuthProvider';
import { NetworkError, PermissionError, AuthenticationError } from '@/types/errors';

export async function getAuthToken(): Promise<string> {
  if (typeof window === 'undefined') {
    throw new Error('MSAL can only be initialized on the client side');
  }
  
  const msalInstance = getMsalInstance();
  const msalInitPromise = getMsalInitPromise();
  
  if (!msalInstance || !msalInitPromise) {
    throw new Error('MSAL instance not available');
  }
  
  // Wait for MSAL to be initialized
  await msalInitPromise;
  const accounts = msalInstance.getAllAccounts();
  
  if (!accounts || accounts.length === 0) {
    throw new Error('No active account! Verify the user is signed in.');
  }

  const tokenRequest = {
    account: accounts[0],
    scopes: ['User.Read'],
  };

  try {
    const response = await msalInstance.acquireTokenSilent(tokenRequest);
    return response.accessToken;
  } catch (error) {
    console.error('Failed to get token:', error);
    if (error instanceof Error) {
      throw error;
    }
    throw new Error('Failed to get token');
  }
}

export async function makeAuthenticatedRequest(path: string, options: RequestInit = {}): Promise<Response> {
  const token = await getAuthToken();
  const baseUrl = process.env.NEXT_PUBLIC_API_URL;
  
  const headers = {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    ...options.headers,
  };

  try {
    const response = await fetch(`${baseUrl}${path}`, {
      ...options,
      headers,
    });

    // Check for specific error statuses
    if (response.status === 401) {
      throw new AuthenticationError('Authentication failed - please sign in again');
    }
    
    if (response.status === 403) {
      throw new PermissionError('You do not have permission to access this resource');
    }

    // For other non-ok statuses, throw a generic error
    if (!response.ok && response.status >= 500) {
      throw new NetworkError(`Server error: ${response.status}`);
    }

    return response;
  } catch (error) {
    // If it's already one of our custom errors, re-throw it
    if (error instanceof AuthenticationError || 
        error instanceof PermissionError || 
        error instanceof NetworkError) {
      throw error;
    }

    // Handle network/connection errors
    if (error instanceof TypeError && error.message.includes('fetch')) {
      throw new NetworkError('Unable to connect to the server. Please check your connection.');
    }

    // Re-throw any other errors
    throw error;
  }
} 