resource "azurerm_resource_group" "rg" {
  location = var.resource_group_location
  name     = "rg-pub-ccds-php-tf-m-qa-w"
}

# Create virtual network
resource "azurerm_virtual_network" "vnetName" {
  name                = var.vnetName
  address_space       = ["10.0.0.0/16"]
  location            = azurerm_resource_group.rg.location
  resource_group_name = azurerm_resource_group.rg.name
}

resource "azurerm_subnet" "subnetName1" {
  name = var.subnetName1
  resource_group_name = azurerm_resource_group.rg.name
  virtual_network_name = azurerm_virtual_network.vnetName.name
  address_prefixes = ["${var.subnetPrefix1}"]
  service_endpoints = ["Microsoft.Sql","Microsoft.KeyVault"]
}

resource "azurerm_subnet" "subnetName2" {
  name = var.subnetName2
  resource_group_name = azurerm_resource_group.rg.name
  virtual_network_name = azurerm_virtual_network.vnetName.name
  address_prefixes = ["${var.subnetPrefix2}"]
}

resource "azurerm_subnet" "subnetName3" {
  name = var.subnetName3
  resource_group_name = azurerm_resource_group.rg.name
  virtual_network_name = azurerm_virtual_network.vnetName.name
  address_prefixes = ["${var.subnetPrefix3}"]
}

resource "azurerm_public_ip" "agwPubIpName" {
  name = var.agwPubIpName
  resource_group_name = azurerm_resource_group.rg.name
  location = azurerm_resource_group.rg.location
  allocation_method = "Static"
  sku = "Standard"
  domain_name_label = var.agwName
}

resource "azurerm_public_ip" "bastionPubIpName" {
  name = var.bastionPubIpName
  resource_group_name = azurerm_resource_group.rg.name
  location = azurerm_resource_group.rg.location
  allocation_method = "Static"
  sku = "Standard"
  idle_timeout_in_minutes = 4
}

resource "azurerm_public_ip" "vmPubIp1Name" {
  name = var.vmPubIp1Name
  resource_group_name = azurerm_resource_group.rg.name
  location = azurerm_resource_group.rg.location
  allocation_method = "Static"
  sku = "Standard"
  idle_timeout_in_minutes = 4
}

resource "azurerm_public_ip" "vmPubIp2Name" {
  name = var.vmPubIp2Name
  resource_group_name = azurerm_resource_group.rg.name
  location = azurerm_resource_group.rg.location
  allocation_method = "Static"
  sku = "Standard"
  idle_timeout_in_minutes = 4
}

