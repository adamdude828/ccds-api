import { Configuration, IPublicClientApplication } from "@azure/msal-browser";
import { TestAuthProvider } from "@/utils/testAuth";

export const msalConfig: Configuration = {
  auth: {
    clientId: process.env.NEXT_PUBLIC_AZURE_CLIENT_ID || '',
    authority: `https://login.microsoftonline.com/${process.env.NEXT_PUBLIC_AZURE_TENANT_ID || ''}`,
    redirectUri: process.env.NODE_ENV === 'development' ? "http://localhost:3000" : `${process.env.NEXT_PUBLIC_APP_URL}`,
  },
  cache: {
    cacheLocation: "sessionStorage",
    storeAuthStateInCookie: false
  }
};

export const getAuthProvider = (): IPublicClientApplication | TestAuthProvider => {
  if (process.env.NEXT_PUBLIC_USE_TEST_AUTH === 'true') {
    return new TestAuthProvider();
  }
  
  // Return the real MSAL instance for production
  // This will be handled by the MSAL Provider in _app.tsx
  return null as unknown as IPublicClientApplication;
};
