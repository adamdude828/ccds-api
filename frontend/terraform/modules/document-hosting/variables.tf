variable "environment" {
  description = "Environment name (dev, staging, prod)"
  type        = string
}

variable "project_name" {
  description = "Name of the project"
  type        = string
  default     = "do-git-mis-next"
}

variable "location" {
  description = "Azure region for resources"
  type        = string
  default     = "East US"
}

variable "storage_replication_type" {
  description = "Storage replication type (LRS, GRS, RAGRS, ZRS, GZRS, RAGZRS)"
  type        = string
  default     = "LRS"
}

variable "cdn_sku" {
  description = "CDN Profile SKU"
  type        = string
  default     = "Standard_Microsoft"
}

variable "custom_domain_name" {
  description = "Custom domain name for CDN (e.g., documents.example.com)"
  type        = string
  default     = ""
}

variable "service_principal_object_id" {
  description = "The Object ID of the service principal that needs access to the storage account"
  type        = string
  default     = ""
} 