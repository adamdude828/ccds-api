<?php

return [
    'provider' => env('CDN_PROVIDER', 'frontdoor'),
    'base_url' => env('CDN_BASE_URL'),

    // Azure Front Door (Standard/Premium) configuration
    'frontdoor' => [
        'subscription_id' => env('AZURE_SUBSCRIPTION_ID'),
        'resource_group'  => env('AZURE_RESOURCE_GROUP'),
        'profile_name'    => env('AZURE_FD_PROFILE'),
        'endpoint_name'   => env('AZURE_FD_ENDPOINT'),

        // Azure AD App credentials (Service Principal)
        'tenant_id'       => env('AZURE_TENANT_ID'),
        'client_id'       => env('AZURE_CLIENT_ID'),
        'client_secret'   => env('AZURE_CLIENT_SECRET'),
    ],
];