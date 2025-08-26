# Installation Guide

This guide will walk you through installing and setting up the Litepie Integration package.

## Requirements

- PHP 8.2 or higher
- Laravel 11.0 or higher
- MySQL 8.0+ or PostgreSQL 13+
- Laravel Sanctum or Passport for API authentication

## Installation Steps

### 1. Install via Composer

```bash
composer require litepie/integration
```

### 2. Publish Configuration and Migrations

```bash
# Publish configuration file
php artisan vendor:publish --provider="Litepie\Integration\IntegrationServiceProvider" --tag="integration-config"

# Publish migrations
php artisan vendor:publish --provider="Litepie\Integration\IntegrationServiceProvider" --tag="integration-migrations"
```

### 3. Configure the Package

Edit the published configuration file at `config/integration.php`:

```php
<?php

return [
    // Database table name
    'table_names' => [
        'integrations' => 'integrations',
    ],

    // Authentication guards
    'guard' => 'web',
    'api_guard' => 'sanctum', // or 'passport'

    // Middleware
    'middleware' => [
        'web' => ['web', 'auth'],
        'api' => ['api', 'auth:sanctum'], // or 'auth:passport'
    ],

    // Client ID/Secret lengths
    'client' => [
        'id_length' => 40,
        'secret_length' => 80,
    ],

    // Default status for new integrations
    'default_status' => 'active',

    // Pagination settings
    'pagination' => [
        'per_page' => 15,
        'max_per_page' => 100,
    ],
];
```

### 4. Run Migrations

```bash
php artisan migrate
```

### 5. Setup Authentication (if not already configured)

#### For Laravel Sanctum:

```bash
# Install Sanctum
composer require laravel/sanctum

# Publish Sanctum config
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Run Sanctum migrations
php artisan migrate
```

Add Sanctum middleware to `app/Http/Kernel.php`:

```php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

#### For Laravel Passport:

```bash
# Install Passport
composer require laravel/passport

# Run Passport migrations
php artisan migrate

# Install Passport
php artisan passport:install
```

### 6. Register Policies (Optional)

If you want to customize authorization, add to `AuthServiceProvider`:

```php
use Litepie\Integration\Models\Integration;
use Litepie\Integration\Policies\IntegrationPolicy;

protected $policies = [
    Integration::class => IntegrationPolicy::class,
];
```

### 7. Add Event Listeners (Optional)

If you want to listen to integration events, add to `EventServiceProvider`:

```php
use Litepie\Integration\Events\IntegrationCreated;
use Litepie\Integration\Events\IntegrationUpdated;
use Litepie\Integration\Events\IntegrationDeleted;

protected $listen = [
    IntegrationCreated::class => [
        // Your listeners here
    ],
    IntegrationUpdated::class => [
        // Your listeners here
    ],
    IntegrationDeleted::class => [
        // Your listeners here
    ],
];
```

## Verification

### 1. Check Routes

```bash
php artisan route:list | grep integration
```

You should see routes like:
- `GET api/integrations`
- `POST api/integrations`
- `GET api/integrations/{integration}`
- etc.

### 2. Test API Endpoints

Create a test user and get an access token, then test the API:

```bash
# Get all integrations (should return empty array initially)
curl -X GET http://your-app.com/api/integrations \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json"
```

### 3. Create Test Integration

```bash
curl -X POST http://your-app.com/api/integrations \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Test Integration",
    "description": "A test integration",
    "redirect_uris": ["https://example.com/callback"]
  }'
```

### 4. Run Tests

```bash
cd vendor/litepie/integration
composer install
vendor/bin/phpunit
```

## Troubleshooting

### Common Issues

1. **Migration Errors**: Ensure your database connection is properly configured
2. **Authentication Errors**: Make sure Sanctum/Passport is properly installed and configured
3. **Route Not Found**: Check that the service provider is registered
4. **Permission Denied**: Verify that policies are working correctly

### Debug Mode

Enable Laravel's debug mode to see detailed error messages:

```env
APP_DEBUG=true
```

### Logs

Check Laravel logs for detailed error information:

```bash
tail -f storage/logs/laravel.log
```

## Next Steps

1. Read the [Examples](EXAMPLES.md) documentation
2. Customize the configuration as needed
3. Set up event listeners for your application
4. Implement OAuth flows using the provided traits
5. Add rate limiting and security measures

## Support

For issues and questions:
- Check the [GitHub Issues](https://github.com/litepie/integration/issues)
- Read the documentation
- Join our community discussions
