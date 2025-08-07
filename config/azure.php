<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Target Azure AD Group
    |--------------------------------------------------------------------------
    |
    | This is the ID of the Azure AD group that users must be a member of
    | to access certain features of the application.
    |
    */
    'target_group_id' => env('AZURE_TARGET_GROUP_ID', '7ecebecb-a3eb-463d-82fb-c4ca4c44a3cf'),
]; 