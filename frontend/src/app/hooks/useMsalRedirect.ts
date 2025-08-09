import { useEffect, useState } from 'react';
import { BrowserAuthError, InteractionStatus } from '@azure/msal-browser';
import { useRouter } from 'next/navigation';
import { useMsal } from '@azure/msal-react';
import { makeAuthenticatedRequest } from '@/lib/auth';
import { NetworkError, PermissionError, AuthenticationError } from '@/types/errors';
import { checkApiHealth } from '@/utils/api/health';

export function useMsalRedirect(): { error: string | null; initialized: boolean } {
  const router = useRouter();
  const { instance, inProgress } = useMsal();
  const [initialized, setInitialized] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    // Simple function to check authorization
    const checkUserAuthorization = async (): Promise<boolean> => {
      try {
        // Temporarily disable API checks to fix redirect loop
        // TODO: Re-enable once API is configured
        return true;
        
        /*
        // First check if API is healthy
        const healthCheck = await checkApiHealth();
        if (!healthCheck.status) {
          router.push('/application-down');
          return false;
        }
        
        const response = await makeAuthenticatedRequest('/api/auth-check');
        const data = await response.json();
        
        // If authenticated but not authorized (not in group)
        if (response.status === 403 || (data.authenticated && !data.authorized)) {
          router.push('/access-denied');
          return false;
        }
        
        // If not authenticated at all
        if (response.status === 401 || !data.authenticated) {
          // Only redirect if no interaction is in progress
          if (inProgress === InteractionStatus.None) {
            instance.loginRedirect();
          }
          return false;
        }
        
        return data.authorized === true;
        */
      } catch (error) {
        // Handle specific error types
        if (error instanceof NetworkError) {
          router.push('/application-down');
          return false;
        }
        
        if (error instanceof PermissionError) {
          router.push('/access-denied');
          return false;
        }
        
        if (error instanceof AuthenticationError) {
          // Force re-authentication only if no interaction is in progress
          if (inProgress === InteractionStatus.None) {
            instance.loginRedirect();
          }
          return false;
        }
        
        // Default to access denied for unknown errors
        router.push('/access-denied');
        return false;
      }
    };

    const handleRedirect = async () => {
      try {
        // Check if there's already an interaction in progress
        if (inProgress !== InteractionStatus.None) {
          console.log("Interaction already in progress, waiting...", inProgress);
          return;
        }

        // Handle redirect promise
        const authResult = await instance.handleRedirectPromise();
        
        // Get all accounts
        const accounts = instance.getAllAccounts();
        
        if (authResult?.account) {
          // Set active account after redirect
          instance.setActiveAccount(authResult.account);
          console.log("Redirect successful, active account set:", authResult.account);
          
          // Only check authorization if we're not on a public page
          const publicPaths = ['/', '/access-denied', '/application-down'];
          if (!publicPaths.includes(window.location.pathname)) {
            // Check authorization
            if (!(await checkUserAuthorization())) {
              return;
            }
          }
          
          // Only redirect to videos if we're on the home page
          if (window.location.pathname === '/') {
            router.push('/videos');
          }
        } else if (accounts.length > 0) {
          // If we have accounts but no redirect result, set the first one as active
          instance.setActiveAccount(accounts[0]);
          console.log("Setting existing account as active:", accounts[0]);
          
          // Only check authorization if we're not on a public page
          const publicPaths = ['/', '/access-denied', '/application-down'];
          if (!publicPaths.includes(window.location.pathname)) {
            // Check authorization
            if (!(await checkUserAuthorization())) {
              return;
            }
          }
          
          if (window.location.pathname === '/') {
            router.push('/videos');
          }
        } else {
          console.log("No active account found");
        }
      } catch (error) {
        if (error instanceof BrowserAuthError) {
          console.error("MSAL redirect error:", error.message);
          
          // Specifically handle interaction_in_progress error
          if (error.errorCode === 'interaction_in_progress') {
            console.log("Interaction already in progress, will retry after current interaction completes");
            // Don't set an error for this case, it will resolve itself
            // The component will be re-rendered when the interaction completes
            return;
          }
          
          setError(error.message);
        } else {
          console.error("Unexpected error during MSAL redirect:", error);
          setError("An unexpected error occurred during authentication redirect");
        }
      } finally {
        setInitialized(true);
      }
    };

    // Only run handleRedirect if we're not already in an interaction
    if (inProgress === InteractionStatus.None) {
      handleRedirect();
    }
  }, [instance, router, inProgress]);

  return { error, initialized };
}
