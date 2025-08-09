import React from 'react';
import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';
import AccessDeniedPage from './page';

describe('AccessDeniedPage', () => {
  it('should render access denied message', () => {
    render(<AccessDeniedPage />);
    
    expect(screen.getByRole('heading', { name: /access denied/i })).toBeInTheDocument();
  });

  it('should display the correct message', () => {
    render(<AccessDeniedPage />);
    
    expect(screen.getByText(/you do not have permission/i)).toBeInTheDocument();
  });

  it('should have centered content', () => {
    const { container } = render(<AccessDeniedPage />);
    
    const mainDiv = container.firstChild as HTMLElement;
    expect(mainDiv).toHaveClass('flex', 'items-center', 'justify-center', 'min-h-screen');
  });

  it('should have proper text styling', () => {
    render(<AccessDeniedPage />);
    
    const heading = screen.getByRole('heading', { name: /access denied/i });
    expect(heading).toHaveClass('text-2xl', 'font-bold', 'text-red-600', 'mb-2');
    
    const message = screen.getByText(/you do not have permission/i);
    expect(message).toHaveClass('text-gray-600');
  });
});