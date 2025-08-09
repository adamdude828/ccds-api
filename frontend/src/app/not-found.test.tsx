import React from 'react';
import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';
import NotFound from './not-found';

// Mock Next.js Link component
jest.mock('next/link', () => ({
  __esModule: true,
  default: ({ children, href }: any) => <a href={href}>{children}</a>
}));

describe('NotFound', () => {
  it('should render 404 heading', () => {
    render(<NotFound />);
    
    expect(screen.getByRole('heading', { name: /404/i })).toBeInTheDocument();
  });

  it('should display page not found message', () => {
    render(<NotFound />);
    
    expect(screen.getByText(/page not found/i)).toBeInTheDocument();
  });

  it('should have a link to return to dashboard', () => {
    render(<NotFound />);
    
    const dashboardLink = screen.getByRole('link', { name: /return to dashboard/i });
    expect(dashboardLink).toBeInTheDocument();
    expect(dashboardLink).toHaveAttribute('href', '/dashboard');
  });

  it('should have proper styling', () => {
    const { container } = render(<NotFound />);
    
    const mainDiv = container.firstChild as HTMLElement;
    expect(mainDiv).toHaveClass('min-h-screen', 'flex', 'items-center', 'justify-center', 'bg-gray-100');
  });
});