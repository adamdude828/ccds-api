# Starting a New Project from This Template

## Initial Setup

1. **Clone and rename the project**
   ```bash
   git clone [template-repo] my-new-project
   cd my-new-project
   rm -rf .git
   git init
   ```

2. **Update package.json**
   ```json
   {
     "name": "my-new-project",
     "version": "0.1.0"
   }
   ```

3. **Set up authentication**
   - Create Azure AD app registration
   - Update `.env.local` with your credentials
   - Configure redirect URIs for your domain

## Common Customizations

### 1. Add Your Business Logic

The dashboard is currently a placeholder. Replace it with your application's main functionality:

```typescript
// src/app/dashboard/page.tsx
export default function DashboardPage() {
  // Add your business logic here
}
```

### 2. Add API Routes

Create new API utilities in `src/utils/api/`:

```typescript
// src/utils/api/my-api.ts
import { makeAuthenticatedRequest } from '@/lib/auth';

export async function getMyData() {
  return makeAuthenticatedRequest('/api/my-endpoint');
}
```

### 3. Add New Types

Create TypeScript types in `src/types/`:

```typescript
// src/types/my-types.ts
export interface MyDataType {
  id: string;
  name: string;
  // ... other fields
}
```

### 4. Add New Dashboard Pages

Create new pages under `src/app/dashboard/`:

```typescript
// src/app/dashboard/my-feature/page.tsx
export default function MyFeaturePage() {
  return (
    <div>
      <h1>My Feature</h1>
      {/* Your content */}
    </div>
  );
}
```

### 5. Use Challenger Components

Import and use components from the library:

```typescript
import { 
  Button, 
  Input, 
  Select, 
  Modal,
  ItemGrid 
} from '@challenger-school/do-git-mis-components-storybook';
```

## Deployment Checklist

- [ ] Update all environment variables in production
- [ ] Configure Azure AD redirect URIs for production domain
- [ ] Set up CI/CD pipeline with Azure PAT for npm registry
- [ ] Configure proper CORS settings if using separate API
- [ ] Enable HTTPS only
- [ ] Set up monitoring and logging
- [ ] Review and update security headers

## Troubleshooting Common Issues

### Authentication Issues
- Ensure redirect URIs match exactly (including trailing slashes)
- Check tenant ID and client ID are correct
- Verify user has access to the Azure AD app

### Component Library Issues
- Run `npm run setup-npmrc` if getting 401 errors
- Ensure Azure PAT has package read permissions
- Check PAT expiration date

### Build Issues
- Clear `.next` directory: `rm -rf .next`
- Clear node_modules: `rm -rf node_modules && npm install`
- Check Node.js version (requires 18+) 