import React from 'react';
import { render, screen, waitFor } from '@testing-library/react';
import '@testing-library/jest-dom';
import { AuthProvider } from './AuthProvider';

// Mock MSAL browser
jest.mock('@azure/msal-browser', () => ({
  PublicClientApplication: jest.fn().mockImplementation(() => ({
    initialize: jest.fn().mockResolvedValue(undefined),
    getAllAccounts: jest.fn(() => [])
  })),
  BrowserAuthError: class BrowserAuthError extends Error {
    constructor(code: string, message: string) {
      super(message);
      this.name = 'BrowserAuthError';
    }
  }
}));

// Mock next/navigation
jest.mock('next/navigation', () => ({
  useRouter: () => ({
    push: jest.fn(),
    replace: jest.fn(),
    prefetch: jest.fn()
  })
}));

// Mock MsalRedirect component
jest.mock('../components/MsalRedirect', () => ({
  __esModule: true,
  default: () => <div data-testid="msal-redirect" />
}));

// Mock MsalProvider to capture the instance
jest.mock('@azure/msal-react', () => ({
  MsalProvider: ({ children }: { children: React.ReactNode }) => (
    <div data-testid="msal-provider">{children}</div>
  )
}));

describe('AuthProvider', () => {
  const originalLog = console.log;
  const originalError = console.error;

  beforeEach(() => {
    console.log = jest.fn();
    console.error = jest.fn();
    jest.clearAllMocks();
  });

  afterEach(() => {
    console.log = originalLog;
    console.error = originalError;
  });

  it.skip('should render children and MsalRedirect', async () => {
    render(
      <AuthProvider>
        <div>Test Content</div>
      </AuthProvider>
    );

    expect(screen.getByTestId('msal-provider')).toBeInTheDocument();
    expect(screen.getByTestId('msal-redirect')).toBeInTheDocument();
    expect(screen.getByText('Test Content')).toBeInTheDocument();
  });

  it.skip('should render MsalProvider wrapper', () => {
    const { getByTestId } = render(
      <AuthProvider>
        <div>Test Content</div>
      </AuthProvider>
    );

    expect(getByTestId('msal-provider')).toBeInTheDocument();
  });

  it.skip('should not display error when initialization succeeds', async () => {
    render(
      <AuthProvider>
        <div>Test Content</div>
      </AuthProvider>
    );

    // No error should be displayed
    await waitFor(() => {
      expect(screen.queryByText(/Authentication Error:/)).not.toBeInTheDocument();
    });
  });

  it.skip('should render children content', () => {
    render(
      <AuthProvider>
        <div>Test Child Component</div>
      </AuthProvider>
    );

    expect(screen.getByText('Test Child Component')).toBeInTheDocument();
  });

  it.skip('should include MsalRedirect component', () => {
    render(
      <AuthProvider>
        <div>Test Content</div>
      </AuthProvider>
    );

    expect(screen.getByTestId('msal-redirect')).toBeInTheDocument();
  });
});