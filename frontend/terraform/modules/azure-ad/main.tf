# Data source for current Azure AD configuration
data "azuread_client_config" "current" {}

# Azure AD Application for Authentication
resource "azuread_application" "main" {
  display_name     = "${var.project_name}-${var.environment}"
  sign_in_audience = "AzureADMyOrg"
  
  required_resource_access {
    resource_app_id = "00000003-0000-0000-c000-000000000000" # Microsoft Graph
    
    resource_access {
      id   = "e1fe6dd8-ba31-4d61-89e7-88639da4683d" # User.Read
      type = "Scope"
    }
    
    resource_access {
      id   = "37f7f235-527c-4136-accd-4a02d197296e" # openid
      type = "Scope"
    }
    
    resource_access {
      id   = "14dad69e-099b-42c9-810b-d002981feec1" # profile
      type = "Scope"
    }
  }
  
  # Add Azure Storage API permissions
  required_resource_access {
    resource_app_id = "e406a681-f3d4-42a8-90b6-c2b029497af1" # Azure Storage
    
    # user_impersonation - Allows the app to access Azure Storage on behalf of the signed-in user
    resource_access {
      id   = "03e0da56-190b-40ad-a80c-ea378c433f7f"
      type = "Scope"
    }
  }
  
  # API permissions that the application exposes (for app-only access)
  api {
    oauth2_permission_scope {
      admin_consent_description  = "Allow the application to access storage resources"
      admin_consent_display_name = "Access Storage Resources"
      enabled                    = true
      id                         = "00000000-0000-0000-0000-000000000001"
      type                       = "User"
      user_consent_description   = "Allow the application to access storage resources on your behalf"
      user_consent_display_name  = "Access storage resources"
      value                      = "storage.access"
    }
  }
  
  web {
    homepage_url  = var.app_url
    redirect_uris = var.redirect_uris
    
    implicit_grant {
      access_token_issuance_enabled = false
      id_token_issuance_enabled     = true
    }
  }
  
  single_page_application {
    redirect_uris = var.spa_redirect_uris
  }
  
  tags = [var.environment, var.project_name, "Terraform"]
}

# Service Principal
resource "azuread_service_principal" "main" {
  client_id                    = azuread_application.main.client_id
  app_role_assignment_required = false
  
  tags = [var.environment, var.project_name, "Terraform"]
}

# Client Secret
resource "azuread_application_password" "main" {
  application_id = azuread_application.main.id
  display_name   = "Terraform Created - ${var.environment}"
  
  # Rotate secret every 90 days by default
  end_date_relative = "${var.client_secret_expiration_days * 24}h"
}

# Optional: Create a second secret for rotation
resource "azuread_application_password" "rotation" {
  count          = var.enable_secret_rotation ? 1 : 0
  application_id = azuread_application.main.id
  display_name   = "Terraform Created - Rotation - ${var.environment}"
  
  # Offset the expiration to allow for rotation
  end_date_relative = "${(var.client_secret_expiration_days + 30) * 24}h"
} 