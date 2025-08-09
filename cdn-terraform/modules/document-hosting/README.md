# Document Hosting Module

This Terraform module creates an Azure infrastructure for hosting and serving documents through Azure CDN with optional custom domain support.

## Architecture

The module creates the following resources:
- **Resource Group**: Container for all document hosting resources
- **Storage Account**: Stores the actual document files with public blob access
- **Blob Container**: Named "documents" for organizing files
- **CDN Profile**: Azure CDN for global content delivery
- **CDN Endpoint**: The actual CDN endpoint serving content from the storage account
- **CDN Custom Domain** (optional): Custom domain attached directly to the CDN endpoint

## Features

- **Public Access**: Documents are publicly accessible via CDN
- **Caching**: Intelligent caching rules with 7-day default and 30-day for PDFs
- **Compression**: Automatic compression for common file types
- **CORS Support**: Configured for cross-origin requests
- **Service Principal Access**: Optional RBAC roles for programmatic access
- **Static Website**: Storage account configured with static website hosting

## Usage

```hcl
module "document_hosting" {
  source = "./modules/document-hosting"

  environment                 = "dev"
  project_name               = "myproject"
  location                   = "East US"
  cdn_sku                    = "Standard_Microsoft"
  custom_domain_name         = "documents.example.com"  # Optional
  service_principal_object_id = "xxxxx-xxxxx-xxxxx"    # Optional
}
```

## Custom Domain Setup

When using a custom domain with CDN:

1. **DNS Configuration**: After the module creates the CDN custom domain resource, you need to create a CNAME record:
   ```
   documents.example.com CNAME yourproject-dev-docs.azureedge.net
   ```

2. **Validation**: Azure CDN will validate the CNAME record before the custom domain becomes active

3. **HTTPS**: Azure CDN provides free managed certificates for custom domains

## Accessing Documents

Documents can be accessed via multiple URLs:

- **CDN Endpoint**: `https://yourproject-dev-docs.azureedge.net/documents/file.pdf`
- **Custom Domain** (if configured): `https://documents.example.com/documents/file.pdf`
- **Direct Storage** (not recommended): Via storage account blob endpoint

## Variables

| Name | Description | Type | Default |
|------|-------------|------|---------|
| environment | Environment name (dev, staging, prod) | string | - |
| project_name | Name of the project | string | "do-git-mis-next" |
| location | Azure region for resources | string | "East US" |
| storage_replication_type | Storage replication type | string | "LRS" |
| cdn_sku | CDN Profile SKU | string | "Standard_Microsoft" |
| custom_domain_name | Custom domain for CDN | string | "" |
| service_principal_object_id | Service principal for storage access | string | "" |

## Outputs

| Name | Description |
|------|-------------|
| storage_account_name | Name of the storage account |
| cdn_endpoint_hostname | Hostname of the CDN endpoint |
| cdn_endpoint_url | Full URL of the CDN endpoint |
| custom_domain_url | Custom domain URL if configured |
| custom_domain_cname_record | CNAME record needed for validation |
| document_access_url_pattern | URL pattern for accessing documents |

