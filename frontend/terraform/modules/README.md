# Terraform Modules

This directory contains reusable Terraform modules for the DO-GIT-MIS-Next project.

## Available Modules

### azure-ad
Creates and configures Azure AD (Entra ID) application registration for authentication.

**Resources created:**
- Azure AD Application
- Service Principal
- Client Secret(s)

**Example usage:**
```hcl
module "azure_ad" {
  source = "../../modules/azure-ad"
  
  environment                   = "dev"
  project_name                  = "my-project"
  app_url                       = "https://myapp.com"
  redirect_uris                 = ["https://myapp.com/api/auth/callback/azure-ad"]
  spa_redirect_uris             = ["https://myapp.com"]
  client_secret_expiration_days = 90
  enable_secret_rotation        = true
}
```

### document-hosting
Creates infrastructure for hosting and serving documents via Azure Storage, CDN, and Front Door.

**Resources created:**
- Resource Group
- Storage Account with blob container
- CDN Profile and Endpoint
- Azure Front Door with URL rewriting rules

**Example usage:**
```hcl
module "document_hosting" {
  source = "../../modules/document-hosting"
  
  environment              = "dev"
  project_name             = "my-project"
  location                 = "East US"
  storage_replication_type = "LRS"
  cdn_sku                  = "Standard_Microsoft"
  front_door_sku           = "Standard_AzureFrontDoor"
}
```

## Module Structure

Each module follows this structure:
```
module-name/
├── main.tf        # Main resource definitions
├── variables.tf   # Input variable declarations
└── outputs.tf     # Output value declarations
```

## Benefits of This Approach

1. **No Code Duplication**: Changes to modules automatically apply to all environments
2. **Consistency**: All environments use the same resource definitions
3. **Maintainability**: Single source of truth for infrastructure
4. **Version Control**: Modules can be versioned if moved to separate repositories
5. **Testing**: Modules can be tested independently 