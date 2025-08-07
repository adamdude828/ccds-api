variable "resource_group_location" {
  default     = "westus"
  description = "Location of the resource group."
}
variable "resource_group_name_prefix" {
  default     = "rg"
  description = "Prefix of the resource group name that's combined with a random ID so name is unique in your Azure subscription."
}
variable "redisName" {
  type = string
}
variable "networkSecurityGroup" {
  type = string
}
variable "redisPrivateLink" {
  type = string
}
variable "vnetName" {
  type = string
}
variable "subnetName1" {
  type = string
}
variable "subnetName2" {
  type = string
}
variable "subnetName3" {
  type = string
}
variable "subnetPrefix1" {
  type = string
}
variable "subnetPrefix2" {
  type = string
}
variable "subnetPrefix3" {
  type = string
}
variable "redisPrivIp" {
  type = string
}
variable "agwPubIpName" {
  type = string
}
variable "bastionPubIpName" {
  type = string
}
variable "vmPubIp1Name" {
  type = string
}
variable "vmPubIp2Name" {
  type = string
}
variable "agwName" {
  type = string
}
variable "agwFqdn" {
  type = string
}
variable "redisPrivateEndpointName" {
  type = string
}
variable "redisPveFqdn" {
  type = string
}
variable "redisPveIp" {
  type = string
}
variable "agwCertName" {
  type = string
}
variable "mysqlServerName" {
  type = string
}
variable "mediaServicesStorageAccount" {
  type = string
}
variable "mediaServiceName" {
  type = string
}
variable "bastionDnsName" {
  type = string
}
variable "bastionName" {
  type = string
}
variable "networkInterfaceName1" {
  type = string
}
variable "networkInterfaceName2" {
  type = string
}
variable "vmPrivateIp1" {
  type = string
}
variable "vmPrivateIp2" {
  type = string
}
variable "vmName1" {
  type = string
}
variable "vmName2" {
  type = string
}
variable "chefExtension" {
  type = string
}
variable "runlist" {
  type = string
}
variable "chefUrl" {
  type = string
}
variable "validationClientName" {
  type = string
}
variable "chefEnv" {
  type = string
}
variable "mediaServicesTransform" {
  type = string
}
variable "sslCert" {
  type = string
}
variable "mysqlAdmin" {
  type = string
  sensitive = true
}
variable "mysqlAdminPassword" {
  type = string
  sensitive = true
}
variable "vmAdminPassword" {
  type = string
  sensitive = true
}
variable "sshKeyData" {
  type = string
  sensitive = true
}
variable "validationKey" {
  type = string
}

# Azure Front Door variables
variable "frontdoor_profile_name" {
  type        = string
  description = "Name of the Azure Front Door profile"
}

variable "frontdoor_endpoint_name" {
  type        = string
  description = "Name of the Azure Front Door endpoint"
}

variable "frontdoor_origin_group_name" {
  type        = string
  description = "Name of the Azure Front Door origin group"
}

variable "frontdoor_origin_name" {
  type        = string
  description = "Name of the Azure Front Door origin"
}

variable "frontdoor_route_name" {
  type        = string
  description = "Name of the Azure Front Door route"
}