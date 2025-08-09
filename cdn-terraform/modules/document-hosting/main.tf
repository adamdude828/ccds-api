# Resource Group for Document Hosting
resource "azurerm_resource_group" "documents" {
  name     = "${var.project_name}-${var.environment}-documents-rg"
  location = var.location
  
  tags = {
    Environment = var.environment
    Project     = var.project_name
    Purpose     = "Document Hosting"
    ManagedBy   = "Terraform"
  }
}

# Storage Account for Documents
resource "azurerm_storage_account" "documents" {
  name                     = "${replace(var.project_name, "-", "")}${var.environment}docs"
  resource_group_name      = azurerm_resource_group.documents.name
  location                 = azurerm_resource_group.documents.location
  account_tier             = "Standard"
  account_replication_type = var.storage_replication_type
  
  public_network_access_enabled    = true
  allow_nested_items_to_be_public  = true
  
  static_website {
    index_document     = "index.html"
    error_404_document = "404.html"
  }
  
  blob_properties {
    cors_rule {
      allowed_headers    = ["*"]
      allowed_methods    = ["GET", "HEAD", "OPTIONS"]
      allowed_origins    = ["*"]
      exposed_headers    = ["*"]
      max_age_in_seconds = 3600
    }
  }
  
  tags = {
    Environment = var.environment
    Project     = var.project_name
    Purpose     = "Document Storage"
    ManagedBy   = "Terraform"
  }
}

# Blob Container for Documents
resource "azurerm_storage_container" "documents" {
  name                  = "documents"
  storage_account_name  = azurerm_storage_account.documents.name
  container_access_type = "blob"
}

# Azure Front Door (Standard/Premium) Profile
resource "azurerm_cdn_frontdoor_profile" "documents" {
  name                = "${var.project_name}-${var.environment}-afd"
  resource_group_name = azurerm_resource_group.documents.name
  sku_name            = var.cdn_sku # e.g. "Standard_AzureFrontDoor" or "Premium_AzureFrontDoor"

  tags = {
    Environment = var.environment
    Project     = var.project_name
    Purpose     = "Document Front Door"
    ManagedBy   = "Terraform"
  }
}

# AFD Endpoint
resource "azurerm_cdn_frontdoor_endpoint" "documents" {
  name                     = "${var.project_name}-${var.environment}-docs"
  cdn_frontdoor_profile_id = azurerm_cdn_frontdoor_profile.documents.id

  tags = {
    Environment = var.environment
    Project     = var.project_name
    Purpose     = "Document AFD Endpoint"
    ManagedBy   = "Terraform"
  }
}

# AFD Origin Group
resource "azurerm_cdn_frontdoor_origin_group" "documents" {
  name                     = "${var.project_name}-${var.environment}-origin-group"
  cdn_frontdoor_profile_id = azurerm_cdn_frontdoor_profile.documents.id

  session_affinity_enabled = false

  health_probe {
    path                = "/"
    protocol            = "Https"
    interval_in_seconds = 60
    request_type        = "HEAD"
  }

  load_balancing {
    sample_size                 = 4
    successful_samples_required = 3
  }
}

# AFD Origin pointing to the Storage Account (blob endpoint)
resource "azurerm_cdn_frontdoor_origin" "storage" {
  name                          = "storage-origin"
  cdn_frontdoor_origin_group_id = azurerm_cdn_frontdoor_origin_group.documents.id

  host_name                      = azurerm_storage_account.documents.primary_blob_host
  http_port                      = 80
  https_port                     = 443
  origin_host_header             = azurerm_storage_account.documents.primary_blob_host
  certificate_name_check_enabled = true
  priority                       = 1
  weight                         = 1000
  enabled                        = true
}

# AFD Route for documents
resource "azurerm_cdn_frontdoor_route" "documents" {
  name                          = "${var.project_name}-${var.environment}-route"
  cdn_frontdoor_endpoint_id     = azurerm_cdn_frontdoor_endpoint.documents.id
  cdn_frontdoor_origin_group_id = azurerm_cdn_frontdoor_origin_group.documents.id
  cdn_frontdoor_origin_ids      = [azurerm_cdn_frontdoor_origin.storage.id]

  enabled                       = true
  patterns_to_match             = ["/documents/*"]
  supported_protocols           = ["Http", "Https"]
  forwarding_protocol           = "HttpsOnly"
  https_redirect_enabled        = true

  cache {
    query_string_caching_behavior = "UseQueryString"
    compression_enabled           = true
    content_types_to_compress     = [
      "application/json",
      "text/plain",
      "text/html",
      "text/css",
      "text/javascript",
      "application/x-javascript",
      "application/javascript"
    ]
  }

  link_to_default_domain = true

  depends_on = [
    azurerm_cdn_frontdoor_origin.storage
  ]
}

# AFD Custom Domain - supports both Azure DNS and external DNS hosting
resource "azurerm_cdn_frontdoor_custom_domain" "documents" {
  count                    = var.custom_domain_name != "" ? 1 : 0
  name                     = replace(var.custom_domain_name, ".", "-")
  cdn_frontdoor_profile_id = azurerm_cdn_frontdoor_profile.documents.id
  host_name                = var.custom_domain_name

  # Use Azure DNS validation only if dns_zone_id is provided, otherwise use domain control validation
  dns_zone_id = var.dns_zone_id != "" ? var.dns_zone_id : null

  tls {
    certificate_type    = "ManagedCertificate"
    minimum_tls_version = "TLS12"
  }
}

# If a custom domain is configured, consider creating a separate route resource bound via that domain in Portal/CLI as provider may not support attaching here.
# Role Assignments for Service Principal (unchanged)
resource "azurerm_role_assignment" "storage_blob_contributor" {
  count = var.service_principal_object_id != "" ? 1 : 0
  
  scope                = azurerm_storage_account.documents.id
  role_definition_name = "Storage Blob Data Contributor"
  principal_id         = var.service_principal_object_id
  
  depends_on = [azurerm_storage_account.documents]
}

resource "azurerm_role_assignment" "storage_account_contributor" {
  count = var.service_principal_object_id != "" ? 1 : 0
  
  scope                = azurerm_storage_account.documents.id
  role_definition_name = "Storage Account Contributor"
  principal_id         = var.service_principal_object_id
  
  depends_on = [azurerm_storage_account.documents]
} 