# Terraform Environments

This directory contains environment-specific configurations that use the shared modules from `../modules/`.

## Structure

Each environment directory contains:
- `main.tf` - Module declarations with environment-specific values
- `variables.tf` - Variable declarations with environment-appropriate defaults
- `outputs.tf` - Output values from the modules
- `terraform.tfvars` - Environment-specific variable values (not in git)
- `backend.tf` - Remote state configuration
- `providers.tf` - Provider configurations

## How It Works

Instead of duplicating resource definitions, each environment:
1. References the shared modules from `../modules/`
2. Passes environment-specific values to the modules
3. All environments automatically get updates when modules change

## Usage

```bash
# Navigate to your environment
cd terraform/environments/dev

# Initialize Terraform
terraform init

# Plan changes
terraform plan

# Apply changes
terraform apply
```

## Creating a New Environment

1. Copy an existing environment directory:
```bash
cp -r dev staging
```

2. Update the `terraform.tfvars` with environment-specific values:
```hcl
environment = "staging"
app_url = "https://staging.example.com"
redirect_uris = ["https://staging.example.com/api/auth/callback/azure-ad"]
```

3. Update the backend configuration in `backend.tf`

4. Initialize and apply:
```bash
terraform init
terraform apply
```

## Benefits

- **Single Source of Truth**: All environments use the same module code
- **No Manual Syncing**: Changes to modules automatically apply to all environments
- **Environment Isolation**: Each environment has its own state file
- **Easy Comparison**: Can easily diff environment configurations 