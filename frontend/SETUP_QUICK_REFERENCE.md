# Quick Setup Reference

## 🚀 5-Minute Setup for New Projects

### 1. Initial Setup
```bash
# Clone and install
git clone [this-template]
cd [your-project-name]
npm install

# Set up Challenger component library access
npm run setup-npmrc
# Enter your Azure PAT when prompted
```

### 2. Create `.env.local`
```env
# Required for authentication
NEXT_PUBLIC_AZURE_CLIENT_ID=your-client-id
NEXT_PUBLIC_AZURE_TENANT_ID=your-tenant-id
NEXT_PUBLIC_APP_URL=http://localhost:3000
NEXT_PUBLIC_REDIRECT_PATH=/videos

# Optional
NEXT_PUBLIC_API_URL=your-api-url
NEXT_PUBLIC_USE_TEST_AUTH=false
```

### 3. Azure AD App Registration Checklist
- [ ] Create new app registration in Azure Portal
- [ ] Add redirect URI: `http://localhost:3000/videos`
- [ ] Enable ID tokens
- [ ] Copy Client ID and Tenant ID to `.env.local`
- [ ] For production: Add production redirect URI

### 4. Start Development
```bash
npm run dev
```

## 📦 What's Included

### Microsoft Authentication (MSAL)
- Pre-configured in `src/app/config/authConfig.ts`
- Auth providers in `src/app/providers/`
- Protected routes under `/videos`, `/documents`, `/dashboard`
- Configurable redirect path via `NEXT_PUBLIC_REDIRECT_PATH`
- Automatic token management

### Challenger Component Library
- UI components: Button, Input, Select, Modal, ItemGrid
- Design tokens integrated with Tailwind
- Consistent styling across projects

## 🔐 Security Reminders
- **Never commit**: `.npmrc`, `.env.local`
- Add to `.gitignore` if not already there
- Rotate Azure PAT tokens regularly
- Use Azure Key Vault in production

## 🛠️ Common Commands
```bash
npm run dev          # Start development
npm test            # Run unit tests
npm run test:e2e    # Run Playwright tests
npm run build       # Build for production
npm run lint        # Run linter
```

## 📚 Key Files to Customize
1. `src/app/page.tsx` - Home page
2. `src/app/videos/page.tsx` - Videos landing page (default redirect)
3. `src/app/dashboard/page.tsx` - Dashboard page
4. `src/app/documents/page.tsx` - Documents page
5. `src/app/layout.tsx` - Root layout
6. `tailwind.config.ts` - Add custom styles
7. `public/` - Add your logos/assets

## 🆘 Quick Troubleshooting
- **Can't install packages**: Check `.npmrc` and Azure PAT
- **Auth not working**: Verify redirect URIs match
- **Styles broken**: Ensure Tailwind content includes component library
- **401 errors**: Azure PAT may be expired

## 📖 Full Documentation
See [README.md](./README.md) for comprehensive documentation. 