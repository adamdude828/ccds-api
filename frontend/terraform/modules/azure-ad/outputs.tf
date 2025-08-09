output "application_id" {
  description = "The Application (client) ID"
  value       = azuread_application.main.client_id
}

output "tenant_id" {
  description = "The Azure AD Tenant ID"
  value       = data.azuread_client_config.current.tenant_id
}

output "client_secret" {
  description = "The client secret for the application"
  value       = azuread_application_password.main.value
  sensitive   = true
}

output "rotation_secret" {
  description = "The rotation client secret for the application"
  value       = var.enable_secret_rotation ? azuread_application_password.rotation[0].value : ""
  sensitive   = true
}

output "client_secret_expiration" {
  description = "The expiration date of the primary client secret"
  value       = azuread_application_password.main.end_date
}

output "rotation_secret_expiration" {
  description = "The expiration date of the rotation client secret"
  value       = var.enable_secret_rotation ? azuread_application_password.rotation[0].end_date : ""
}

output "app_url" {
  description = "The configured application URL"
  value       = var.app_url
}

output "redirect_uris" {
  description = "The configured redirect URIs"
  value       = var.redirect_uris
}

output "spa_redirect_uris" {
  description = "The configured SPA redirect URIs"
  value       = var.spa_redirect_uris
}

output "service_principal_id" {
  description = "The Service Principal ID (Enterprise Application ID)"
  value       = azuread_service_principal.main.id
}

output "service_principal_object_id" {
  description = "The Service Principal Object ID"
  value       = azuread_service_principal.main.object_id
} 