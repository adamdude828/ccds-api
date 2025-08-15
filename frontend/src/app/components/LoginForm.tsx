'use client';

import { useMsal } from "@azure/msal-react";
import { Button } from '@challenger-school/do-git-mis-components-storybook';
import { useRouter } from 'next/navigation';
import { InteractionStatus } from '@azure/msal-browser';
import { useState } from 'react';

export default function LoginForm() {
  const { instance, inProgress } = useMsal();
  const router = useRouter();
  const [isLoggingIn, setIsLoggingIn] = useState(false);

  const handleLogin = async () => {
    setIsLoggingIn(true);
    
    // Don't attempt login if there's already an interaction in progress
    if (inProgress !== InteractionStatus.None) {
      console.log('Interaction in progress, skipping login attempt');
      setIsLoggingIn(false);
      return;
    }

    try {
      const config = instance.getConfiguration();
      console.log("MSAL config details:", {
        clientId: config.auth.clientId,
        authority: config.auth.authority,
        redirectUri: config.auth.redirectUri
      });
      
      await instance.loginRedirect({
        scopes: ["User.Read", "openid", "profile", "email"]
      });
      
      console.log("Initiating login redirect...");
      router.prefetch(process.env.NEXT_PUBLIC_REDIRECT_PATH || '/videos');
    } catch (error) {
      console.error("Login failed:", error);
      if (error instanceof Error) {
        console.error("Error details:", {
          name: error.name,
          message: error.message,
          stack: error.stack
        });
      }
      setIsLoggingIn(false);
    }
  };

  return (
    <div className="space-y-6">
      <Button
        label={isLoggingIn ? "Signing in..." : "Sign in with Microsoft"}
        variant="primary"
        className="w-full"
        onClick={handleLogin}
        disabled={inProgress !== InteractionStatus.None || isLoggingIn}
      />
    </div>
  );
}                   