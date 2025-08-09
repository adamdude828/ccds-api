require('@testing-library/jest-dom');
const React = require('react');

// Make React available globally for JSX in tests
global.React = React;

global.jest = jest;
global.describe = describe;
global.it = it;
global.expect = expect;
global.beforeEach = beforeEach;
global.afterEach = afterEach;

// Mock fetch globally
global.fetch = jest.fn();

// Reset all mocks after each test
afterEach(() => {
  jest.clearAllMocks();
});

// Add crypto polyfill for MSAL
global.crypto = {
  getRandomValues: (arr) => {
    for (let i = 0; i < arr.length; i++) {
      arr[i] = Math.floor(Math.random() * 256);
    }
    return arr;
  },
  randomUUID: () => {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
      const r = (Math.random() * 16) | 0;
      const v = c === 'x' ? r : (r & 0x3) | 0x8;
      return v.toString(16);
    });
  }
}; 