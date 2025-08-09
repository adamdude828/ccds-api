# General Variables
variable "project_name" {
  description = "Name of the project"
  type        = string
  default     = "do-git-mis-next"
}

variable "environment" {
  description = "Environment name (dev, staging, prod)"
  type        = string
  validation {
    condition     = contains(["dev", "staging", "prod"], var.environment)
    error_message = "Environment must be one of: dev, staging, prod"
  }
}

# Application URLs
variable "app_url" {
  description = "Homepage URL for the application"
  type        = string
  default     = "http://localhost:3000"
}

variable "redirect_uris" {
  description = "Redirect URIs for web platform"
  type        = list(string)
  default     = ["http://localhost:3000/api/auth/callback/azure-ad"]
}

variable "spa_redirect_uris" {
  description = "Redirect URIs for single-page application"
  type        = list(string)
  default     = ["http://localhost:3000/", "http://localhost:3000/videos", "http://localhost:3000/videos/", "http://localhost:3000/documents", "http://localhost:3000/documents/"]
}

# Client Secret Configuration
variable "client_secret_expiration_days" {
  description = "Number of days until client secret expires"
  type        = number
  default     = 90
}

variable "enable_secret_rotation" {
  description = "Create a second secret for rotation purposes"
  type        = bool
  default     = false
}

# Document Hosting Variables
variable "location" {
  description = "Azure region for resources"
  type        = string
  default     = "East US"
}

variable "storage_replication_type" {
  description = "Storage account replication type"
  type        = string
  default     = "LRS" # Locally Redundant Storage
  validation {
    condition     = contains(["LRS", "GRS", "RAGRS", "ZRS", "GZRS", "RAGZRS"], var.storage_replication_type)
    error_message = "Storage replication type must be one of: LRS, GRS, RAGRS, ZRS, GZRS, RAGZRS"
  }
}

variable "cdn_sku" {
  description = "CDN Profile SKU"
  type        = string
  default     = "Standard_Microsoft"
  validation {
    condition     = contains(["Standard_Microsoft", "Standard_Akamai", "Standard_Verizon", "Premium_Verizon"], var.cdn_sku)
    error_message = "CDN SKU must be one of: Standard_Microsoft, Standard_Akamai, Standard_Verizon, Premium_Verizon"
  }
}

variable "custom_domain_name" {
  description = "Custom domain name for CDN document hosting"
  type        = string
  default     = ""
}
