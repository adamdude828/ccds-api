import React from 'react';
import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';
import Home from './page';

// Mock Next.js navigation
jest.mock('next/navigation', () => ({
  useRouter: () => ({
    push: jest.fn(),
    replace: jest.fn(),
    prefetch: jest.fn(),
    back: jest.fn(),
    forward: jest.fn(),
    refresh: jest.fn(),
    pathname: '/',
    query: {}
  }),
  useSearchParams: () => new URLSearchParams(),
  usePathname: () => '/'
}));

// Mock MSAL
jest.mock('@azure/msal-react', () => ({
  useMsal: () => ({
    instance: {
      loginRedirect: jest.fn()
    },
    accounts: [],
    inProgress: 'none'
  })
}));

// Mock the LoginForm component to avoid router issues
jest.mock('./components/LoginForm', () => ({
  __esModule: true,
  default: () => <div data-testid="login-form">Login Form</div>
}));

describe('Home', () => {
  it('should render the main page', () => {
    render(<Home />);
    
    // Check if the page title exists
    const title = screen.getByText('Challenger Reviews');
    expect(title).toBeInTheDocument();
  });

  it('should have proper layout classes', () => {
    const { container } = render(<Home />);
    
    const mainDiv = container.querySelector('div.min-h-screen');
    expect(mainDiv).toHaveClass('min-h-screen', 'flex', 'items-center', 'justify-center', 'bg-gray-50');
  });

  it('should render the login form container', () => {
    const { container } = render(<Home />);
    
    const formContainer = container.querySelector('div.w-full.max-w-md');
    expect(formContainer).toHaveClass('w-full', 'max-w-md', 'p-8', 'bg-white', 'rounded-lg', 'shadow-md');
  });

  it('should render the title with correct styling', () => {
    render(<Home />);
    
    const title = screen.getByRole('heading', { level: 1 });
    expect(title).toHaveClass('text-2xl', 'font-bold', 'text-center', 'mb-8');
    expect(title).toHaveTextContent('Challenger Reviews');
  });

  it('should render the LoginForm component', () => {
    render(<Home />);
    
    const loginForm = screen.getByTestId('login-form');
    expect(loginForm).toBeInTheDocument();
  });
});