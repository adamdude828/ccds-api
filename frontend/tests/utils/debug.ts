import { Page, Response } from '@playwright/test';

export interface DebugOptions {
  captureConsole?: boolean;
  captureNetwork?: boolean;
  logPrefix?: string;
}

/**
 * Sets up debugging utilities for a Playwright page
 * @param page - The Playwright page instance
 * @param options - Debug options
 */
export function setupDebugLogging(page: Page, options: DebugOptions = {}) {
  const {
    captureConsole = true,
    captureNetwork = true,
    logPrefix = '[TEST]'
  } = options;

  if (captureConsole) {
    // Capture browser console logs
    page.on('console', msg => {
      const type = msg.type();
      const text = msg.text();
      
      // Format the output based on type
      switch (type) {
        case 'error':
          console.error(`${logPrefix} Console ERROR:`, text);
          break;
        case 'warning':
          console.warn(`${logPrefix} Console WARN:`, text);
          break;
        case 'info':
          console.info(`${logPrefix} Console INFO:`, text);
          break;
        default:
          console.log(`${logPrefix} Console [${type}]:`, text);
      }
      
      // Also log the location if available
      const location = msg.location();
      if (location.url) {
        console.log(`${logPrefix}   at ${location.url}:${location.lineNumber}`);
      }
    });

    // Capture page errors
    page.on('pageerror', error => {
      console.error(`${logPrefix} Page ERROR:`, error.message);
      console.error(`${logPrefix} Stack:`, error.stack);
    });
  }

  if (captureNetwork) {
    // Capture failed requests
    page.on('requestfailed', request => {
      console.error(`${logPrefix} Request FAILED:`, request.url());
      console.error(`${logPrefix}   Method:`, request.method());
      console.error(`${logPrefix}   Failure:`, request.failure()?.errorText);
    });

    // Optionally capture all network requests
    page.on('request', request => {
      const url = request.url();
      // Filter out noise - only log API calls
      if (url.includes('/api/') || url.includes('set-active-provider')) {
        console.log(`${logPrefix} API Request:`, request.method(), url);
        const postData = request.postData();
        if (postData) {
          console.log(`${logPrefix}   Body:`, postData);
        }
      }
    });

    // Capture responses for API calls
    page.on('response', response => {
      const url = response.url();
      if (url.includes('/api/') || url.includes('set-active-provider')) {
        console.log(`${logPrefix} API Response:`, response.status(), url);
        
        // Log response body for non-successful responses
        if (response.status() >= 400) {
          response.text().then(body => {
            console.log(`${logPrefix}   Response body:`, body);
          }).catch(() => {
            // Ignore if we can't get the body
          });
        }
      }
    });
  }
}

/**
 * Waits for and logs a specific API call
 * @param page - The Playwright page instance
 * @param urlPattern - Pattern to match in the URL
 * @param timeout - Timeout in milliseconds
 * @returns The response or null if timeout
 */
export async function waitForApiCall(
  page: Page, 
  urlPattern: string, 
  timeout: number = 5000
): Promise<Response | null> {
  try {
    const response = await page.waitForResponse(
      response => response.url().includes(urlPattern),
      { timeout }
    );
    console.log(`[API] Captured ${urlPattern}:`, response.status());
    return response;
  } catch (error) {
    console.log(`[API] No call detected for ${urlPattern} within ${timeout}ms`);
    return null;
  }
}

/**
 * Logs the current state of form inputs on the page
 * @param page - The Playwright page instance
 * @param selector - Optional selector to scope the search
 */
export async function logFormState(page: Page, selector?: string) {
  const scope = selector ? await page.locator(selector) : page;
  
  // Log all radio buttons
  const radios = await scope.locator('input[type="radio"]').all();
  console.log(`[FORM] Found ${radios.length} radio buttons:`);
  for (let i = 0; i < radios.length; i++) {
    const radio = radios[i];
    const name = await radio.getAttribute('name');
    const checked = await radio.isChecked();
    const value = await radio.getAttribute('value') || i.toString();
    console.log(`[FORM]   Radio[${i}] name="${name}" value="${value}" checked=${checked}`);
  }
  
  // Log all checkboxes
  const checkboxes = await scope.locator('input[type="checkbox"]').all();
  console.log(`[FORM] Found ${checkboxes.length} checkboxes:`);
  for (let i = 0; i < checkboxes.length; i++) {
    const checkbox = checkboxes[i];
    const name = await checkbox.getAttribute('name');
    const checked = await checkbox.isChecked();
    console.log(`[FORM]   Checkbox[${i}] name="${name}" checked=${checked}`);
  }
}