import React from 'react';
import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';
import MsalRedirect from './MsalRedirect';

// Mock the custom hook
jest.mock('../hooks/useMsalRedirect', () => ({
  useMsalRedirect: jest.fn()
}));

describe('MsalRedirect', () => {
  let mockUseMsalRedirect: jest.Mock;
  const originalError = console.error;

  beforeEach(() => {
    mockUseMsalRedirect = require('../hooks/useMsalRedirect').useMsalRedirect;
    console.error = jest.fn();
  });

  afterEach(() => {
    jest.clearAllMocks();
    console.error = originalError;
  });

  it('should render null without error', () => {
    mockUseMsalRedirect.mockReturnValue({ error: null, initialized: true });

    const { container } = render(<MsalRedirect />);
    
    expect(container.firstChild).toBeNull();
    expect(console.error).not.toHaveBeenCalled();
  });

  it('should log error when hook returns error', () => {
    const testError = 'Test authentication error';
    mockUseMsalRedirect.mockReturnValue({ error: testError, initialized: true });

    render(<MsalRedirect />);
    
    expect(console.error).toHaveBeenCalledWith('MSAL Redirect Error:', testError);
  });

  it('should handle authentication error', () => {
    const authError = 'Authentication failed';
    mockUseMsalRedirect.mockReturnValue({ error: authError, initialized: false });

    const { container } = render(<MsalRedirect />);
    
    expect(container.firstChild).toBeNull();
    expect(console.error).toHaveBeenCalledWith('MSAL Redirect Error:', authError);
  });

  it('should render null when initialization is pending', () => {
    mockUseMsalRedirect.mockReturnValue({ error: null, initialized: false });

    const { container } = render(<MsalRedirect />);
    
    expect(container.firstChild).toBeNull();
    expect(console.error).not.toHaveBeenCalled();
  });
});