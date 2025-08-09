<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Target Azure AD Group
    |--------------------------------------------------------------------------
    */
    'target_group_id' => env('AZURE_TARGET_GROUP_ID', '7ecebecb-a3eb-463d-82fb-c4ca4c44a3cf'),

    /*
    |--------------------------------------------------------------------------
    | Azure Storage Configuration (used by SAS helpers and tests)
    |--------------------------------------------------------------------------
    */
    'storage' => [
        // Account identifiers
        'account'       => env('AZURE_STORAGE_ACCOUNT'),
        'account_key'   => env('AZURE_STORAGE_ACCOUNT_KEY'),
        'connection'    => env('AZURE_STORAGE_CONNECTION'),

        // Legacy accessors in Azure.php (kept for compatibility)
        'name'          => env('AZURE_STORAGE_ACCOUNT'),
        'key'           => env('AZURE_STORAGE_ACCOUNT_KEY'),
        'url'           => env('AZURE_STORAGE_URL'),

        // Containers
        'container'     => env('AZURE_STORAGE_CONTAINER', 'videos'),
        'poster'        => env('AZURE_STORAGE_POSTER_CONTAINER', 'posters'),
        'documents'     => env('AZURE_STORAGE_DOCUMENTS_CONTAINER', 'documents'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Azure Media (kept as-is if used elsewhere)
    |--------------------------------------------------------------------------
    */
    'media' => [
        'name'                => env('AZURE_MEDIA_ACCOUNT_NAME'),
        'url'                 => env('AZURE_MEDIA_PUBLIC_BASE_URL'),
        'transform'           => env('AZURE_MEDIA_TRANSFORM_NAME'),
        'poster-transform'    => env('AZURE_MEDIA_POSTER_TRANSFORM_NAME'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Azure AD App (used by App\Classes\Azure)
    |--------------------------------------------------------------------------
    */
    'ad' => [
        'appId'         => env('AZURE_CLIENT_ID'),
        'secret'        => env('AZURE_CLIENT_SECRET'),
        'domain'        => env('AZURE_TENANT_ID'),
        'subscription_id'=> env('AZURE_SUBSCRIPTION_ID'),
        'group'         => env('AZURE_RESOURCE_GROUP'),
    ],
]; 