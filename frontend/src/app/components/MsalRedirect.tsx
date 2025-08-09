'use client';

import { useMsalRedirect } from '../hooks/useMsalRedirect';

export default function MsalRedirect() {
  // Use the new hook to handle redirect
  const { error } = useMsalRedirect();

  if (error) {
    console.error('MSAL Redirect Error:', error);
  }

  // This component doesn't render anything
  return null;
}  