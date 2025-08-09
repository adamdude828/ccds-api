import React from 'react';
import { render, screen, waitFor, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom';
import ApplicationDown from './page';

// Mock Next.js navigation
const mockPush = jest.fn();
jest.mock('next/navigation', () => ({
  useRouter: () => ({
    push: mockPush
  })
}));

// Mock Button component
jest.mock('@challenger-school/do-git-mis-components-storybook', () => ({
  Button: ({ label, onClick, disabled }: any) => (
    <button onClick={onClick} disabled={disabled}>
      {label}
    </button>
  )
}));

// Mock fetch
global.fetch = jest.fn();

describe('ApplicationDown', () => {
  const originalEnv = process.env;
  const originalConsoleError = console.error;

  beforeEach(() => {
    jest.clearAllMocks();
    jest.useFakeTimers();
    console.error = jest.fn();
    process.env = { ...originalEnv, NEXT_PUBLIC_API_URL: 'http://test-api.com' };
    (global.fetch as jest.Mock).mockClear();
  });

  afterEach(() => {
    jest.useRealTimers();
    console.error = originalConsoleError;
    process.env = originalEnv;
  });

  it('should render the application down message', () => {
    render(<ApplicationDown />);
    
    expect(screen.getByText('Application Temporarily Unavailable')).toBeInTheDocument();
    expect(screen.getByText(/We're experiencing technical difficulties/)).toBeInTheDocument();
  });

  it('should show retry button', () => {
    render(<ApplicationDown />);
    
    const retryButton = screen.getByRole('button', { name: 'Retry Now' });
    expect(retryButton).toBeInTheDocument();
    expect(retryButton).not.toBeDisabled();
  });

  it('should automatically retry health check', async () => {
    (global.fetch as jest.Mock).mockResolvedValue({ ok: false });
    
    render(<ApplicationDown />);
    
    // Fast-forward time to trigger first retry
    jest.advanceTimersByTime(30000);
    
    await waitFor(() => {
      expect(global.fetch).toHaveBeenCalledWith('http://test-api.com/api/health');
    });
  });

  it('should redirect to dashboard on successful health check', async () => {
    (global.fetch as jest.Mock).mockResolvedValue({ ok: true });
    
    render(<ApplicationDown />);
    
    // Fast-forward time to trigger retry
    jest.advanceTimersByTime(30000);
    
    await waitFor(() => {
      expect(mockPush).toHaveBeenCalledWith('/dashboard');
    });
  });

  it('should handle manual retry', async () => {
    (global.fetch as jest.Mock).mockResolvedValue({ ok: false });
    
    render(<ApplicationDown />);
    
    const retryButton = screen.getByRole('button', { name: 'Retry Now' });
    fireEvent.click(retryButton);
    
    expect(screen.getByRole('button', { name: 'Checking...' })).toBeDisabled();
    
    await waitFor(() => {
      expect(global.fetch).toHaveBeenCalledWith('http://test-api.com/api/health');
    });
    
    await waitFor(() => {
      expect(screen.getByRole('button', { name: 'Retry Now' })).not.toBeDisabled();
    });
  });

  it('should handle fetch errors', async () => {
    (global.fetch as jest.Mock).mockRejectedValue(new Error('Network error'));
    
    render(<ApplicationDown />);
    
    jest.advanceTimersByTime(30000);
    
    await waitFor(() => {
      expect(console.error).toHaveBeenCalledWith('Health check failed:', expect.any(Error));
    });
  });

  it('should show max retries message after multiple failures', async () => {
    (global.fetch as jest.Mock).mockResolvedValue({ ok: false });
    
    const { rerender } = render(<ApplicationDown />);
    
    // Fast forward through multiple retries
    for (let i = 0; i < 10; i++) {
      jest.advanceTimersByTime(300000);
      await waitFor(() => {
        expect(global.fetch).toHaveBeenCalled();
      });
      rerender(<ApplicationDown />);
    }
    
    // Should eventually show max retries message
    await waitFor(() => {
      expect(screen.getByText(/Automatic retries have stopped/)).toBeInTheDocument();
    });
  });

  it('should display retry count', async () => {
    (global.fetch as jest.Mock).mockResolvedValue({ ok: false });
    
    render(<ApplicationDown />);
    
    jest.advanceTimersByTime(30000);
    
    await waitFor(() => {
      expect(screen.getByText(/Retry attempts: 1 \/ 10/)).toBeInTheDocument();
    });
  });

  it('should calculate next retry delay with exponential backoff', async () => {
    (global.fetch as jest.Mock).mockResolvedValue({ ok: false });
    
    render(<ApplicationDown />);
    
    // Initially shows 30 seconds
    expect(screen.getByText(/Next check in: 30 seconds/)).toBeInTheDocument();
    
    // After first retry, should show 45 seconds (30 * 1.5)
    jest.advanceTimersByTime(30000);
    await waitFor(() => {
      expect(screen.getByText(/Next check in: 45 seconds/)).toBeInTheDocument();
    });
  });

  it('should handle missing API URL', async () => {
    process.env.NEXT_PUBLIC_API_URL = '';
    
    render(<ApplicationDown />);
    
    jest.advanceTimersByTime(30000);
    
    await waitFor(() => {
      expect(console.error).toHaveBeenCalledWith('API URL not configured');
      expect(global.fetch).not.toHaveBeenCalled();
    });
  });
});