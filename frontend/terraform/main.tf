# Dev Environment Configuration

# Azure AD Application Module
module "azure_ad" {
  source = "../../modules/azure-ad"
  
  environment                   = var.environment
  project_name                  = var.project_name
  app_url                       = var.app_url
  redirect_uris                 = var.redirect_uris
  spa_redirect_uris             = var.spa_redirect_uris
  client_secret_expiration_days = var.client_secret_expiration_days
  enable_secret_rotation        = var.enable_secret_rotation
}

# Document Hosting Module
module "document_hosting" {
  source = "../../modules/document-hosting"
  
  environment              = var.environment
  project_name             = var.project_name
  location                 = var.location
  storage_replication_type = var.storage_replication_type
  cdn_sku                  = var.cdn_sku
} 