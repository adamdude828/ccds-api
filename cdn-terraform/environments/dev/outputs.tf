# Azure AD Module Outputs
output "application_id" {
  description = "Application (client) ID"
  value       = module.azure_ad.application_id
}

output "tenant_id" {
  description = "Directory (tenant) ID"
  value       = module.azure_ad.tenant_id
}

output "client_secret" {
  description = "Client secret value (sensitive)"
  value       = module.azure_ad.client_secret
  sensitive   = true
}

output "rotation_secret" {
  description = "Rotation client secret value (sensitive)"
  value       = module.azure_ad.rotation_secret
  sensitive   = true
}

output "client_secret_expiration" {
  description = "Client secret expiration date"
  value       = module.azure_ad.client_secret_expiration
}

output "rotation_secret_expiration" {
  description = "Rotation client secret expiration date"
  value       = module.azure_ad.rotation_secret_expiration
}

# Document Hosting Module Outputs
output "storage_account_name" {
  description = "Name of the storage account for documents"
  value       = module.document_hosting.storage_account_name
}

output "storage_account_primary_blob_endpoint" {
  description = "Primary blob endpoint of the storage account"
  value       = module.document_hosting.storage_account_primary_blob_endpoint
}

output "storage_account_primary_web_endpoint" {
  description = "Primary web endpoint of the storage account (static website)"
  value       = module.document_hosting.storage_account_primary_web_endpoint
}

output "cdn_endpoint_hostname" {
  description = "Hostname of the CDN endpoint"
  value       = module.document_hosting.cdn_endpoint_hostname
}

output "cdn_endpoint_url" {
  description = "Full URL of the CDN endpoint"
  value       = module.document_hosting.cdn_endpoint_url
}

output "document_access_url_pattern" {
  description = "URL pattern for accessing documents via CDN"
  value       = module.document_hosting.document_access_url_pattern
}

# Custom Domain Outputs
output "custom_domain_hostname" {
  description = "Custom domain hostname if configured"
  value       = module.document_hosting.custom_domain_hostname
}

output "custom_domain_url" {
  description = "Full URL of the custom domain if configured"
  value       = module.document_hosting.custom_domain_url
}

output "custom_domain_cname_record" {
  description = "CNAME record needed for custom domain validation"
  value       = module.document_hosting.custom_domain_cname_record
}

# Formatted outputs for convenience
output "environment_variables" {
  description = "Environment variables to set for the application"
  value = {
    NEXT_PUBLIC_AZURE_CLIENT_ID     = module.azure_ad.application_id
    NEXT_PUBLIC_AZURE_TENANT_ID     = module.azure_ad.tenant_id
    AZURE_AD_CLIENT_SECRET          = module.azure_ad.client_secret
    NEXT_PUBLIC_APP_URL             = var.app_url
    NEXTAUTH_SECRET                 = "Generate a random string for production"
    NEXTAUTH_URL                    = var.app_url
  }
  sensitive = true
}

output "nextauth_configuration" {
  description = "Configuration for NextAuth.js"
  value = <<-EOT
    
    === NextAuth.js Configuration ===
    
    Add these environment variables to your .env.local file:
    
    NEXT_PUBLIC_AZURE_CLIENT_ID=${module.azure_ad.application_id}
    NEXT_PUBLIC_AZURE_TENANT_ID=${module.azure_ad.tenant_id}
    NEXT_PUBLIC_APP_URL=${var.app_url}
    AZURE_AD_CLIENT_SECRET=<see terraform output -raw client_secret>
    
    NEXTAUTH_URL=${var.app_url}
    NEXTAUTH_SECRET=<generate a random string>
    
    === Redirect URIs configured ===
    
    Web platform: ${join(", ", var.redirect_uris)}
    SPA platform: ${join(", ", var.spa_redirect_uris)}
    
    === Application Details ===
    
    Application ID: ${module.azure_ad.application_id}
    
    EOT
}

output "document_upload_instructions" {
  description = "Instructions for uploading documents"
  value = <<-EOT
    To upload documents to the storage account:
    
    1. Using Azure Portal:
       - Navigate to Storage Account: ${module.document_hosting.storage_account_name}
       - Go to Containers > ${module.document_hosting.storage_container_name}
       - Upload your PDF files
    
    2. Using Azure CLI:
       az storage blob upload \
         --account-name ${module.document_hosting.storage_account_name} \
         --container-name ${module.document_hosting.storage_container_name} \
         --name "your-document.pdf" \
         --file "./path/to/your-document.pdf"
    
    3. Access documents via:
       - Direct Storage: ${module.document_hosting.storage_account_primary_blob_endpoint}${module.document_hosting.storage_container_name}/<filename>
       - CDN URL: ${module.document_hosting.cdn_endpoint_url}/${module.document_hosting.storage_container_name}/<filename>
       ${var.custom_domain_name != "" ? "- Custom Domain: ${module.document_hosting.custom_domain_url}/${module.document_hosting.storage_container_name}/<filename>" : ""}
       
    Note: 
    - CDN propagation can take 5-10 minutes for new content
    - Custom domains require DNS CNAME configuration to validate
    
    4. Test the deployment:
       Run the test script from the module directory:
       cd terraform/modules/document-hosting && ./test-cdn-simple.sh
  EOT
}
