# Next.js Template with Microsoft Authentication & Challenger Components

This is a Next.js template with Microsoft Azure AD (Entra ID) authentication and the Challenger component library pre-configured. Use this as a starting point for future projects that require enterprise authentication and consistent UI components.

## Features

- ✅ **Next.js 15** with App Router
- ✅ **Microsoft Authentication** (Azure AD/Entra ID) via MSAL
- ✅ **Challenger Component Library** (@challenger-school/do-git-mis-components-storybook)
- ✅ **TypeScript** support
- ✅ **Tailwind CSS** with Challenger design tokens
- ✅ **Jest** and **Playwright** testing setup
- ✅ **Docker** support

## Prerequisites

- Node.js 18+
- Azure DevOps access (for Challenger component library)
- Azure AD application registration (for authentication)
- Azure Personal Access Token (PAT)

## Quick Start

### 1. Clone and Install

```bash
# Clone the repository
git clone [repository-url]
cd [project-name]

# Install dependencies
npm install
```

### 2. Configure NPM for Challenger Components

The Challenger component library is hosted in Azure DevOps and requires authentication:

```bash
# Run the setup script
npm run setup-npmrc

# You'll be prompted for your Azure PAT
# Get your PAT from: https://dev.azure.com/ChallengerSchoolDevOps/_usersSettings/tokens
```

This creates a `.npmrc` file with your credentials. **Never commit this file!**

### 3. Set Up Environment Variables

Create a `.env.local` file in the root directory:

```env
# Azure AD Authentication
NEXT_PUBLIC_AZURE_CLIENT_ID=your-azure-app-client-id
NEXT_PUBLIC_AZURE_TENANT_ID=your-azure-tenant-id
NEXT_PUBLIC_APP_URL=http://localhost:3000

# Backend API (if applicable)
NEXT_PUBLIC_API_URL=your-api-url

# Test Mode (optional)
NEXT_PUBLIC_USE_TEST_AUTH=false
```

### 4. Run the Development Server

```bash
npm run dev
```

Open [http://localhost:3000](http://localhost:3000) to see the application.

## Microsoft Authentication Setup

This template uses Microsoft Authentication Library (MSAL) for Azure AD authentication.

### Azure AD Configuration

1. **Register an application** in Azure AD:
   - Go to [Azure Portal](https://portal.azure.com) → Azure Active Directory → App registrations
   - Click "New registration"
   - Set redirect URI: `http://localhost:3000/dashboard` (for development)
   - For production, add your production URL: `https://your-domain.com/dashboard`

2. **Configure authentication**:
   - Enable "ID tokens" under Authentication → Implicit grant
   - Add your redirect URIs
   - Set supported account types (single or multi-tenant)

3. **Get your credentials**:
   - Client ID: Found in Overview page
   - Tenant ID: Found in Overview page

### Authentication Configuration

The authentication is configured in `src/app/config/authConfig.ts`:

```typescript
export const msalConfig: Configuration = {
  auth: {
    clientId: process.env.NEXT_PUBLIC_AZURE_CLIENT_ID || '',
    authority: `https://login.microsoftonline.com/${process.env.NEXT_PUBLIC_AZURE_TENANT_ID}`,
    redirectUri: process.env.NODE_ENV === 'development' 
      ? "http://localhost:3000/dashboard" 
      : `${process.env.NEXT_PUBLIC_APP_URL}/dashboard`,
  },
  cache: {
    cacheLocation: "sessionStorage",
    storeAuthStateInCookie: false
  }
};
```

### Protected Routes

The dashboard and its sub-routes are protected by default. The authentication flow:
1. User visits the app → redirected to Microsoft login
2. After successful login → redirected to `/dashboard`
3. Session stored in browser storage
4. Automatic token refresh

## Azure AD App Registration with Terraform

This template includes Terraform configuration to automatically create and manage your Azure AD application registration.

### Quick Setup

1. **Bootstrap Terraform for your environment**:
   ```bash
   ./scripts/bootstrap-terraform-simple.sh -e dev
   ```

2. **Create the Azure AD Application**:
   ```bash
   cd terraform/environments/dev
   ./init.sh
   terraform plan
   terraform apply
   ```

3. **Get your configuration**:
   ```bash
   # Display all configuration details
   terraform output nextauth_configuration
   
   # Get just the client secret
   terraform output -raw client_secret
   ```

The Terraform setup will:
- Create an Azure AD application with the correct redirect URIs
- Generate a client secret with 90-day expiration
- Configure the required Microsoft Graph permissions
- Output all the environment variables you need

See the [Terraform README](terraform/README.md) for detailed instructions on managing secrets, rotation, and production configuration.

## Challenger Component Library

This template includes the Challenger component library which provides:
- Consistent UI components (Button, Input, Select, Modal, etc.)
- Design tokens (colors, spacing, typography)
- Grid and layout components
- Form components with validation

### Using Components

```tsx
import { Button, Input, Select, Modal } from '@challenger-school/do-git-mis-components-storybook';

