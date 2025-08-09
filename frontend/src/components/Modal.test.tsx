import React from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import '@testing-library/jest-dom';
import { Modal } from './Modal';

// Mock createPortal
jest.mock('react-dom', () => ({
  ...jest.requireActual('react-dom'),
  createPortal: (element: any) => element,
}));

describe('Modal', () => {
  const defaultProps = {
    isOpen: true,
    onClose: jest.fn(),
    title: 'Test Modal',
    children: <div>Modal content</div>,
  };

  beforeEach(() => {
    jest.clearAllMocks();
  });

  it('should not render when isOpen is false', () => {
    render(<Modal {...defaultProps} isOpen={false} />);
    
    expect(screen.queryByText('Test Modal')).not.toBeInTheDocument();
    expect(screen.queryByText('Modal content')).not.toBeInTheDocument();
  });

  it('should render when isOpen is true', () => {
    render(<Modal {...defaultProps} />);
    
    expect(screen.getByText('Test Modal')).toBeInTheDocument();
    expect(screen.getByText('Modal content')).toBeInTheDocument();
  });

  it('should display the correct title', () => {
    const customTitle = 'Custom Title';
    render(<Modal {...defaultProps} title={customTitle} />);
    
    expect(screen.getByText(customTitle)).toBeInTheDocument();
  });

  it('should render children content', () => {
    const customContent = <div data-testid="custom-content">Custom modal content</div>;
    render(<Modal {...defaultProps}>{customContent}</Modal>);
    
    expect(screen.getByTestId('custom-content')).toBeInTheDocument();
    expect(screen.getByText('Custom modal content')).toBeInTheDocument();
  });

  it('should call onClose when close button is clicked', () => {
    const onCloseMock = jest.fn();
    render(<Modal {...defaultProps} onClose={onCloseMock} />);
    
    const closeButton = screen.getByRole('button', { name: /close/i });
    fireEvent.click(closeButton);
    
    expect(onCloseMock).toHaveBeenCalledTimes(1);
  });

  it('should have proper accessibility attributes', () => {
    render(<Modal {...defaultProps} />);
    
    const closeButton = screen.getByRole('button', { name: /close/i });
    expect(closeButton).toHaveAccessibleName('Close');
  });

  it('should have correct styling classes for overlay', () => {
    const { container } = render(<Modal {...defaultProps} />);
    
    const overlay = container.querySelector('.fixed.top-0.left-0.w-full.h-full');
    expect(overlay).toBeInTheDocument();
    expect(overlay).toHaveClass('bg-black', 'bg-opacity-50', 'flex', 'items-center', 'justify-center');
  });

  it('should have correct styling classes for modal content', () => {
    const { container } = render(<Modal {...defaultProps} />);
    
    const modalContent = container.querySelector('.bg-white.rounded-lg');
    expect(modalContent).toBeInTheDocument();
    expect(modalContent).toHaveClass('p-6', 'max-w-md', 'w-full', 'mx-4');
  });

  it('should render SVG icon in close button', () => {
    render(<Modal {...defaultProps} />);
    
    const closeButton = screen.getByRole('button', { name: /close/i });
    const svg = closeButton.querySelector('svg');
    
    expect(svg).toBeInTheDocument();
    expect(svg).toHaveAttribute('viewBox', '0 0 24 24');
  });

  it('should handle multiple modals', () => {
    const { rerender } = render(
      <>
        <Modal {...defaultProps} title="Modal 1">Content 1</Modal>
        <Modal {...defaultProps} title="Modal 2">Content 2</Modal>
      </>
    );
    
    expect(screen.getByText('Modal 1')).toBeInTheDocument();
    expect(screen.getByText('Modal 2')).toBeInTheDocument();
    expect(screen.getByText('Content 1')).toBeInTheDocument();
    expect(screen.getByText('Content 2')).toBeInTheDocument();
  });

  it('should update when props change', () => {
    const { rerender } = render(<Modal {...defaultProps} />);
    
    expect(screen.getByText('Test Modal')).toBeInTheDocument();
    
    rerender(<Modal {...defaultProps} title="Updated Title" />);
    
    expect(screen.queryByText('Test Modal')).not.toBeInTheDocument();
    expect(screen.getByText('Updated Title')).toBeInTheDocument();
  });

  it('should cleanup when unmounted', () => {
    const { unmount } = render(<Modal {...defaultProps} />);
    
    expect(screen.getByText('Test Modal')).toBeInTheDocument();
    
    unmount();
    
    expect(screen.queryByText('Test Modal')).not.toBeInTheDocument();
  });
});