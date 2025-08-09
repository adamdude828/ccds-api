'use client';

import { Button } from '@challenger-school/do-git-mis-components-storybook';
import { useState, useEffect, useCallback } from 'react';
import { useRouter } from 'next/navigation';

const MAX_RETRIES = 10;
const BASE_DELAY = 30000; // 30 seconds

export default function ApplicationDown() {
  const router = useRouter();
  const [isRetrying, setIsRetrying] = useState(false);
  const [retryCount, setRetryCount] = useState(0);
  const [maxRetriesReached, setMaxRetriesReached] = useState(false);

  const checkHealth = useCallback(async (isManual = false) => {
    setIsRetrying(true);
    try {
      // Ensure we only access environment variable on the client side
      const apiUrl = typeof window !== 'undefined' ? process.env.NEXT_PUBLIC_API_URL : '';
      if (!apiUrl) {
        console.error('API URL not configured');
        return;
      }
      
      const response = await fetch(`${apiUrl}/api/health`);
      if (response.ok) {
        // If health check passes, redirect to dashboard
        router.push('/dashboard');
        return;
      }
    } catch (error) {
      // Health check failed, stay on this page
      console.error('Health check failed:', error);
    } finally {
      setIsRetrying(false);
      if (!isManual) {
        setRetryCount(prev => prev + 1);
      }
    }
  }, [router]);

  // Auto-retry with exponential backoff
  useEffect(() => {
    if (retryCount >= MAX_RETRIES) {
      setMaxRetriesReached(true);
      return;
    }

    // Calculate exponential backoff delay: 30s, 45s, 67s, 101s, 151s, etc.
    const delay = Math.min(BASE_DELAY * Math.pow(1.5, retryCount), 300000); // Cap at 5 minutes
    
    const timeout = setTimeout(() => {
      checkHealth();
    }, delay);

    return () => clearTimeout(timeout);
  }, [checkHealth, retryCount]);

  const handleManualRetry = () => {
    // Allow manual retry even after max auto-retries reached
    checkHealth(true);
  };

  // Calculate next retry delay for display
  const getNextRetryDelay = () => {
    if (maxRetriesReached) return null;
    const delay = Math.min(BASE_DELAY * Math.pow(1.5, retryCount), 300000);
    return Math.round(delay / 1000); // Convert to seconds
  };

  return (
    <div className="min-h-screen flex flex-col items-center justify-center p-4">
      <div className="bg-white p-8 rounded-lg shadow-md max-w-md w-full text-center">
        <div className="mb-6">
          <h1 className="text-2xl font-bold text-orange-600 mb-2">Application Temporarily Unavailable</h1>
          <p className="text-gray-600 mb-4">
            We&apos;re experiencing technical difficulties. Our team is working to restore service as quickly as possible.
          </p>
          
          {!maxRetriesReached ? (
            <p className="text-sm text-gray-500">
              The page will automatically check for availability with increasing intervals.
              {getNextRetryDelay() && (
                <span className="block mt-1">
                  Next check in: {getNextRetryDelay()} seconds
                </span>
              )}
            </p>
          ) : (
            <p className="text-sm text-red-500">
              Automatic retries have stopped after {MAX_RETRIES} attempts. 
              You can still check manually using the button below.
            </p>
          )}
          
          {retryCount > 0 && (
            <p className="text-xs text-gray-400 mt-2">
              Retry attempts: {retryCount} / {MAX_RETRIES}
            </p>
          )}
        </div>
        
        <div className="flex justify-center">
          <Button
            label={isRetrying ? 'Checking...' : 'Retry Now'}
            variant="primary"
            onClick={handleManualRetry}
            disabled={isRetrying}
            className="mx-2"
          />
        </div>
      </div>
    </div>
  );
}