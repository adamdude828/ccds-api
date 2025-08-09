/// <reference types="node" />

import { PublicClientApplication } from '@azure/msal-browser';

declare global {
  interface Window {
    msal: PublicClientApplication;
  }
  type RequestInit = globalThis.RequestInit;
}

export {};