resource "azurerm_application_gateway" "agwName" {
  name = var.agwName
  resource_group_name = azurerm_resource_group.rg.name
  location = azurerm_resource_group.rg.location
  identity {
    type = "UserAssigned"
    identity_ids = ["/subscriptions/2f8df4de-39b4-475c-ae78-1cefb4f2c573/resourcegroups/rg-mis-kv-u-global-w/providers/Microsoft.ManagedIdentity/userAssignedIdentities/id-ccds-kv-global-w"]
  }
  sku {
    name = "Standard_v2"
    tier = "Standard_v2"
    capacity = 2
  }
  gateway_ip_configuration {
    name = "appGatewayIpConfig"
    subnet_id = azurerm_subnet.subnetName2.id
  }
  frontend_port {
    name = "port_80"
    port = 80
  }
  frontend_port {
    name = "port_443"
    port = 443
  }
  frontend_ip_configuration {
    name = "appGatewayFrontentIP"
    public_ip_address_id = azurerm_public_ip.agwPubIpName.id
  }
  backend_address_pool {
    name = "pool-ccds-php-webservers"
  }
  backend_http_settings {
    name = "HTTP-Setting"
    port = 80
    cookie_based_affinity = "Enabled"
    protocol = "Http"
    request_timeout = 20
    pick_host_name_from_backend_address = false
    affinity_cookie_name = "ApplicationGatewayAffinity"
  }
  ssl_certificate {
    name = var.agwCertName
    key_vault_secret_id = var.sslCert
  }
  http_listener {
    name = "HTTP-Listener"
    frontend_ip_configuration_name = "appGatewayFrontentIP"
    frontend_port_name = "port_80"
    protocol = "Http"
    require_sni = false
  }
  http_listener {
    name = "HTTPS-Listener"
    frontend_ip_configuration_name = "appGatewayFrontentIP"
    frontend_port_name = "port_443"
    protocol = "Https"
    require_sni = false
    ssl_certificate_name = var.agwCertName
  }
  request_routing_rule {
    name = "HTTP-Rule"
    rule_type = "Basic"
    http_listener_name = "HTTP-Listener"
    redirect_configuration_name = "HTTP-Rule"
  }
  request_routing_rule {
    name = "HTTPS-Rule"
    rule_type = "Basic"
    http_listener_name = "HTTPS-Listener"
    backend_address_pool_name = "pool-ccds-php-webservers"
    backend_http_settings_name = "HTTP-Setting"
    rewrite_rule_set_name = "CorsRewrite"
  }
  rewrite_rule_set {
    name = "CorsRewrite"
    rewrite_rule {
      name = "Enable_Cors"
      rule_sequence = 100
      response_header_configuration {
        header_name = "Access-Control-Allow-Origin"
        header_value = "*"
      }
    }
  }
  redirect_configuration {
    name = "HTTP-Rule"
    redirect_type = "Permanent"
    target_listener_name = "HTTPS-Listener"
    include_path = true
    include_query_string = true
  }
}
resource "azurerm_redis_cache" "redis" {
  name = var.redisName
  resource_group_name = azurerm_resource_group.rg.name
  location = azurerm_resource_group.rg.location
  capacity = 1
  family = "C"
  sku_name = "Standard"
  enable_non_ssl_port = false
  minimum_tls_version = "1.2"
  public_network_access_enabled = false
  depends_on = [azurerm_application_gateway.agwName]
}
resource "azurerm_network_security_group" "nsg" {
  name = var.networkSecurityGroup
  resource_group_name = azurerm_resource_group.rg.name
  location = azurerm_resource_group.rg.location
  security_rule {
    name = "nsgRule1"
    protocol = "Tcp"
    description = "description"
    source_port_range = "*"
    destination_port_range = "*"
    source_address_prefix = "*"
    destination_address_prefix = "*"
    access = "Allow"
    priority = 100
    direction = "Inbound"
  }
}
resource "azurerm_private_dns_zone" "privateDnsZone" {
  name = var.redisPrivateLink
  depends_on = [
    azurerm_virtual_network.vnetName
  ]
  resource_group_name = azurerm_resource_group.rg.name
}
resource "azurerm_private_dns_zone_virtual_network_link" "virtualNetworkLink" {
  name = "redis-vnet-link"
  resource_group_name = azurerm_resource_group.rg.name
  private_dns_zone_name = azurerm_private_dns_zone.privateDnsZone.name
  virtual_network_id = azurerm_virtual_network.vnetName.id
}
resource "azurerm_private_dns_a_record" "aRecord" {
  name = var.redisName
  zone_name = azurerm_private_dns_zone.privateDnsZone.name
  resource_group_name = azurerm_resource_group.rg.name
  ttl = 300
  records = [ var.redisPrivIp ]
}
resource "azurerm_private_endpoint" "privateEndpoint" {
  name = var.redisPrivateEndpointName
  resource_group_name = azurerm_resource_group.rg.name
  location = azurerm_resource_group.rg.location
  subnet_id = azurerm_subnet.subnetName1.id
  private_service_connection {
    name = "redis-connection"
    is_manual_connection = false
    private_connection_resource_id = azurerm_redis_cache.redis.id
    subresource_names = [ "redisCache" ]
  }
}
resource "azurerm_mysql_server" "mysqlServer" {
  name = var.mysqlServerName
  resource_group_name = azurerm_resource_group.rg.name
  location = azurerm_resource_group.rg.location
  administrator_login = var.mysqlAdmin
  administrator_login_password = var.mysqlAdminPassword
  sku_name = "GP_Gen5_2"
  depends_on = [
    azurerm_application_gateway.agwName
  ]
  version = "5.7"
  ssl_enforcement_enabled = false
  ssl_minimal_tls_version_enforced = "TLSEnforcementDisabled"
  public_network_access_enabled = true
  infrastructure_encryption_enabled = false
  auto_grow_enabled = true
  backup_retention_days = 7
  geo_redundant_backup_enabled = false
  storage_mb = 102400
}
resource "azurerm_mysql_database" "mysqlDatabase" {
  name = "vcp"
  resource_group_name = azurerm_resource_group.rg.name
  server_name = azurerm_mysql_server.mysqlServer.name
  charset = "latin1"
  collation = "latin1_swedish_ci"
}
resource "azurerm_mysql_virtual_network_rule" "mysqlNetworkRule" {
  name = "backend-servers"
  resource_group_name = azurerm_resource_group.rg.name
  server_name = azurerm_mysql_server.mysqlServer.name
  subnet_id = azurerm_subnet.subnetName1.id
}
resource "azurerm_storage_account" "storageAccount" {
  name = var.mediaServicesStorageAccount
  resource_group_name = azurerm_resource_group.rg.name
  location = azurerm_resource_group.rg.location
  account_tier = "Standard"
  account_replication_type = "LRS"
  account_kind = "StorageV2"
  network_rules {
    default_action = "Allow"
    bypass = ["AzureServices"]
  }
  enable_https_traffic_only = false
  access_tier = "Hot"
  blob_properties {
    cors_rule {
      allowed_origins = [ "*" ]
      allowed_headers = [ "*" ]
      allowed_methods = [ 
        "DELETE",
        "GET",
        "HEAD",
        "MERGE",
        "POST",
        "OPTIONS",
        "PUT"
        ]
      max_age_in_seconds = 3600
      exposed_headers = [ "*" ]
    }
  }
}
resource "azurerm_storage_container" "container" {
  name = "poster"
  storage_account_name = azurerm_storage_account.storageAccount.name
  container_access_type = "private"
}
resource "azurerm_media_services_account" "mediaService" {
  name = var.mediaServiceName
  resource_group_name = azurerm_resource_group.rg.name
  location = azurerm_resource_group.rg.location
  storage_account {
    id = azurerm_storage_account.storageAccount.id
    is_primary = true
  }
}
resource "azurerm_media_transform" "transform" {
  name = var.mediaServicesTransform
  resource_group_name = azurerm_resource_group.rg.name
  media_services_account_name = azurerm_media_services_account.mediaService.name
  output {
    relative_priority = "Normal"
    on_error_action = "StopProcessingJob"
    builtin_preset {
      preset_name = "H264MultipleBitrate720p"
    }
  }
}
resource "azurerm_network_interface" "networkInterfaceName1" {
  name = var.networkInterfaceName1
  resource_group_name = azurerm_resource_group.rg.name
  location = azurerm_resource_group.rg.location
  ip_configuration {
    name = "ipconfig1"
    subnet_id = azurerm_subnet.subnetName1.id
    private_ip_address_allocation = "Dynamic"
    public_ip_address_id = azurerm_public_ip.vmPubIp1Name.id
  }
  enable_ip_forwarding = false
  enable_accelerated_networking = false

}
resource "azurerm_network_interface_application_gateway_backend_address_pool_association" "backendPoolAssosiation1" {
  network_interface_id = azurerm_network_interface.networkInterfaceName1.id
  ip_configuration_name = "ipconfig1"
  backend_address_pool_id = tolist(azurerm_application_gateway.agwName.backend_address_pool).0.id
}
resource "azurerm_network_interface_security_group_association" "securityGroupAssociation1" {
  network_interface_id = azurerm_network_interface.networkInterfaceName1.id
  network_security_group_id = azurerm_network_security_group.nsg.id
}
resource "azurerm_network_interface" "networkInterfaceName2" {
  name = var.networkInterfaceName2
  resource_group_name = azurerm_resource_group.rg.name
  location = azurerm_resource_group.rg.location
  ip_configuration {
    name = "ipconfig1"
    subnet_id = azurerm_subnet.subnetName1.id
    private_ip_address_allocation = "Dynamic"
    public_ip_address_id = azurerm_public_ip.vmPubIp2Name.id
  }
  enable_ip_forwarding = false
  enable_accelerated_networking = false
  
}
resource "azurerm_network_interface_application_gateway_backend_address_pool_association" "backendPoolAssosiation2" {
  network_interface_id = azurerm_network_interface.networkInterfaceName2.id
  ip_configuration_name = "ipconfig1"
  backend_address_pool_id = tolist(azurerm_application_gateway.agwName.backend_address_pool).0.id
}
resource "azurerm_network_interface_security_group_association" "securityGroupAssociation2" {
  network_interface_id = azurerm_network_interface.networkInterfaceName2.id
  network_security_group_id = azurerm_network_security_group.nsg.id
}
resource "azurerm_virtual_machine" "vmName1" {
  name = var.vmName1
  resource_group_name = azurerm_resource_group.rg.name
  location = azurerm_resource_group.rg.location
  network_interface_ids = [azurerm_network_interface.networkInterfaceName1.id]
  vm_size = "Standard_DS1_v2"
  storage_image_reference {
    publisher = "Canonical"
    offer = "UbuntuServer"
    sku = "18.04-LTS"
    version = "latest"
  }
  storage_os_disk {
    name = "${var.vmName1}_disk1"
    caching = "ReadWrite"
    create_option = "FromImage"
    managed_disk_type = "Premium_LRS"
    os_type = "Linux"
  }
  os_profile {
    computer_name = var.vmName1
    admin_username = "ccds-admin"
    admin_password = var.vmAdminPassword
  }
  os_profile_linux_config {
    disable_password_authentication = false
    ssh_keys {
      path = "/home/ccds-admin/.ssh/authorized_keys"
      key_data = var.sshKeyData
    }
  }
}
resource "azurerm_virtual_machine_extension" "chefExtension1" {
  name = var.chefExtension
  virtual_machine_id = azurerm_virtual_machine.vmName1.id
  publisher = "Chef.Bootstrap.WindowsAzure"
  type = "LinuxChefClient"
  type_handler_version = "1210.14"
  auto_upgrade_minor_version = false
  settings = <<SETTINGS
   { 
    "bootstrap_options": {
        "chef_server_url": "${var.chefUrl}",
        "environment": "${var.chefEnv}",
        "validation_client_name": "${var.validationClientName}",
        "chef_node_name": "${azurerm_virtual_machine.vmName1.name}"
    },
    "runlist": "${var.runlist}",
    "CHEF_LICENSE": "accept-silent",
    "hints": {
        "public_ip": "${azurerm_public_ip.vmPubIp1Name.id}"
    }
  }
  SETTINGS
  protected_settings = <<SETTINGS
  {
    "validation_key": "${var.validationKey}"
  }
  SETTINGS
}
resource "azurerm_virtual_machine" "vmName2" {
  name = var.vmName2
  resource_group_name = azurerm_resource_group.rg.name
  location = azurerm_resource_group.rg.location
  network_interface_ids = [azurerm_network_interface.networkInterfaceName2.id]
  vm_size = "Standard_DS1_v2"
  storage_image_reference {
    publisher = "Canonical"
    offer = "UbuntuServer"
    sku = "18.04-LTS"
    version = "latest"
  }
  storage_os_disk {
    name = "${var.vmName2}_disk1"
    caching = "ReadWrite"
    create_option = "FromImage"
    managed_disk_type = "Premium_LRS"
    os_type = "Linux"
  }
  os_profile {
    computer_name = var.vmName2
    admin_username = "ccds-admin"
    admin_password = var.vmAdminPassword
  }
  os_profile_linux_config {
    disable_password_authentication = false
    ssh_keys {
      path = "/home/ccds-admin/.ssh/authorized_keys"
      key_data = var.sshKeyData
    }
  }
}
resource "azurerm_virtual_machine_extension" "chefExtension2" {
  name = var.chefExtension
  virtual_machine_id = azurerm_virtual_machine.vmName2.id
  publisher = "Chef.Bootstrap.WindowsAzure"
  type = "LinuxChefClient"
  type_handler_version = "1210.14"
  auto_upgrade_minor_version = false
  settings = <<SETTINGS
  {
    "bootstrap_options": {
        "chef_server_url": "${var.chefUrl}",
        "environment": "${var.chefEnv}",
        "validation_client_name": "${var.validationClientName}",
        "chef_node_name": "${azurerm_virtual_machine.vmName2.name}"
    },
    "runlist": "${var.runlist}",
    "CHEF_LICENSE": "accept-silent",
    "hints": {
        "public_ip": "${azurerm_public_ip.vmPubIp2Name.id}"
    }
  }
  SETTINGS
  protected_settings = <<SETTINGS
  {
    "validation_key": "${var.validationKey}"
  }
  SETTINGS
}
resource "azurerm_bastion_host" "bastion" {
  name = var.bastionName
  resource_group_name = azurerm_resource_group.rg.name
  location = azurerm_resource_group.rg.location
  ip_configuration {
    name = "IpConf"
    subnet_id = azurerm_subnet.subnetName3.id
    public_ip_address_id = azurerm_public_ip.bastionPubIpName.id
  }
}