export function MyComponent() {
  return (
    <div>
      <Button variant="primary" onClick={handleClick}>
        Click me
      </Button>
      <Input 
        label="Name"
        value={name}
        onChange={setName}
        required
      />
    </div>
  );
}
```

### Tailwind Configuration

The template uses Challenger design tokens in Tailwind:

```javascript
// tailwind.config.ts
import { colors } from "@challenger-school/do-git-mis-components-storybook";

export default {
  theme: {
    extend: {
      colors: colors,
    },
  },
  // Component styles are included from:
  content: [
    "./node_modules/@challenger-school/do-git-mis-components-storybook/dist/*.js",
  ]
}
```

## Testing

### Unit Tests (Jest)

```bash
# Run tests
npm test

# Watch mode
npm run test:watch

# Coverage
npm run test:coverage
```

### E2E Tests (Playwright)

For testing with Microsoft authentication, see the [Playwright AD Authentication Guide](./docs/playwright-ad-authentication.md).

```bash
# Run E2E tests
npm run test:e2e

# Run with UI
npm run test:e2e:ui
```

## Docker Support

Build and run with Docker:

```bash
# Build
docker build -t my-app .

# Run
docker run -p 3000:3000 \
  -e NEXT_PUBLIC_AZURE_CLIENT_ID=your-client-id \
  -e NEXT_PUBLIC_AZURE_TENANT_ID=your-tenant-id \
  -e NEXT_PUBLIC_APP_URL=http://localhost:3000 \
  my-app
```

## Project Structure

```
src/
├── app/                    # Next.js app directory
│   ├── config/            # Configuration files
│   │   └── authConfig.ts  # MSAL configuration
│   ├── providers/         # React providers
│   │   ├── AuthProvider.tsx
│   │   └── MsalProvider.tsx
│   ├── dashboard/         # Protected routes
│   └── layout.tsx         # Root layout with auth
├── components/            # Shared components
├── lib/                   # Utility libraries
└── types/                 # TypeScript types
```

## Deployment

### Environment Variables Required

- `NEXT_PUBLIC_AZURE_CLIENT_ID` - Azure AD application ID
- `NEXT_PUBLIC_AZURE_TENANT_ID` - Azure AD tenant ID  
- `NEXT_PUBLIC_APP_URL` - Your application URL
- `NEXT_PUBLIC_API_URL` - Backend API URL (if applicable)

### CI/CD Considerations

1. **NPM Registry Access**: Your CI/CD pipeline needs access to Azure DevOps:
   - Store your Azure PAT as a secret
   - Generate `.npmrc` during build

2. **Build Arguments**: Pass environment variables during Docker build:
   ```bash
   docker build \
     --build-arg NEXT_PUBLIC_AZURE_CLIENT_ID=$AZURE_CLIENT_ID \
     --build-arg NEXT_PUBLIC_AZURE_TENANT_ID=$AZURE_TENANT_ID \
     -t my-app .
   ```

## Troubleshooting

### Authentication Issues

- **Login redirect not working**: Check redirect URIs in Azure AD match your app URL
- **Token errors**: Clear browser storage and try again
- **MFA issues**: Ensure your Azure AD policies allow the authentication flow

### Component Library Issues

- **401 Unauthorized**: Your Azure PAT may be expired or invalid
- **Package not found**: Ensure `.npmrc` is properly configured
- **Style issues**: Make sure Tailwind content includes the component library path

## Security Notes

1. **Never commit**:
   - `.npmrc` file (contains your PAT)
   - `.env.local` file (contains secrets)
   - Any authentication tokens or credentials

2. **Production considerations**:
   - Use Azure Key Vault for secrets
   - Enable HTTPS only
   - Configure proper CORS policies
   - Set up monitoring and logging

## Resources

- [Next.js Documentation](https://nextjs.org/docs)
- [MSAL.js Documentation](https://github.com/AzureAD/microsoft-authentication-library-for-js)
- [Azure AD Documentation](https://docs.microsoft.com/en-us/azure/active-directory/)
- [Playwright Testing Guide](./docs/playwright-ad-authentication.md)

## License

[Your License]

## Template Notes

This template has been cleaned from a production application and provides:
- ✅ Microsoft Azure AD authentication fully configured
- ✅ Challenger component library integration
- ✅ Protected dashboard route structure
- ✅ TypeScript and Tailwind CSS setup
- ✅ Testing infrastructure (Jest + Playwright)
- ✅ Docker support
- ✅ Health check API example

All application-specific business logic has been removed, leaving you with a clean starting point for your Next.js enterprise application.
