# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Essential Commands

### Development
```bash
npm run dev          # Start Next.js development server (port 3000)
npm run build        # Build production bundle
npm start            # Start production server
npm run lint         # Run ESLint
npm run verify       # TypeScript type checking + linting
```

### Testing
```bash
npm test             # Run Jest unit tests
npm test -- --watch  # Run Jest in watch mode
npm test -- path/to/test.ts  # Run specific test file
cd tests && npx playwright test    # Run Playwright E2E tests
npx playwright test --ui  # Run Playwright with UI mode
```

## Notes and Memories
- When running tests, use `--reporter=list` so that the HTML reporter does not hang the command

## Architecture Overview

This is a Next.js 15 template with Microsoft Azure AD authentication and the Challenger component library.

### Core Technologies
- **Next.js 15.3.2** with App Router (`src/app/` directory)
- **React 18** with TypeScript
- **Azure AD Authentication** via MSAL (Microsoft Authentication Library)
- **Tailwind CSS** for styling
- **@challenger-school/do-git-mis-components-storybook** component library

### Authentication Flow
1. All authentication is handled through Azure AD using MSAL
2. `src/app/config/authConfig.ts` contains MSAL configuration
3. Protected routes use `MsalAuthenticationTemplate` in dashboard layout
4. API calls use `getAuthToken()` from `src/lib/auth.ts` to acquire tokens
5. `makeAuthenticatedRequest()` helper adds Bearer token to API requests

### API Integration Pattern
- Base API URL from `NEXT_PUBLIC_API_URL` environment variable
- All API utilities in `src/utils/api/`
- Standard pattern: `makeAuthenticatedRequest()` wrapper for authenticated calls
- Bearer token authentication required for all API endpoints

### Key Environment Variables
```
NEXT_PUBLIC_API_URL              # Backend API endpoint (optional)
NEXT_PUBLIC_AZURE_CLIENT_ID      # Azure AD application ID
NEXT_PUBLIC_AZURE_TENANT_ID      # Azure AD tenant ID
NEXT_PUBLIC_APP_URL              # Frontend application URL
```

### Project Structure
- `/src/app/` - Next.js App Router pages and layouts
- `/src/app/dashboard/` - Protected routes requiring authentication
- `/src/utils/api/` - API client utilities
- `/src/types/` - TypeScript type definitions
- `/src/components/` - Shared React components
- `/tests/` - Playwright E2E tests with Page Object Model

### Development Notes
- TypeScript strict mode is enabled
- Path alias `@/*` maps to `src/*`
- Docker builds use standalone Next.js output mode
- MSAL cache stores in sessionStorage
- Test authentication mode available via `NEXT_PUBLIC_USE_TEST_AUTH`