# Azure Front Door Standard Profile
resource "azurerm_cdn_frontdoor_profile" "frontdoor" {
  name                = var.frontdoor_profile_name
  resource_group_name = azurerm_resource_group.rg.name
  sku_name           = "Standard_AzureFrontDoor"

  tags = {
    Environment = "qa"
    Project     = "ccds"
  }
}

# Origin Group for Application Gateway
resource "azurerm_cdn_frontdoor_origin_group" "agw_origin_group" {
  name                     = var.frontdoor_origin_group_name
  cdn_frontdoor_profile_id = azurerm_cdn_frontdoor_profile.frontdoor.id
  session_affinity_enabled = true

  load_balancing {
    sample_size                 = 4
    successful_samples_required = 3
  }

  health_probe {
    path                = "/"
    request_type        = "HEAD"
    protocol            = "Https"
    interval_in_seconds = 100
  }
}

# Origin pointing to Application Gateway
resource "azurerm_cdn_frontdoor_origin" "agw_origin" {
  name                          = var.frontdoor_origin_name
  cdn_frontdoor_origin_group_id = azurerm_cdn_frontdoor_origin_group.agw_origin_group.id

  enabled                        = true
  host_name                     = azurerm_public_ip.agwPubIpName.fqdn
  http_port                     = 80
  https_port                    = 443
  origin_host_header            = azurerm_public_ip.agwPubIpName.fqdn
  priority                      = 1
  weight                        = 1000
  certificate_name_check_enabled = true
}

