output "resource_group_name" {
  value = azurerm_resource_group.rg.name
}

# Azure Front Door outputs
output "frontdoor_profile_id" {
  value       = azurerm_cdn_frontdoor_profile.frontdoor.id
  description = "The ID of the Azure Front Door profile"
}

output "frontdoor_endpoint_hostname" {
  value       = azurerm_cdn_frontdoor_endpoint.endpoint.host_name
  description = "The hostname of the Azure Front Door endpoint"
}

output "frontdoor_endpoint_fqdn" {
  value       = "https://${azurerm_cdn_frontdoor_endpoint.endpoint.host_name}"
  description = "The full URL of the Azure Front Door endpoint"
}