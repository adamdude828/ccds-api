import React from 'react';
import { render } from '@testing-library/react';
import '@testing-library/jest-dom';
import RootLayout from './layout';

// Mock Next.js font imports
jest.mock('next/font/local', () => ({
  __esModule: true,
  default: () => ({
    variable: '--font-geist-sans'
  })
}));

// Mock the AuthProvider
jest.mock('./providers/AuthProvider', () => ({
  AuthProvider: ({ children }: { children: React.ReactNode }) => (
    <div data-testid="auth-provider">{children}</div>
  )
}));

describe('RootLayout', () => {
  it('should render children within the layout', () => {
    const { getByText } = render(
      <RootLayout>
        <div>Test Content</div>
      </RootLayout>
    );
    
    expect(getByText('Test Content')).toBeInTheDocument();
  });

  it('should wrap children in AuthProvider', () => {
    const { getByTestId } = render(
      <RootLayout>
        <div>Test Content</div>
      </RootLayout>
    );
    
    expect(getByTestId('auth-provider')).toBeInTheDocument();
  });

  it('should have correct html structure', () => {
    const { container } = render(
      <RootLayout>
        <div>Test Content</div>
      </RootLayout>
    );
    
    const html = container.querySelector('html');
    expect(html).toHaveAttribute('lang', 'en');
  });

  it('should apply font variables to body', () => {
    const { container } = render(
      <RootLayout>
        <div>Test Content</div>
      </RootLayout>
    );
    
    const body = container.querySelector('body');
    expect(body).toHaveClass('antialiased');
  });
});