# Litepie Integration Package Structure

## Overview

This is a comprehensive Laravel package that provides integration management functionality similar to Facebook/Google OAuth applications. The package includes all the components needed for a production-ready integration system.

## Package Structure

```
litepie/integrations/
├── .github/
│   └── workflows/
│       └── tests.yml                    # GitHub Actions CI/CD
├── config/
│   └── integration.php                  # Package configuration
├── database/
│   ├── factories/
│   │   └── IntegrationFactory.php       # Model factory for testing
│   ├── migrations/
│   │   └── 2024_01_01_000001_create_integrations_table.php
│   └── seeders/
│       └── IntegrationSeeder.php        # Database seeder
├── src/
│   ├── Console/
│   │   └── Commands/
│   │       └── ListIntegrationsCommand.php  # Artisan command
│   ├── Events/
│   │   ├── IntegrationCreated.php       # Event classes
│   │   ├── IntegrationUpdated.php
│   │   └── IntegrationDeleted.php
│   ├── Facades/
│   │   └── Integration.php              # Package facade
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── IntegrationController.php    # API controller
│   │   ├── Middleware/
│   │   │   └── IntegrationRateLimit.php     # Rate limiting middleware
│   │   ├── Requests/
│   │   │   ├── StoreIntegrationRequest.php  # Form requests
│   │   │   └── UpdateIntegrationRequest.php
│   │   └── Resources/
│   │       ├── IntegrationResource.php      # API resources
│   │       └── IntegrationCollection.php
│   ├── Listeners/
│   │   └── LogIntegrationActivity.php   # Event listener
│   ├── Models/
│   │   └── Integration.php              # Eloquent model
│   ├── Policies/
│   │   └── IntegrationPolicy.php        # Authorization policy
│   ├── Traits/
│   │   └── HandlesOAuth.php             # OAuth helper trait
│   ├── routes/
│   │   └── api.php                      # API routes
│   ├── IntegrationManager.php           # Main service class
│   └── IntegrationServiceProvider.php   # Service provider
├── tests/
│   ├── Feature/
│   │   └── IntegrationApiTest.php       # Feature tests
│   ├── Unit/
│   │   └── IntegrationModelTest.php     # Unit tests
│   └── TestCase.php                     # Base test case
├── .gitignore                           # Git ignore file
├── composer.json                        # Composer configuration
├── phpunit.xml                          # PHPUnit configuration
├── EXAMPLES.md                          # Usage examples
├── INSTALLATION.md                      # Installation guide
├── LICENSE.md                           # MIT license
└── README.md                            # Package documentation
```

## Key Features Implemented

### 1. **Core Functionality**
- ✅ Integration model with auto-generated client ID/secret
- ✅ Redirect URI management and validation
- ✅ Status management (active/inactive)
- ✅ Multi-tenant support (user-based)
- ✅ Soft deletes

### 2. **API Endpoints**
- ✅ RESTful API with full CRUD operations
- ✅ Authentication via Laravel Sanctum/Passport
- ✅ Policy-based authorization
- ✅ Pagination and search functionality
- ✅ Rate limiting middleware

### 3. **Security & Authorization**
- ✅ Multi-tenant isolation
- ✅ Client secret regeneration
- ✅ Secure credential storage
- ✅ OAuth flow helpers
- ✅ Request validation

### 4. **Developer Experience**
- ✅ Comprehensive test suite
- ✅ Factory for testing
- ✅ Console commands
- ✅ Event system
- ✅ Facade for easy access
- ✅ PSR-4 compliant

### 5. **Configuration & Extensibility**
- ✅ Publishable configuration
- ✅ Customizable table names
- ✅ Configurable middleware
- ✅ Event listeners
- ✅ Traits for OAuth functionality

### 6. **Documentation & CI/CD**
- ✅ Installation guide
- ✅ Usage examples
- ✅ GitHub Actions workflow
- ✅ PHPUnit configuration

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/integrations` | List user's integrations |
| POST | `/api/integrations` | Create new integration |
| GET | `/api/integrations/{id}` | Show specific integration |
| PUT | `/api/integrations/{id}` | Update integration |
| DELETE | `/api/integrations/{id}` | Delete integration |
| POST | `/api/integrations/{id}/activate` | Activate integration |
| POST | `/api/integrations/{id}/deactivate` | Deactivate integration |
| POST | `/api/integrations/{id}/regenerate-secret` | Regenerate client secret |

## Usage Examples

### Basic Usage
```php
use Litepie\Integration\Models\Integration;

$integration = Integration::create([
    'name' => 'My App Integration',
    'description' => 'OAuth integration for my app',
    'redirect_uris' => ['https://myapp.com/callback'],
    'user_id' => auth()->id(),
]);
```

### Using the Facade
```php
use Litepie\Integration\Facades\Integration;

$integration = Integration::findByClientId('client-id');
$isValid = Integration::validateCredentials('client-id', 'secret');
```

### OAuth Flow
```php
use Litepie\Integration\Traits\HandlesOAuth;

class OAuthController extends Controller
{
    use HandlesOAuth;
    
    public function authorize(Request $request)
    {
        $integration = Integration::where('client_id', $request->client_id)->first();
        $authUrl = $this->generateAuthorizationUrl($integration, $request->redirect_uri);
        return redirect($authUrl);
    }
}
```

## Testing

The package includes comprehensive tests:

```bash
# Run all tests
vendor/bin/phpunit

# Run specific test suites
vendor/bin/phpunit tests/Unit
vendor/bin/phpunit tests/Feature
```

## Console Commands

```bash
# List all integrations
php artisan integration:list

# Filter by user
php artisan integration:list --user=1

# Filter by status
php artisan integration:list --status=active
```

## Events

The package dispatches events for:
- `IntegrationCreated`
- `IntegrationUpdated`
- `IntegrationDeleted`

## Requirements Met

✅ **Laravel 12 Compatible**: Built for Laravel 11+ (forward compatible)
✅ **Integration Management**: Complete CRUD operations
✅ **Auto-generated Credentials**: Client ID & Secret generation
✅ **Redirect URI Support**: Multiple URIs with validation
✅ **Status Management**: Active/Inactive states
✅ **Multi-tenant Ready**: User-based isolation
✅ **Laravel Passport/Sanctum**: Authentication support
✅ **PSR-4 Standards**: Proper autoloading
✅ **Publishable Assets**: Config and migrations
✅ **Comprehensive Tests**: Unit and feature tests
✅ **Documentation**: Installation and usage guides

This package provides a production-ready foundation for managing API integrations in Laravel applications, with all the security, scalability, and developer experience features you'd expect from a professional package.
