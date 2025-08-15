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
  default     = ["http://localhost:3000/", "http://localhost:3000/videos", "http://localhost:3000/videos/", "http://localhost:3000/dashboard", "http://localhost:3000/documents", "http://localhost:3000/documents/"]
}

variable "primary_redirect_path" {
  description = "Primary redirect path for MSAL authentication (e.g., '/videos', '/dashboard')"
  type        = string
  default     = "/videos"
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