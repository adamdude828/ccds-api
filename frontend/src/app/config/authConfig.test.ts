import { msalConfig } from './authConfig';

describe('authConfig', () => {
  const originalEnv = process.env;

  beforeEach(() => {
    jest.resetModules();
    process.env = { ...originalEnv };
  });

  afterEach(() => {
    process.env = originalEnv;
  });

  it('should have correct auth configuration structure', () => {
    expect(msalConfig).toHaveProperty('auth');
    expect(msalConfig.auth).toHaveProperty('clientId');
    expect(msalConfig.auth).toHaveProperty('authority');
    expect(msalConfig.auth).toHaveProperty('redirectUri');
  });

  it('should have correct cache configuration', () => {
    expect(msalConfig).toHaveProperty('cache');
    expect(msalConfig.cache).toHaveProperty('cacheLocation', 'sessionStorage');
    expect(msalConfig.cache).toHaveProperty('storeAuthStateInCookie', false);
  });

  it('should use environment variables for configuration', () => {
    const { auth } = msalConfig;
    
    expect(auth.clientId).toBeDefined();
    expect(auth.authority).toBeDefined();
    
    // Check that authority includes tenant ID
    expect(auth.authority).toMatch(/https:\/\/login\.microsoftonline\.com\//);
  });

  it('should build redirect URI based on environment', () => {
    const { auth } = msalConfig;
    
    // In test environment, window.location.origin will be empty
    // So it should use the environment variable
    expect(auth.redirectUri).toBeDefined();
  });

  it('should handle different environments for redirectUri', () => {
    const originalEnv = process.env.NODE_ENV;
    
    // Test development environment
    Object.defineProperty(process.env, 'NODE_ENV', {
      value: 'development',
      configurable: true
    });
    jest.resetModules();
    const { msalConfig: devConfig } = require('./authConfig');
    expect(devConfig.auth.redirectUri).toBe('http://localhost:3000/dashboard');
    
    // Test production environment
    Object.defineProperty(process.env, 'NODE_ENV', {
      value: 'production',
      configurable: true
    });
    process.env.NEXT_PUBLIC_APP_URL = 'https://myapp.com';
    jest.resetModules();
    const { msalConfig: prodConfig } = require('./authConfig');
    expect(prodConfig.auth.redirectUri).toBe('https://myapp.com/dashboard');
    
    Object.defineProperty(process.env, 'NODE_ENV', {
      value: originalEnv,
      configurable: true
    });
  });

  it('should export getAuthProvider function', () => {
    const { getAuthProvider } = require('./authConfig');
    expect(getAuthProvider).toBeDefined();
    expect(typeof getAuthProvider).toBe('function');
  });
});