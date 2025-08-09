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
  description = "Hostname of the AFD endpoint"
  value       = azurerm_cdn_frontdoor_endpoint.documents.host_name
}

output "cdn_endpoint_url" {
  description = "Full URL of the AFD endpoint"
  value       = "https://${azurerm_cdn_frontdoor_endpoint.documents.host_name}"
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
  description = "URL pattern for accessing documents via AFD"
  value       = "https://${azurerm_cdn_frontdoor_endpoint.documents.host_name}/${azurerm_storage_container.documents.name}/<document-name>"
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

output "custom_domain_validation_token" {
  description = "Domain validation token for external DNS providers (like Digital Ocean)"
  value       = length(azurerm_cdn_frontdoor_custom_domain.documents) > 0 ? azurerm_cdn_frontdoor_custom_domain.documents[0].validation_token : null
}

output "afd_endpoint_hostname" {
  description = "AFD endpoint hostname for CNAME record creation"
  value       = azurerm_cdn_frontdoor_endpoint.documents.host_name
}

output "custom_domain_resource_id" {
  description = "Resource ID of the AFD custom domain if configured"
  value       = length(azurerm_cdn_frontdoor_custom_domain.documents) > 0 ? azurerm_cdn_frontdoor_custom_domain.documents[0].id : null
}

# Storage Account Identity Information
output "storage_account_id" {
  description = "Resource ID of the storage account"
  value       = azurerm_storage_account.documents.id
}

# Note: We intentionally do NOT output storage account keys here.
# The application should use Azure AD authentication with the service principal
# to generate SAS tokens, not storage account keys. 