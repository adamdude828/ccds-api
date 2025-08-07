terraform {
  required_providers {
    azurerm = {
      source  = "hashicorp/azurerm"
      version = "~>3.0"
    }
  }
    backend "azurerm" {
        resource_group_name  = "rg-mis-gs-global-w"
        storage_account_name = "starchivegpv2ls02"
        container_name       = "ccds-api-laravel-qa-terraform-stack"
        key                  = "terraform.tfstate"
    }

}

provider "azurerm" {
  features {}
}