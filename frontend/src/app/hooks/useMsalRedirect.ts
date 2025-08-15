import { useEffect, useState } from 'react';
import { BrowserAuthError, InteractionStatus } from '@azure/msal-browser';
import { useRouter, usePathname } from 'next/navigation';
import { useMsal } from '@azure/msal-react';
import { makeAuthenticatedRequest } from '@/lib/auth';
import { NetworkError, PermissionError, AuthenticationError } from '@/types/errors';
import { checkApiHealth } from '@/utils/api/health';

export function useMsalRedirect(): { error: string | null; initialized: boolean } {
  const router = useRouter();
  const pathname = usePathname();
  const { instance, inProgress, accounts } = useMsal();
  const [initialized, setInitialized] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    // Function to check authorization against backend API
    const checkUserAuthorization = async (): Promise<boolean> => {
      try {
        // If no account yet, skip API call and let auth flow proceed
        if (!accounts || accounts.length === 0) {
          return false;
        }
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
          // Let MSAL handle the login redirect - gated to avoid loops
          if (inProgress === InteractionStatus.None) {
            instance.loginRedirect();
          }
          return false;
        }
        
        return data.authorized === true;
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
          // Force re-authentication
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
        // Handle redirect promise
        const authResult = await instance.handleRedirectPromise();
        
        // Get all accounts
        const currentAccounts = instance.getAllAccounts();
        
        if (authResult?.account) {
          // Set active account after redirect
          instance.setActiveAccount(authResult.account);
          console.log("Redirect successful, active account set:", authResult.account);
          
          // Check authorization
          if (!(await checkUserAuthorization())) {
            return;
          }
          
          const redirectPath = process.env.NEXT_PUBLIC_REDIRECT_PATH || '/videos';
          if (!window.location.pathname.includes(redirectPath.substring(1))) {
            router.push(redirectPath);
          }
        } else if (currentAccounts.length > 0) {
          // If we have accounts but no redirect result, set the first one as active
          instance.setActiveAccount(currentAccounts[0]);
          console.log("Setting existing account as active:", currentAccounts[0]);
          
          // Check authorization
          if (!(await checkUserAuthorization())) {
            return;
          }
          
          if (window.location.pathname === '/') {
            const redirectPath = process.env.NEXT_PUBLIC_REDIRECT_PATH || '/videos';
            router.push(redirectPath);
          }
        } else {
          console.log("No active account found");
          // If on protected routes, do not trigger login here; let MsalAuthenticationTemplate handle it
          const isProtectedRoute = pathname?.startsWith('/videos') || pathname?.startsWith('/documents') || pathname?.startsWith('/dashboard');
          if (!isProtectedRoute && inProgress === InteractionStatus.None) {
            instance.loginRedirect();
          }
        }
      } catch (error) {
        if (error instanceof BrowserAuthError) {
          console.error("MSAL redirect error:", error.message);
          setError(error.message);
        } else {
          console.error("Unexpected error during MSAL redirect:", error);
          setError("An unexpected error occurred during authentication redirect");
        }
      } finally {
        setInitialized(true);
      }
    };

    // Only process when no interaction is ongoing to reduce loop risk
    if (inProgress === InteractionStatus.None) {
      handleRedirect();
    }
  }, [instance, router, inProgress, pathname, accounts]);

  return { error, initialized };
}
