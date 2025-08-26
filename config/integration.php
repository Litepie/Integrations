<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Integration Table Names
    |--------------------------------------------------------------------------
    |
    | Here you can configure the table names used by the integration package.
    |
    */
    'table_names' => [
        'integrations' => 'integrations',
        'integration_secrets' => 'integration_secrets',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guard
    |--------------------------------------------------------------------------
    |
    | Here you can specify which authentication guard should be used
    | by the integration middleware.
    |
    */
    'guard' => 'web',

    /*
    |--------------------------------------------------------------------------
    | API Guard
    |--------------------------------------------------------------------------
    |
    | Here you can specify which authentication guard should be used
    | for API endpoints. This can be 'sanctum' or 'passport'.
    |
    */
    'api_guard' => 'sanctum',

    /*
    |--------------------------------------------------------------------------
    | Route Middleware
    |--------------------------------------------------------------------------
    |
    | Here you can specify which middleware should be applied to the
    | integration routes.
    |
    */
    'middleware' => [
        'web' => ['web', 'auth'],
        'api' => ['api', 'auth:sanctum'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | Here you can specify the route prefix for the integration routes.
    |
    */
    'route_prefix' => [
        'web' => 'integrations',
        'api' => 'api/integrations',
    ],

    /*
    |--------------------------------------------------------------------------
    | Client ID/Secret Generation
    |--------------------------------------------------------------------------
    |
    | Configuration for generating client IDs and secrets.
    |
    */
    'client' => [
        'id_length' => 40,
        'secret_length' => 80,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Status
    |--------------------------------------------------------------------------
    |
    | The default status for new integrations.
    |
    */
    'default_status' => 'active',

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    |
    | Default configuration values for new integrations.
    |
    */
    'defaults' => [
        'status' => 'active',
        'role' => 'user',
        'permissions' => [],
        'allowed_scopes' => [],
        'default_scopes' => [],
        'ip_whitelist' => [
            'require_whitelist' => false,
            'allowed_ips' => [],
            'blocked_ips' => [],
        ],
        'geo_restrictions' => [
            'allowed_countries' => [],
            'blocked_countries' => [],
            'allowed_regions' => [],
            'strict_mode' => false,
        ],
        'time_restrictions' => [
            'timezone' => 'UTC',
            'business_hours_only' => false,
        ],
        'rate_limits' => [
            'requests_per_minute' => 60,
            'requests_per_hour' => 1000,
            'requests_per_day' => 10000,
            'burst_limit' => 100,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Definitions
    |--------------------------------------------------------------------------
    |
    | Available roles and their descriptions.
    |
    */
    'roles' => [
        'admin' => 'Full access to all resources',
        'user' => 'Standard user access',
        'guest' => 'Limited read-only access',
        'service' => 'Service account access',
        'readonly' => 'Read-only access to all resources',
    ],

    /*
    |--------------------------------------------------------------------------
    | Permission Resources
    |--------------------------------------------------------------------------
    |
    | Available resources and their possible actions.
    |
    */
    'permissions' => [
        'users' => ['create', 'read', 'update', 'delete'],
        'posts' => ['create', 'read', 'update', 'delete'],
        'comments' => ['create', 'read', 'update', 'delete'],
        'analytics' => ['read'],
        'admin' => ['settings', 'users', 'system'],
        'files' => ['upload', 'download', 'delete'],
        'webhooks' => ['create', 'read', 'update', 'delete'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Available Scopes
    |--------------------------------------------------------------------------
    |
    | OAuth scopes available for integrations.
    |
    */
    'scopes' => [
        'read' => 'Read access to basic information',
        'write' => 'Write access to create/update resources',
        'delete' => 'Delete access to remove resources',
        'admin' => 'Administrative access',
        'user' => 'User profile access',
        'posts' => 'Posts management access',
        'analytics' => 'Analytics data access',
        'files' => 'File management access',
        'webhooks' => 'Webhook management access',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Default pagination settings for integration listings.
    |
    */
    'pagination' => [
        'per_page' => 15,
        'max_per_page' => 100,
    ],
];
