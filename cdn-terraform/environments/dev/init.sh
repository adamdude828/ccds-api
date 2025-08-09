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
