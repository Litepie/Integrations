# Litepie Integration Package

A production-ready Laravel package for managing API integrations similar to Facebook/Google applications. This package provides a complete integration management system with multi-tenant support.

## Requirements

- PHP 8.2 or higher
- Laravel 12.x
- MySQL 8.0+ / PostgreSQL 13+ / SQLite 3.35+

## Features

- Create and manage API integrations (OAuth clients)
- Auto-generated Client ID and Client Secret
- Multiple redirect URIs support
- Status management (active/inactive)
- Multi-tenant ready
- Laravel Passport/Sanctum authentication support
- Comprehensive API endpoints
- Policy-based authorization
- PSR-4 compliant
- Full test coverage

## Installation

Install the package via Composer:

```bash
composer require litepie/integration
```

Publish the configuration and migrations:

```bash
php artisan vendor:publish --provider="Litepie\Integration\IntegrationServiceProvider"
```

Run the migrations:

```bash
php artisan migrate
```

## Configuration

The package publishes a configuration file to `config/integration.php` where you can customize:

- Database table names
- Middleware settings
- Authentication guards
- Default settings

## Usage

### Creating an Integration

```php
use Litepie\Integration\Models\Integration;

$integration = Integration::create([
    'name' => 'My API Integration',
    'description' => 'Integration for my application',
    'redirect_uris' => ['https://myapp.com/callback'],
    'status' => 'active',
    'user_id' => auth()->id(),
]);
```

### API Endpoints

The package provides RESTful API endpoints:

- `GET /api/integrations` - List integrations
- `POST /api/integrations` - Create integration
- `GET /api/integrations/{id}` - Show integration
- `PUT /api/integrations/{id}` - Update integration
- `DELETE /api/integrations/{id}` - Delete integration

### Multi-tenant Support

The package automatically handles multi-tenancy by associating integrations with the authenticated user.

## Testing

Run the test suite:

```bash
vendor/bin/phpunit
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
