# Document Hosting Outputs

output "storage_account_name" {
  description = "Name of the storage account for documents"
  value       = azurerm_storage_account.documents.name
}

output "storage_account_primary_blob_endpoint" {
  description = "Primary blob endpoint of the storage account"
  value       = azurerm_storage_account.documents.primary_blob_endpoint
}

output "storage_account_primary_web_endpoint" {
  description = "Primary web endpoint of the storage account (static website)"
  value       = azurerm_storage_account.documents.primary_web_endpoint
}

output "storage_container_name" {
  description = "Name of the blob container for documents"
  value       = azurerm_storage_container.documents.name
}

output "cdn_endpoint_hostname" {
  description = "Hostname of the CDN endpoint"
  value       = azurerm_cdn_endpoint.documents.fqdn
}

output "cdn_endpoint_url" {
  description = "Full URL of the CDN endpoint"
  value       = "https://${azurerm_cdn_endpoint.documents.fqdn}"
}

output "resource_group_name" {
  description = "Name of the resource group for document hosting"
  value       = azurerm_resource_group.documents.name
}

output "resource_group_location" {
  description = "Location of the resource group"
  value       = azurerm_resource_group.documents.location
}

# Helper output for accessing documents
output "document_access_url_pattern" {
  description = "URL pattern for accessing documents via CDN"
  value       = "https://${azurerm_cdn_endpoint.documents.fqdn}/${azurerm_storage_container.documents.name}/<document-name>"
}

# Custom Domain Outputs
output "custom_domain_hostname" {
  description = "Custom domain hostname if configured"
  value       = var.custom_domain_name != "" ? var.custom_domain_name : null
}

output "custom_domain_url" {
  description = "Full URL of the custom domain if configured"
  value       = var.custom_domain_name != "" ? "https://${var.custom_domain_name}" : null
}

output "custom_domain_cname_record" {
  description = "CNAME record needed for custom domain validation"
  value       = var.custom_domain_name != "" ? "CNAME ${var.custom_domain_name} -> ${azurerm_cdn_endpoint.documents.fqdn}" : null
}

# Storage Account Identity Information
output "storage_account_id" {
  description = "Resource ID of the storage account"
  value       = azurerm_storage_account.documents.id
}

# Note: We intentionally do NOT output storage account keys here.
# The application should use Azure AD authentication with the service principal
# to generate SAS tokens, not storage account keys. 