'use client';

import { MsalProvider } from "@azure/msal-react";
import { Configuration, PublicClientApplication, BrowserAuthError } from "@azure/msal-browser";
import { ReactNode, useEffect, useState } from "react";
import { msalConfig } from "../config/authConfig";
import MsalRedirect from '../components/MsalRedirect';

// Create a singleton pattern for MSAL instance
let msalInstance: PublicClientApplication | null = null;
let msalInitPromise: Promise<void> | null = null;

export function getMsalInstance(): PublicClientApplication {
  if (!msalInstance) {
    msalInstance = new PublicClientApplication(msalConfig as Configuration);
    msalInitPromise = msalInstance.initialize();
  }
  return msalInstance;
}

export function getMsalInitPromise(): Promise<void> | null {
  if (typeof window === 'undefined') {
    return null;
  }
  
  getMsalInstance(); // Ensure instance is created
  return msalInitPromise;
}

interface AuthProviderProps {
  children: ReactNode;
}

export function AuthProvider({ children }: AuthProviderProps) {
  const [initialized, setInitialized] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const initializeMsal = async () => {
      try {
        // Get the existing initialization promise (already started in getMsalInstance)
        const initPromise = getMsalInitPromise();
        if (initPromise) {
          await initPromise;
          console.log("MSAL initialized successfully");
        }
        setInitialized(true);
      } catch (error) {
        if (error instanceof BrowserAuthError) {
          console.error("MSAL initialization error:", error.message);
          setError(error.message);
        } else {
          console.error("Unexpected error during MSAL initialization:", error);
          setError("An unexpected error occurred during authentication initialization");
        }
        setInitialized(true);
      }
    };

    initializeMsal();
  }, []);

  if (!initialized) {
    return <div>Initializing authentication...</div>;
  }

  if (error) {
    return <div className="text-red-500">Authentication Error: {error}</div>;
  }

  return (
    <MsalProvider instance={getMsalInstance()}>
      <MsalRedirect />
      {children}
    </MsalProvider>
  );
}