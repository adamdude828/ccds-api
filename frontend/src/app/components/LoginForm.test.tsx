import React from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import '@testing-library/jest-dom';
import { useMsal } from '@azure/msal-react';
import { useRouter } from 'next/navigation';
import { InteractionStatus } from '@azure/msal-browser';
import LoginForm from './LoginForm';

// Mock dependencies
jest.mock('@azure/msal-react');
jest.mock('next/navigation');
jest.mock('@challenger-school/do-git-mis-components-storybook', () => ({
  Button: ({ label, onClick, disabled, className }: any) => (
    <button onClick={onClick} disabled={disabled} className={className}>
      {label}
    </button>
  )
}));

describe('LoginForm', () => {
  const mockUseMsal = useMsal as jest.MockedFunction<typeof useMsal>;
  const mockUseRouter = useRouter as jest.MockedFunction<typeof useRouter>;
  const mockInstance = {
    loginRedirect: jest.fn(),
    getConfiguration: jest.fn().mockReturnValue({
      auth: {
        clientId: 'test-client-id',
        authority: 'https://login.microsoftonline.com/test',
        redirectUri: 'http://localhost:3000'
      }
    })
  };
  const mockRouter = {
    prefetch: jest.fn(),
    push: jest.fn(),
    replace: jest.fn(),
    refresh: jest.fn(),
    back: jest.fn(),
    forward: jest.fn()
  };

  beforeEach(() => {
    jest.clearAllMocks();
    mockUseRouter.mockReturnValue(mockRouter as any);
    mockUseMsal.mockReturnValue({
      instance: mockInstance as any,
      inProgress: InteractionStatus.None,
      accounts: [],
      logger: {} as any
    });
  });

  it('should render login button with correct text', () => {
    render(<LoginForm />);
    
    const button = screen.getByRole('button', { name: /sign in with microsoft/i });
    expect(button).toBeInTheDocument();
    expect(button).not.toBeDisabled();
  });

  it('should disable button when interaction is in progress', () => {
    mockUseMsal.mockReturnValue({
      instance: mockInstance as any,
      inProgress: InteractionStatus.Login,
      accounts: [],
      logger: {} as any
    });

    render(<LoginForm />);
    
    const button = screen.getByRole('button');
    expect(button).toBeDisabled();
  });

  it('should show "Signing in..." when logging in', async () => {
    render(<LoginForm />);
    
    const button = screen.getByRole('button', { name: /sign in with microsoft/i });
    fireEvent.click(button);
    
    await waitFor(() => {
      expect(screen.getByRole('button', { name: /signing in\.\.\./i })).toBeInTheDocument();
    });
  });

  it('should call loginRedirect with correct scopes on button click', async () => {
    render(<LoginForm />);
    
    const button = screen.getByRole('button', { name: /sign in with microsoft/i });
    fireEvent.click(button);
    
    await waitFor(() => {
      expect(mockInstance.loginRedirect).toHaveBeenCalledWith({
        scopes: ["User.Read", "openid", "profile", "email"]
      });
    });
  });

  it('should prefetch dashboard route after login', async () => {
    render(<LoginForm />);
    
    const button = screen.getByRole('button', { name: /sign in with microsoft/i });
    fireEvent.click(button);
    
    await waitFor(() => {
      expect(mockRouter.prefetch).toHaveBeenCalledWith('/dashboard');
    });
  });

  it('should log MSAL configuration details', async () => {
    const consoleSpy = jest.spyOn(console, 'log').mockImplementation();
    
    render(<LoginForm />);
    
    const button = screen.getByRole('button', { name: /sign in with microsoft/i });
    fireEvent.click(button);
    
    await waitFor(() => {
      expect(consoleSpy).toHaveBeenCalledWith("MSAL config details:", {
        clientId: 'test-client-id',
        authority: 'https://login.microsoftonline.com/test',
        redirectUri: 'http://localhost:3000'
      });
    });
    
    consoleSpy.mockRestore();
  });

  it('should not attempt login when interaction is already in progress', async () => {
    mockUseMsal.mockReturnValue({
      instance: mockInstance as any,
      inProgress: InteractionStatus.Login,
      accounts: [],
      logger: {} as any
    });
    
    const consoleSpy = jest.spyOn(console, 'log').mockImplementation();
    
    render(<LoginForm />);
    
    const button = screen.getByRole('button');
    
    // Button should be disabled when interaction is in progress
    expect(button).toBeDisabled();
    
    // Try to click anyway (shouldn't trigger login)
    fireEvent.click(button);
    
    // Since button is disabled and interaction is in progress, loginRedirect should not be called
    expect(mockInstance.loginRedirect).not.toHaveBeenCalled();
    
    consoleSpy.mockRestore();
  });

  it('should handle login errors gracefully', async () => {
    const testError = new Error('Login failed');
    mockInstance.loginRedirect.mockRejectedValue(testError);
    
    const consoleSpy = jest.spyOn(console, 'error').mockImplementation();
    
    render(<LoginForm />);
    
    const button = screen.getByRole('button', { name: /sign in with microsoft/i });
    fireEvent.click(button);
    
    await waitFor(() => {
      expect(consoleSpy).toHaveBeenCalledWith("Login failed:", testError);
      expect(consoleSpy).toHaveBeenCalledWith("Error details:", {
        name: 'Error',
        message: 'Login failed',
        stack: expect.any(String)
      });
    });
    
    // Button should be re-enabled after error
    await waitFor(() => {
      const updatedButton = screen.getByRole('button', { name: /sign in with microsoft/i });
      expect(updatedButton).not.toBeDisabled();
    });
    
    consoleSpy.mockRestore();
  });

  it('should have correct styling classes on the container', () => {
    const { container } = render(<LoginForm />);
    
    const divElement = container.querySelector('.space-y-6');
    expect(divElement).toBeInTheDocument();
  });

  it('should pass correct props to Button component', () => {
    render(<LoginForm />);
    
    const button = screen.getByRole('button');
    expect(button).toHaveClass('w-full');
  });
});