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
  
  # Ensure public access is allowed for CDN
  public_network_access_enabled = true
  allow_nested_items_to_be_public = true
  
  # Enable static website hosting for better CDN compatibility
  static_website {
    index_document     = "index.html"
    error_404_document = "404.html"
  }
  
  # Enable blob versioning (optional - uncomment to keep version history)
  # blob_properties {
  #   versioning_enabled = true
  #   
  #   # Optional: Also enable soft delete to recover deleted blobs
  #   delete_retention_policy {
  #     days = 7
  #   }
  # }
  
  # Enable CORS for CDN
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
  container_access_type = "blob" # Allows public read access to blobs
}

# CDN Profile
resource "azurerm_cdn_profile" "documents" {
  name                = "${var.project_name}-${var.environment}-cdn"
  location            = azurerm_resource_group.documents.location
  resource_group_name = azurerm_resource_group.documents.name
  sku                 = var.cdn_sku
  
  tags = {
    Environment = var.environment
    Project     = var.project_name
    Purpose     = "Document CDN"
    ManagedBy   = "Terraform"
  }
}

# CDN Endpoint
resource "azurerm_cdn_endpoint" "documents" {
  name                = "${var.project_name}-${var.environment}-docs"
  profile_name        = azurerm_cdn_profile.documents.name
  location            = azurerm_resource_group.documents.location
  resource_group_name = azurerm_resource_group.documents.name
  
  # Use the primary blob endpoint for standard blob access
  origin_host_header = azurerm_storage_account.documents.primary_blob_host
  
  origin {
    name      = "storage"
    host_name = azurerm_storage_account.documents.primary_blob_host
  }
  
  # Optimization for general web delivery
  optimization_type = "GeneralWebDelivery"
  
  # Query string caching behavior - use query strings for unique cache entries
  querystring_caching_behaviour = "UseQueryString"
  
  # Compression settings
  is_compression_enabled = true
  content_types_to_compress = [
    "application/pdf",
    "application/json",
    "text/plain",
    "text/html",
    "text/css",
    "text/javascript",
    "application/x-javascript",
    "application/javascript"
  ]
  
  # Basic caching rules
  global_delivery_rule {
    cache_expiration_action {
      behavior = "Override"
      duration = "7.00:00:00" # 7 days cache for all content
    }
  }
  
  # Delivery rule for PDF files with specific caching
  delivery_rule {
    name  = "PDFCacheRule"
    order = 1
    
    url_file_extension_condition {
      operator         = "Equal"
      match_values     = ["pdf"]
      transforms       = ["Lowercase"]
    }
    
    cache_expiration_action {
      behavior = "Override"
      duration = "30.00:00:00" # 30 days cache for PDFs
    }
  }
  
  tags = {
    Environment = var.environment
    Project     = var.project_name
    Purpose     = "Document CDN Endpoint"
    ManagedBy   = "Terraform"
  }
}

# CDN Custom Domain (conditional - only created if custom_domain_name is provided)
resource "azurerm_cdn_endpoint_custom_domain" "documents" {
  count = var.custom_domain_name != "" ? 1 : 0
  
  name            = replace(var.custom_domain_name, ".", "-")
  cdn_endpoint_id = azurerm_cdn_endpoint.documents.id
  host_name       = var.custom_domain_name
  
  # IMPORTANT: Azure CDN requires DNS validation BEFORE creating the custom domain
  # You MUST create a CNAME record first:
  #   YOUR_DOMAIN (e.g., documents.example.com) -> CDN_ENDPOINT.azureedge.net
  # 
  # Steps:
  # 1. Run 'terraform plan' to see the CDN endpoint hostname
  # 2. Create the CNAME record at your DNS provider
  # 3. Wait for DNS propagation (verify with: nslookup YOUR_DOMAIN)
  # 4. Run 'terraform apply' to create the custom domain
}

# Role Assignment for Service Principal
# This allows the app to generate SAS tokens and manage blobs
resource "azurerm_role_assignment" "storage_blob_contributor" {
  count = var.service_principal_object_id != "" ? 1 : 0
  
  scope                = azurerm_storage_account.documents.id
  role_definition_name = "Storage Blob Data Contributor"
  principal_id         = var.service_principal_object_id
  
  depends_on = [azurerm_storage_account.documents]
}

# Additional role for generating SAS tokens
resource "azurerm_role_assignment" "storage_account_contributor" {
  count = var.service_principal_object_id != "" ? 1 : 0
  
  scope                = azurerm_storage_account.documents.id
  role_definition_name = "Storage Account Contributor"
  principal_id         = var.service_principal_object_id
  
  depends_on = [azurerm_storage_account.documents]
} 