# Front Door Endpoint
resource "azurerm_cdn_frontdoor_endpoint" "endpoint" {
  name                     = var.frontdoor_endpoint_name
  cdn_frontdoor_profile_id = azurerm_cdn_frontdoor_profile.frontdoor.id
}

# Route for the endpoint
resource "azurerm_cdn_frontdoor_route" "route" {
  name                          = var.frontdoor_route_name
  cdn_frontdoor_endpoint_id     = azurerm_cdn_frontdoor_endpoint.endpoint.id
  cdn_frontdoor_origin_group_id = azurerm_cdn_frontdoor_origin_group.agw_origin_group.id
  cdn_frontdoor_origin_ids      = [azurerm_cdn_frontdoor_origin.agw_origin.id]

  supported_protocols    = ["Http", "Https"]
  patterns_to_match     = ["/*"]
  forwarding_protocol   = "HttpsOnly"
  link_to_default_domain = true
  https_redirect_enabled = true

  cache {
    query_string_caching_behavior = "IgnoreQueryString"
    query_strings                = []
    compression_enabled          = true
    content_types_to_compress = [
      "application/eot",
      "application/font",
      "application/font-sfnt",
      "application/javascript",
      "application/json",
      "application/opentype",
      "application/otf",
      "application/pkcs7-mime",
      "application/truetype",
      "application/ttf",
      "application/vnd.ms-fontobject",
      "application/xhtml+xml",
      "application/xml",
      "application/xml+rss",
      "application/x-font-opentype",
      "application/x-font-truetype",
      "application/x-font-ttf",
      "application/x-httpd-cgi",
      "application/x-javascript",
      "application/x-mpegurl",
      "application/x-opentype",
      "application/x-otf",
      "application/x-perl",
      "application/x-ttf",
      "font/eot",
      "font/ttf",
      "font/otf",
      "font/opentype",
      "image/svg+xml",
      "text/css",
      "text/csv",
      "text/html",
      "text/javascript",
      "text/js",
      "text/plain",
      "text/richtext",
      "text/tab-separated-values",
      "text/xml",
      "text/x-script",
      "text/x-component",
      "text/x-java-source"
    ]
  }
}