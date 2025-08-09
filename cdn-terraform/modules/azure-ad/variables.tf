variable "environment" {
  description = "Environment name (dev, staging, prod)"
  type        = string
}

variable "project_name" {
  description = "Name of the project"
  type        = string
  default     = "do-git-mis-next"
}

variable "app_url" {
  description = "Homepage URL for the application"
  type        = string
  default     = "http://localhost:3000"
}

variable "redirect_uris" {
  description = "OAuth redirect URIs for web platform"
  type        = list(string)
  default     = ["http://localhost:3000/api/auth/callback/azure-ad"]
}

variable "spa_redirect_uris" {
  description = "OAuth redirect URIs for single-page application"
  type        = list(string)
  default     = ["http://localhost:3000"]
}

variable "client_secret_expiration_days" {
  description = "Number of days until the client secret expires"
  type        = number
  default     = 90
}

variable "enable_secret_rotation" {
  description = "Enable creation of a rotation secret"
  type        = bool
  default     = false
} 