# Terraform Configuration for Azure AD (Entra ID) App Registration

This directory contains Terraform configurations for creating an Azure AD application registration for authentication in the DO-GIT-MIS-Next application.

## Overview

This Terraform setup creates:
- **Azure AD Application** with proper redirect URIs for authentication
- **Service Principal** for the application
- **Client Secret** with configurable expiration
- **Optional Rotation Secret** for zero-downtime secret rotation
- **Document Hosting Infrastructure**:
  - **Storage Account** for hosting PDF and other documents
  - **CDN Profile and Endpoint** for global content delivery
  - **Azure Front Door** for advanced URL rewriting and routing

## Prerequisites

1. **Azure CLI**: Install from [here](https://docs.microsoft.com/en-us/cli/azure/install-azure-cli)
2. **Terraform**: Version 1.3.0 or higher
3. **Azure AD Permissions**: Application.ReadWrite.All or Application Administrator role

## Quick Start

### 1. Bootstrap the Environment

Run the bootstrap script to set up the environment-specific configuration:

```bash
# For development environment
./scripts/bootstrap-terraform-simple.sh -e dev

# For staging environment
./scripts/bootstrap-terraform-simple.sh -e staging

# For production environment
./scripts/bootstrap-terraform-simple.sh -e prod
```

The bootstrap script will:
- Check Azure CLI authentication
- Create environment-specific directory structure
- Generate terraform.tfvars with appropriate defaults
- Create initialization scripts

### 2. Initialize and Apply

Navigate to your environment directory and run:

```bash
cd terraform/environments/dev
./init.sh
terraform plan
terraform apply
```

### 3. Configure Your Application

After applying, get the configuration for your Next.js application:

```bash
# Display formatted configuration
terraform output nextauth_configuration

# Get the client secret (sensitive)
terraform output -raw client_secret
```

## Directory Structure

```
terraform/
├── modules/               # Reusable Terraform modules
│   ├── azure-ad/          # Azure AD application module
│   │   ├── main.tf
│   │   ├── variables.tf
│   │   └── outputs.tf
│   └── document-hosting/  # Document hosting infrastructure module
│       ├── main.tf
│       ├── variables.tf
│       └── outputs.tf
├── environments/          # Environment-specific configurations
│   ├── dev/               # Development environment
│   │   ├── main.tf        # Module declarations
│   │   ├── variables.tf   # Variable definitions
│   │   ├── outputs.tf     # Output definitions
│   │   ├── providers.tf   # Provider configuration
│   │   └── backend.tf     # State backend configuration
│   ├── staging/           # Staging environment
│   └── prod/              # Production environment
├── terraform.tfvars.example # Example variables file
└── README.md              # This file
```

## Module-Based Architecture

This Terraform configuration uses a module-based approach to avoid code duplication:

1. **Shared Modules** (`modules/`): Contains reusable infrastructure definitions
2. **Environment Configurations** (`environments/`): References modules with environment-specific values
3. **No Symlinks Required**: Changes to modules automatically apply to all environments

## Configuration

### Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `environment` | Environment name (dev, staging, prod) | Required |
| `project_name` | Name of the project | `do-git-mis-next` |
| `app_url` | Homepage URL for the application | `http://localhost:3000` |
| `redirect_uris` | OAuth redirect URIs for web platform | `["http://localhost:3000/api/auth/callback/azure-ad"]` |
| `spa_redirect_uris` | OAuth redirect URIs for SPA platform | `["http://localhost:3000"]` |
| `client_secret_expiration_days` | Days until client secret expires | `90` |
| `enable_secret_rotation` | Create a rotation secret | `false` |
| `location` | Azure region for resources | `East US` |
| `storage_replication_type` | Storage replication type (LRS, GRS, RAGRS, ZRS, GZRS, RAGZRS) | `LRS` |
| `cdn_sku` | CDN Profile SKU | `Standard_Microsoft` |
| `front_door_sku` | Azure Front Door SKU | `Standard_AzureFrontDoor` |

### Example terraform.tfvars

```hcl
environment  = "prod"
project_name = "do-git-mis-next"

# Production URLs
app_url           = "https://app.example.com"
redirect_uris     = ["https://app.example.com/api/auth/callback/azure-ad"]
spa_redirect_uris = ["https://app.example.com"]

# Enable secret rotation for production
enable_secret_rotation = true
```

## Application Configuration

### Environment Variables

After running Terraform, set these environment variables in your application:

```bash
AZURE_AD_CLIENT_ID=<application_id>
AZURE_AD_CLIENT_SECRET=<client_secret>
AZURE_AD_TENANT_ID=<tenant_id>
NEXTAUTH_URL=<app_url>
NEXTAUTH_SECRET=<generate_random_string>
```

### NextAuth.js Configuration

The Terraform output provides the exact configuration needed for NextAuth.js. The application is configured with:
- Microsoft Graph permissions: User.Read, openid, profile
- Both web and SPA redirect URIs
- Proper token configuration

## Document Hosting Infrastructure

### Overview

The document hosting infrastructure provides a scalable solution for hosting and serving PDF documents and other static files with:

- **Azure Storage Account**: Secure blob storage for documents
- **Azure CDN**: Global content delivery with caching
- **Azure Front Door**: Advanced routing and URL rewriting capabilities

### Features

1. **URL Rewriting**: Transform URLs for better user experience
   - `/docs/file.pdf` → `/documents/file.pdf`
   - `/pdfs/file.pdf` → `/documents/pdfs/file.pdf`

2. **Performance Optimization**:
   - CDN caching with 7-day TTL for PDFs
   - Compression for supported content types
   - Global edge locations for low latency

3. **Security**:
   - HTTPS-only access via Front Door
   - Public read access for blobs (configurable)
   - CORS configuration for web applications

### Uploading Documents

After deployment, upload documents using:

```bash
# Azure CLI
az storage blob upload \
  --account-name $(terraform output -raw storage_account_name) \
  --container-name documents \
  --name "my-document.pdf" \
  --file "./my-document.pdf"
```

### Accessing Documents

Documents can be accessed via multiple URLs:

1. **Storage Direct**: `https://<storage-account>.blob.core.windows.net/documents/file.pdf`
2. **CDN**: `https://<cdn-endpoint>.azureedge.net/documents/file.pdf`
3. **Front Door**: `https://<front-door>.azurefd.net/docs/file.pdf`

### Configuration Options

For production environments, consider:

```hcl
# Production-ready configuration
storage_replication_type = "GRS"                     # Geo-redundant storage
front_door_sku          = "Premium_AzureFrontDoor"  # Advanced security features
```

## Secret Management

### Automatic Expiration

Client secrets expire after the configured number of days (default: 90). Plan to rotate secrets before expiration.

### Secret Rotation

Enable secret rotation to create two secrets with staggered expiration dates:

```hcl
enable_secret_rotation = true
```

This creates:
- Primary secret: expires in 90 days
- Rotation secret: expires in 120 days (30 days offset)

### Rotation Process

1. Update your application to use the rotation secret
2. Remove the expired primary secret
3. Create a new rotation secret

```bash
# View both secrets
terraform output -raw client_secret
terraform output -raw rotation_secret
```

## Updating Configuration

### Adding Redirect URIs

Update the `redirect_uris` or `spa_redirect_uris` in your terraform.tfvars:

```hcl
redirect_uris = [
  "http://localhost:3000/api/auth/callback/azure-ad",
  "https://staging.example.com/api/auth/callback/azure-ad"
]
```

### Changing Permissions

To modify Microsoft Graph permissions, edit the `required_resource_access` block in main.tf.

## Troubleshooting

### Common Issues

1. **Permission Denied**: Ensure you have Application.ReadWrite.All permission or Application Administrator role
   ```bash
   az ad app create --display-name test-permissions
   ```

2. **Invalid Redirect URI**: Ensure redirect URIs match exactly, including trailing slashes

3. **Secret Expired**: Check expiration date
   ```bash
   terraform output client_secret_expiration
   ```

### Debugging

Enable Terraform debug logging:
```bash
export TF_LOG=DEBUG
terraform plan
```

## Security Best Practices

1. **Never commit secrets**: The terraform.tfvars file is gitignored
2. **Use short-lived secrets**: Set appropriate expiration (90 days or less)
3. **Enable rotation**: Use secret rotation for production environments
4. **Limit permissions**: Only request necessary Microsoft Graph permissions
5. **Environment isolation**: Use separate app registrations per environment

## Cleanup

To remove the Azure AD application:

```bash
terraform destroy
```

This will:
- Delete the Azure AD application
- Remove the service principal
- Revoke all client secrets

## Additional Resources

- [Azure AD App Registration](https://docs.microsoft.com/en-us/azure/active-directory/develop/quickstart-register-app)
- [NextAuth.js Azure AD Provider](https://next-auth.js.org/providers/azure-ad)
- [Microsoft Graph Permissions](https://docs.microsoft.com/en-us/graph/permissions-reference) 