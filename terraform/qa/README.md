# Azure Front Door Migration

This Terraform configuration has been updated to include Azure Front Door Standard/Premium to replace deprecated Azure CDN services.

## Changes Made

### 1. Provider Update
- Updated azurerm provider from `=2.46.0` to `~>3.0` to support new Azure Front Door resources

### 2. Added Azure Front Door Resources

#### Front Door Profile
- `azurerm_cdn_frontdoor_profile`: Standard tier Azure Front Door profile
- SKU: `Standard_AzureFrontDoor`

#### Origin Configuration
- `azurerm_cdn_frontdoor_origin_group`: Origin group pointing to the Application Gateway
- Health probes configured for HTTPS on path `/`
- Session affinity enabled

#### Origin
- `azurerm_cdn_frontdoor_origin`: Origin pointing to the Application Gateway public IP
- HTTPS enabled with certificate name checking

#### Endpoint and Route
- `azurerm_cdn_frontdoor_endpoint`: Front Door endpoint
- `azurerm_cdn_frontdoor_route`: Route configuration with caching and compression

### 3. Configuration Details

#### Caching
- Query string caching behavior: Ignore query strings
- Compression enabled for common web content types
- Content types include JavaScript, CSS, HTML, JSON, fonts, and images

#### Security
- HTTPS redirect enabled
- HTTPS-only forwarding protocol
- Certificate name checking enabled

#### Variables Added
- `frontdoor_profile_name`: Name of the Front Door profile
- `frontdoor_endpoint_name`: Name of the Front Door endpoint  
- `frontdoor_origin_group_name`: Name of the origin group
- `frontdoor_origin_name`: Name of the origin
- `frontdoor_route_name`: Name of the route

### 4. Outputs Added
- `frontdoor_profile_id`: The ID of the Front Door profile
- `frontdoor_endpoint_hostname`: The hostname of the Front Door endpoint
- `frontdoor_endpoint_fqdn`: The full HTTPS URL of the Front Door endpoint

## Deployment

1. Review the new variables in `terraform.tfvars`
2. Run `terraform init` to download the updated provider
3. Run `terraform plan` to review the changes
4. Run `terraform apply` to deploy the Front Door configuration

## New Endpoint URL Format

The new Azure Front Door endpoint will have the format:
`<endpoint-name>-<hash>.z01.azurefd.net`

The exact URL will be available in the `frontdoor_endpoint_hostname` output after deployment.

## Migration Notes

- No existing CDN resources were found to replace
- The Front Door is configured to route traffic through the existing Application Gateway
- All traffic will be forced to HTTPS
- Caching and compression are optimized for web applications
- Health probes will monitor the Application Gateway availability

## Testing Checklist

After deployment, verify:
- [ ] Front Door endpoint is accessible
- [ ] HTTPS redirect is working
- [ ] Content is being cached properly
- [ ] Compression is working for supported file types  
- [ ] Health probes are passing
- [ ] SSL/TLS certificate is valid