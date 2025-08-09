#!/bin/bash

# Simple Bootstrap script for Terraform Azure AD setup
# This script creates the necessary Azure resources for Terraform state management

set -euo pipefail

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_message() {
    local color=$1
    local message=$2
    echo -e "${color}${message}${NC}"
}

# Check if Azure CLI is installed
if ! command -v az &> /dev/null; then
    print_message $RED "Error: Azure CLI is not installed. Please install it first."
    echo "Visit: https://docs.microsoft.com/en-us/cli/azure/install-azure-cli"
    exit 1
fi

# Default values
DEFAULT_PROJECT_NAME="do-git-mis-next"

# Parse command line arguments
ENVIRONMENT=""
PROJECT_NAME=""

while [[ $# -gt 0 ]]; do
    case $1 in
        -e|--environment)
            ENVIRONMENT="$2"
            shift 2
            ;;
        -p|--project)
            PROJECT_NAME="$2"
            shift 2
            ;;
        -h|--help)
            echo "Usage: $0 -e <environment> [-p <project-name>]"
            echo ""
            echo "Options:"
            echo "  -e, --environment    Environment name (required: dev, staging, prod)"
            echo "  -p, --project        Project name (default: $DEFAULT_PROJECT_NAME)"
            echo "  -h, --help          Show this help message"
            exit 0
            ;;
        *)
            print_message $RED "Unknown option: $1"
            exit 1
            ;;
    esac
done

# Validate required parameters
if [ -z "${ENVIRONMENT:-}" ]; then
    print_message $RED "Error: Environment is required. Use -e or --environment"
    exit 1
fi

# Validate environment value
if [[ ! "$ENVIRONMENT" =~ ^(dev|staging|prod)$ ]]; then
    print_message $RED "Error: Environment must be one of: dev, staging, prod"
    exit 1
fi

# Set defaults if not provided
PROJECT_NAME="${PROJECT_NAME:-$DEFAULT_PROJECT_NAME}"

print_message $GREEN "=== Terraform Azure AD Bootstrap Script ==="
print_message $YELLOW "Environment: $ENVIRONMENT"
print_message $YELLOW "Project: $PROJECT_NAME"

# Login to Azure if not already logged in
if ! az account show &> /dev/null; then
    print_message $YELLOW "Please login to Azure..."
    az login
fi

# Get current subscription info
CURRENT_SUB=$(az account show --query name -o tsv)
TENANT_ID=$(az account show --query tenantId -o tsv)
print_message $GREEN "Using subscription: $CURRENT_SUB"
print_message $GREEN "Tenant ID: $TENANT_ID"

# Create environment directory if it doesn't exist
ENV_DIR="terraform/environments/${ENVIRONMENT}"
mkdir -p "$ENV_DIR"

# Create backend configuration file (local state for simplicity)
BACKEND_CONFIG_FILE="${ENV_DIR}/backend.tf"
print_message $YELLOW "Creating backend configuration at: $BACKEND_CONFIG_FILE"

cat > "$BACKEND_CONFIG_FILE" <<EOF
terraform {
  backend "local" {
    path = "${ENVIRONMENT}.tfstate"
  }
}
EOF

# Create environment-specific terraform.tfvars file
TFVARS_FILE="${ENV_DIR}/terraform.tfvars"
print_message $YELLOW "Creating terraform.tfvars at: $TFVARS_FILE"

# Determine default URLs based on environment
if [ "$ENVIRONMENT" = "prod" ]; then
    APP_URL="https://app.example.com"
    REDIRECT_URI="https://app.example.com/api/auth/callback/azure-ad"
    SPA_REDIRECT_URIS='["https://app.example.com/", "https://app.example.com/dashboard"]'
else
    APP_URL="http://localhost:3000"
    REDIRECT_URI="http://localhost:3000/api/auth/callback/azure-ad"
    SPA_REDIRECT_URIS='["http://localhost:3000/", "http://localhost:3000/dashboard"]'
fi

cat > "$TFVARS_FILE" <<EOF
environment = "${ENVIRONMENT}"
project_name = "${PROJECT_NAME}"

# Application URLs
app_url = "${APP_URL}"
redirect_uris = ["${REDIRECT_URI}"]
spa_redirect_uris = ${SPA_REDIRECT_URIS}

# Client Secret Configuration
client_secret_expiration_days = 90
enable_secret_rotation = false
EOF

# Create symbolic links to main terraform files
print_message $YELLOW "Creating symbolic links to terraform files..."
cd "$ENV_DIR"
ln -sf ../../main.tf main.tf 2>/dev/null || true
ln -sf ../../providers.tf providers.tf 2>/dev/null || true
ln -sf ../../variables.tf variables.tf 2>/dev/null || true
ln -sf ../../outputs.tf outputs.tf 2>/dev/null || true
cd - > /dev/null

# Create initialization script
INIT_SCRIPT="${ENV_DIR}/init.sh"
print_message $YELLOW "Creating initialization script at: $INIT_SCRIPT"

cat > "$INIT_SCRIPT" <<'EOF'
#!/bin/bash
# Initialize Terraform for environment

cd "$(dirname "$0")"

echo "Initializing Terraform..."
terraform init

echo "Terraform initialized successfully!"
echo ""
echo "Next steps:"
echo "1. Review and modify terraform.tfvars as needed"
echo "2. Run: terraform plan"
echo "3. Run: terraform apply"
echo ""
echo "After apply, get the configuration with:"
echo "  terraform output nextauth_configuration"
echo "  terraform output -raw client_secret"
EOF

chmod +x "$INIT_SCRIPT"

print_message $GREEN "=== Bootstrap Complete! ==="
print_message $GREEN ""
print_message $GREEN "Files created:"
print_message $GREEN "- Backend configuration: $BACKEND_CONFIG_FILE"
print_message $GREEN "- Variables file: $TFVARS_FILE"
print_message $GREEN "- Initialization script: $INIT_SCRIPT"
print_message $GREEN ""
print_message $YELLOW "Next steps:"
print_message $YELLOW "1. cd ${ENV_DIR}"
print_message $YELLOW "2. ./init.sh"
print_message $YELLOW "3. terraform plan"
print_message $YELLOW "4. terraform apply"
print_message $YELLOW ""
print_message $YELLOW "After apply, configure your app with:"
print_message $YELLOW "  terraform output nextauth_configuration" 