terraform {
  required_version = ">= 1.3.0"
  
  required_providers {
    azuread = {
      source  = "hashicorp/azuread"
      version = "~> 2.47.0"
    }
    azurerm = {
      source  = "hashicorp/azurerm"
      version = "~> 3.85.0"
    }
  }
}

provider "azuread" {
  # Configuration options
  # tenant_id = "your-tenant-id" # Optional: can be set via ARM_TENANT_ID env var
}

provider "azurerm" {
  features {}
  skip_provider_registration = true  # Skip automatic provider registration
  # subscription_id = "your-subscription-id" # Optional: can be set via ARM_SUBSCRIPTION_ID env var
} 