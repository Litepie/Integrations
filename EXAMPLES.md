# Integration Package Examples

This document provides examples of how to use the Litepie Integration package.

## Basic Usage

### Creating an Integration

```php
use Litepie\Integration\Models\Integration;

$integration = Integration::create([
    'name' => 'My API Integration',
    'description' => 'Integration for my application',
    'redirect_uris' => [
        'https://myapp.com/callback',
        'https://myapp.com/auth/callback'
    ],
    'user_id' => auth()->id(),
]);

echo "Client ID: " . $integration->client_id;
echo "Client Secret: " . $integration->client_secret; // Legacy single secret

// Create additional secrets
$productionSecret = $integration->createSecret([
    'name' => 'Production Key',
    'expires_at' => now()->addYear(),
]);

$stagingSecret = $integration->createSecret([
    'name' => 'Staging Key',
    'expires_at' => now()->addMonths(6),
]);

echo "Production Secret: " . $productionSecret->secret_key;
echo "Staging Secret: " . $stagingSecret->secret_key;
```

## Permission and Restriction Management

The Integration package now supports comprehensive permission, role-based access, IP restrictions, geographic limitations, and time-based controls.

### Role-Based Access Control

```php
use Litepie\Integration\Models\Integration;

// Create integration with role
$integration = Integration::create([
    'name' => 'Admin Dashboard',
    'role' => 'admin',
    'redirect_uris' => ['https://admin.myapp.com/callback'],
    'user_id' => auth()->id(),
]);

// Check roles
if ($integration->isAdmin()) {
    echo "This integration has admin access";
}

if ($integration->hasRole('user')) {
    echo "This integration has user role";
}

// Set different role
$integration->setRole('readonly');
```

### Permission Management

```php
// Grant permissions
$integration->grantPermission('users', 'read');
$integration->grantPermission('users', 'write');
$integration->grantPermission('posts', 'read');

// Check permissions
if ($integration->hasPermission('users', 'read')) {
    echo "Can read users";
}

// Set multiple permissions for a resource
$integration->setResourcePermissions('analytics', ['read', 'export']);

// Get all permissions for a resource
$userPermissions = $integration->getResourcePermissions('users');

// Revoke permission
$integration->revokePermission('users', 'write');
```

### IP Address Restrictions

```php
// Create integration with IP whitelist
$integration = Integration::create([
    'name' => 'Office Network Integration',
    'ip_whitelist' => [
        'require_whitelist' => true,
        'allowed_ips' => ['203.0.113.0/24', '198.51.100.50'],
        'blocked_ips' => ['192.168.1.100'],
    ],
    'redirect_uris' => ['https://myapp.com/callback'],
    'user_id' => auth()->id(),
]);

// Add allowed IP
$integration->addAllowedIp('203.0.113.100');

// Check if IP is allowed
if ($integration->isIpAllowed('203.0.113.50')) {
    echo "IP is allowed";
}

// Remove allowed IP
$integration->removeAllowedIp('203.0.113.100');
```

### Geographic Restrictions

```php
// Create integration with geographic restrictions
$integration = Integration::create([
    'name' => 'US/Canada Only Integration',
    'geo_restrictions' => [
        'allowed_countries' => ['US', 'CA'],
        'blocked_countries' => [],
        'strict_mode' => true,
    ],
    'redirect_uris' => ['https://myapp.com/callback'],
    'user_id' => auth()->id(),
]);

// Check if country is allowed
if ($integration->isCountryAllowed('US')) {
    echo "US is allowed";
}

// Update geo restrictions
$integration->update([
    'geo_restrictions' => [
        'allowed_countries' => ['US', 'CA', 'GB'],
        'blocked_countries' => ['CN', 'RU'],
    ]
]);
```

### Time-Based Restrictions

```php
// Create integration with business hours restriction
$integration = Integration::create([
    'name' => 'Business Hours Integration',
    'time_restrictions' => [
        'allowed_hours' => ['start' => '09:00', 'end' => '17:00'],
        'allowed_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
        'timezone' => 'America/New_York',
        'business_hours_only' => true,
    ],
    'redirect_uris' => ['https://myapp.com/callback'],
    'user_id' => auth()->id(),
]);

// Check if current time is allowed
if ($integration->isTimeAllowed()) {
    echo "Access allowed at current time";
}

// Update time restrictions
$integration->update([
    'time_restrictions' => [
        'allowed_hours' => ['start' => '08:00', 'end' => '20:00'],
        'timezone' => 'UTC',
    ]
]);
```

### Scope Management

```php
// Create integration with OAuth scopes
$integration = Integration::create([
    'name' => 'Third Party App',
    'allowed_scopes' => ['read', 'write', 'user'],
    'default_scopes' => ['read'],
    'redirect_uris' => ['https://thirdparty.com/callback'],
    'user_id' => auth()->id(),
]);

// Check if scope is allowed
if ($integration->hasScope('write')) {
    echo "Write scope is allowed";
}

// Add scope
$integration->addScope('admin');

// Remove scope
$integration->removeScope('write');

// Validate requested scopes
$requestedScopes = ['read', 'write', 'admin'];
$validScopes = $integration->validateScopes($requestedScopes);
```

### Rate Limiting

```php
// Create integration with rate limits
$integration = Integration::create([
    'name' => 'API Integration',
    'rate_limits' => [
        'requests_per_minute' => 100,
        'requests_per_hour' => 1000,
        'requests_per_day' => 10000,
        'burst_limit' => 200,
        'scope_limits' => [
            'users:write' => '10/minute',
            'admin:access' => '5/minute',
        ],
    ],
    'redirect_uris' => ['https://api.myapp.com/callback'],
    'user_id' => auth()->id(),
]);

// Get rate limit for specific scope
$rateLimit = $integration->getRateLimit('users:write');
echo "Users write limit: " . $rateLimit['requests_per_minute'];

// Get default rate limit
$defaultLimit = $integration->getRateLimit();
```

### Complete Enterprise Example

```php
// Create enterprise integration with all restrictions
$enterpriseIntegration = Integration::create([
    'name' => 'Enterprise Client Integration',
    'description' => 'Secure enterprise client with full restrictions',
    'role' => 'admin',
    'permissions' => [
        'users' => ['read', 'write'],
        'posts' => ['read'],
        'analytics' => ['read', 'export'],
        'admin' => ['settings'],
    ],
    'allowed_scopes' => ['user', 'content', 'analytics'],
    'default_scopes' => ['user'],
    'ip_whitelist' => [
        'require_whitelist' => true,
        'allowed_ips' => ['203.0.113.0/24', '198.51.100.0/24'],
    ],
    'geo_restrictions' => [
        'allowed_countries' => ['US', 'CA'],
        'strict_mode' => true,
    ],
    'time_restrictions' => [
        'allowed_hours' => ['start' => '06:00', 'end' => '22:00'],
        'allowed_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
        'timezone' => 'America/New_York',
    ],
    'rate_limits' => [
        'requests_per_minute' => 500,
        'requests_per_hour' => 10000,
        'burst_limit' => 1000,
        'scope_limits' => [
            'admin:settings' => '5/minute',
            'analytics:export' => '10/hour',
        ],
    ],
    'redirect_uris' => ['https://enterprise.myapp.com/callback'],
    'user_id' => auth()->id(),
]);

// Create multiple secrets with different restrictions
$prodSecret = $enterpriseIntegration->createSecret([
    'name' => 'Production Key',
    'expires_at' => now()->addYear(),
]);

$stagingSecret = $enterpriseIntegration->createSecret([
    'name' => 'Staging Key',
    'expires_at' => now()->addMonths(6),
]);
```

## Using Middleware for API Protection

### Basic Usage

```php
// In your routes file
Route::middleware(['integration.restrictions'])->group(function () {
    Route::get('/api/users', [UserController::class, 'index']);
    Route::post('/api/users', [UserController::class, 'store']);
});

// With specific permission requirements
Route::middleware(['integration.restrictions:users:read'])->group(function () {
    Route::get('/api/users', [UserController::class, 'index']);
});

Route::middleware(['integration.restrictions:users:write'])->group(function () {
    Route::post('/api/users', [UserController::class, 'store']);
    Route::put('/api/users/{user}', [UserController::class, 'update']);
});

// Multiple permissions
Route::middleware(['integration.restrictions:users:read,posts:read'])->group(function () {
    Route::get('/api/dashboard', [DashboardController::class, 'index']);
});
```

### Advanced Middleware Usage

```php
// Role-specific routes
Route::middleware(['integration.restrictions', 'integration.role:admin'])->group(function () {
    Route::get('/api/admin/users', [AdminController::class, 'users']);
    Route::get('/api/admin/settings', [AdminController::class, 'settings']);
});

// Time-restricted routes (business hours only)
Route::middleware(['integration.restrictions:business_hours'])->group(function () {
    Route::post('/api/reports/generate', [ReportController::class, 'generate']);
});

// High-security routes with multiple restrictions
Route::middleware([
    'integration.restrictions:admin:access',
    'integration.ip:office_network',
    'integration.geo:us_only'
])->group(function () {
    Route::delete('/api/admin/users/{user}', [AdminController::class, 'deleteUser']);
});
```

### API Authentication Headers

```bash
# Required headers for API access
curl -X GET http://your-app.com/api/users \
  -H "X-Client-ID: your-client-id" \
  -H "X-Client-Secret: your-secret-key" \
  -H "Accept: application/json"

# Alternative: URL parameters
curl -X GET "http://your-app.com/api/users?client_id=your-client-id&client_secret=your-secret-key" \
  -H "Accept: application/json"

# With geographic header (if using CDN)
curl -X GET http://your-app.com/api/users \
  -H "X-Client-ID: your-client-id" \
  -H "X-Client-Secret: your-secret-key" \
  -H "X-Country-Code: US" \
  -H "Accept: application/json"
```

### Using the Facade

```php
use Litepie\Integration\Facades\Integration;

// Create integration
$integration = Integration::create([
    'name' => 'Test Integration',
    'redirect_uris' => ['https://example.com/callback'],
    'user_id' => 1,
]);

// Find by client ID
$integration = Integration::findByClientId('your-client-id');

// Validate credentials
$isValid = Integration::validateCredentials('client-id', 'client-secret');

// Validate redirect URI
$isValidUri = Integration::validateRedirectUri('client-id', 'https://example.com/callback');
```

## Multiple Secret Keys Management

Similar to Amazon IAM, you can now manage multiple secret keys for each integration:

### Creating Multiple Secrets

```php
use Litepie\Integration\Models\Integration;

$integration = Integration::create([
    'name' => 'My App Integration',
    'redirect_uris' => ['https://myapp.com/callback'],
    'user_id' => auth()->id(),
]);

// Create production secret
$prodSecret = $integration->createSecret([
    'name' => 'Production Environment',
    'expires_at' => now()->addYear(),
    'metadata' => ['environment' => 'production']
]);

// Create staging secret
$stagingSecret = $integration->createSecret([
    'name' => 'Staging Environment',
    'expires_at' => now()->addMonths(6),
    'metadata' => ['environment' => 'staging']
]);

// Create development secret (no expiration)
$devSecret = $integration->createSecret([
    'name' => 'Development Environment',
    'metadata' => ['environment' => 'development']
]);
```

### Managing Secrets

```php
// Get all secrets for an integration
$secrets = $integration->secrets;

// Get only active secrets
$activeSecrets = $integration->activeSecrets;

// Get only valid secrets (active and not expired)
$validSecrets = $integration->getValidSecrets();

// Validate a secret
$isValid = $integration->validateSecret('secret-key-here');

// Find a secret by key
$secret = $integration->getSecretByKey('secret-key-here');

// Deactivate a secret
$secret->deactivate();

// Set expiration
$secret->setExpiration(now()->addDays(30));

// Remove expiration
$secret->removeExpiration();

// Rotate all secrets (deactivate old ones, create new one)
$newSecret = $integration->rotateSecrets('Emergency Rotation');

// Clean up expired secrets
$deletedCount = $integration->cleanupExpiredSecrets();
```

## API Usage

### Authentication

All API endpoints require authentication. Use Laravel Sanctum or Passport:

```bash
# Get access token first (using Sanctum)
curl -X POST http://your-app.com/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'
```

### List Integrations

```bash
curl -X GET http://your-app.com/api/integrations \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json"
```

### Create Integration

```bash
curl -X POST http://your-app.com/api/integrations \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "My Integration",
    "description": "Description of my integration",
    "redirect_uris": [
      "https://myapp.com/callback",
      "https://myapp.com/auth"
    ]
  }'
```

### Update Integration

```bash
curl -X PUT http://your-app.com/api/integrations/YOUR_CLIENT_ID \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Updated Integration Name",
    "description": "Updated description"
  }'
```

### Activate/Deactivate Integration

```bash
# Activate
curl -X POST http://your-app.com/api/integrations/YOUR_CLIENT_ID/activate \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json"

# Deactivate
curl -X POST http://your-app.com/api/integrations/YOUR_CLIENT_ID/deactivate \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json"
```

### Regenerate Client Secret

```bash
curl -X POST http://your-app.com/api/integrations/YOUR_CLIENT_ID/regenerate-secret \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json"
```

## Multiple Secret Keys API

### List Integration Secrets

```bash
curl -X GET http://your-app.com/api/integrations/YOUR_CLIENT_ID/secrets \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json"

# Filter by status
curl -X GET "http://your-app.com/api/integrations/YOUR_CLIENT_ID/secrets?status=active" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json"

# Include expired secrets
curl -X GET "http://your-app.com/api/integrations/YOUR_CLIENT_ID/secrets?include_expired=true" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json"
```

### Create New Secret

```bash
curl -X POST http://your-app.com/api/integrations/YOUR_CLIENT_ID/secrets \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Production Environment Key",
    "expires_at": "2025-12-31T23:59:59Z",
    "metadata": {
      "environment": "production",
      "created_by": "admin"
    }
  }'
```

### Update Secret

```bash
curl -X PUT http://your-app.com/api/integrations/YOUR_CLIENT_ID/secrets/SECRET_ID \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Updated Secret Name",
    "status": "inactive"
  }'
```

### Activate/Deactivate Secret

```bash
# Activate
curl -X POST http://your-app.com/api/integrations/YOUR_CLIENT_ID/secrets/SECRET_ID/activate \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json"

# Deactivate
curl -X POST http://your-app.com/api/integrations/YOUR_CLIENT_ID/secrets/SECRET_ID/deactivate \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json"
```

### Set/Remove Expiration

```bash
# Set expiration
curl -X POST http://your-app.com/api/integrations/YOUR_CLIENT_ID/secrets/SECRET_ID/set-expiration \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "expires_at": "2025-06-30T23:59:59Z"
  }'

# Remove expiration
curl -X POST http://your-app.com/api/integrations/YOUR_CLIENT_ID/secrets/SECRET_ID/remove-expiration \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json"
```

### Rotate All Secrets

```bash
curl -X POST http://your-app.com/api/integrations/YOUR_CLIENT_ID/secrets/rotate \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Emergency Rotation Key"
  }'
```

### Cleanup Expired Secrets

```bash
curl -X POST http://your-app.com/api/integrations/YOUR_CLIENT_ID/secrets/cleanup \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json"
```

### Delete Secret

```bash
curl -X DELETE http://your-app.com/api/integrations/YOUR_CLIENT_ID/secrets/SECRET_ID \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json"
```

## OAuth Flow Example

### Using the OAuth Trait

```php
use Litepie\Integration\Traits\HandlesOAuth;
use Litepie\Integration\Models\Integration;

class OAuthController extends Controller
{
    use HandlesOAuth;

    public function authorize(Request $request)
    {
        $integration = Integration::where('client_id', $request->client_id)->first();
        
        if (!$integration || !$integration->isActive()) {
            return response()->json(['error' => 'invalid_client'], 400);
        }

        $authUrl = $this->generateAuthorizationUrl(
            $integration,
            $request->redirect_uri,
            explode(' ', $request->scope ?? ''),
            $request->state
        );

        return redirect($authUrl);
    }

    public function callback(Request $request)
    {
        $integration = Integration::where('client_id', $request->client_id)->first();
        
        if (!$this->validateOAuthCallback($request, $integration)) {
            return response()->json(['error' => 'invalid_request'], 400);
        }

        $token = $this->exchangeCodeForToken(
            $integration,
            $request->code,
            $request->redirect_uri
        );

        return response()->json($token);
    }
}
```

## Event Handling

### Listening to Integration Events

```php
use Litepie\Integration\Events\IntegrationCreated;
use Litepie\Integration\Events\IntegrationUpdated;
use Litepie\Integration\Events\IntegrationDeleted;

// In your EventServiceProvider
protected $listen = [
    IntegrationCreated::class => [
        \App\Listeners\SendIntegrationCreatedNotification::class,
    ],
    IntegrationUpdated::class => [
        \App\Listeners\LogIntegrationUpdate::class,
    ],
    IntegrationDeleted::class => [
        \App\Listeners\CleanupIntegrationData::class,
    ],
];
```

## Console Commands

### List All Integrations

```bash
php artisan integration:list
```

### Filter by User

```bash
php artisan integration:list --user=1
```

### Filter by Status

```bash
php artisan integration:list --status=active
```

### Limit Results

```bash
php artisan integration:list --limit=5
```

## Advanced Usage

### Custom Policy

If you need custom authorization logic, extend the Integration policy:

```php
use Litepie\Integration\Policies\IntegrationPolicy as BaseIntegrationPolicy;

class IntegrationPolicy extends BaseIntegrationPolicy
{
    public function view($user, $integration)
    {
        // Custom logic here
        if ($user->hasRole('admin')) {
            return true;
        }

        return parent::view($user, $integration);
    }
}
```

### Custom Middleware

```php
use Litepie\Integration\Http\Middleware\IntegrationRateLimit;

Route::middleware([IntegrationRateLimit::class.':100,5'])->group(function () {
    // Your routes with custom rate limiting
});
```

### Integration Factory in Tests

```php
use Litepie\Integration\Models\Integration;
use Litepie\Integration\Models\IntegrationSecret;

// In your tests
$integration = Integration::factory()->create([
    'user_id' => $user->id,
]);

$inactiveIntegration = Integration::factory()->inactive()->create();

// Create secrets for testing
$secret = IntegrationSecret::factory()->create([
    'integration_id' => $integration->id,
    'name' => 'Test Secret',
]);

$expiredSecret = IntegrationSecret::factory()->create([
    'integration_id' => $integration->id,
    'expires_at' => now()->subDay(),
]);
```

## Real-World Usage Scenarios

### AWS-Style Key Management

```php
// Create different keys for different environments
$integration = Integration::create([
    'name' => 'My SaaS Application',
    'redirect_uris' => ['https://myapp.com/callback'],
    'user_id' => auth()->id(),
]);

// Production key (long-lived, strict monitoring)
$prodKey = $integration->createSecret([
    'name' => 'Production API Key',
    'expires_at' => now()->addYear(),
    'metadata' => [
        'environment' => 'production',
        'permissions' => ['read', 'write'],
        'created_by' => auth()->user()->email,
        'ip_whitelist' => ['203.0.113.0/24'],
    ]
]);

// Staging key (shorter-lived)
$stagingKey = $integration->createSecret([
    'name' => 'Staging API Key',
    'expires_at' => now()->addMonths(3),
    'metadata' => [
        'environment' => 'staging',
        'permissions' => ['read', 'write', 'debug'],
    ]
]);

// Development key (no expiration, limited scope)
$devKey = $integration->createSecret([
    'name' => 'Development API Key',
    'metadata' => [
        'environment' => 'development',
        'permissions' => ['read'],
        'rate_limit' => 100,
    ]
]);

// Emergency/Rotation key
$emergencyKey = $integration->createSecret([
    'name' => 'Emergency Access Key',
    'expires_at' => now()->addDays(7),
    'metadata' => [
        'purpose' => 'emergency_access',
        'created_during' => 'security_incident_2024_001',
        'auto_expire' => true,
    ]
]);
```

### Service Account Management

```php
// Create keys for different services
$integration = Integration::findByClientId('my-client-id');

// Microservice A
$serviceAKey = $integration->createSecret([
    'name' => 'Microservice A',
    'metadata' => [
        'service' => 'user-service',
        'version' => 'v2.1.0',
        'deploy_id' => 'deploy-12345',
        'permissions' => ['users:read', 'users:write'],
    ]
]);

// Background Job Service
$jobServiceKey = $integration->createSecret([
    'name' => 'Background Jobs',
    'expires_at' => now()->addMonths(6),
    'metadata' => [
        'service' => 'job-processor',
        'queue' => 'high-priority',
        'permissions' => ['jobs:process', 'notifications:send'],
    ]
]);

// Analytics Service (read-only)
$analyticsKey = $integration->createSecret([
    'name' => 'Analytics Service',
    'metadata' => [
        'service' => 'analytics',
        'permissions' => ['analytics:read'],
        'data_retention' => '90_days',
    ]
]);
```

### Key Rotation Strategy

```php
// Scheduled key rotation (in a job or command)
class RotateIntegrationKeysJob
{
    public function handle()
    {
        $integrations = Integration::whereHas('secrets', function($query) {
            // Find integrations with keys expiring in 30 days
            $query->where('expires_at', '<=', now()->addDays(30))
                  ->where('status', 'active');
        })->get();

        foreach ($integrations as $integration) {
            // Notify before rotation
            $this->notifyKeyExpiring($integration);
            
            // Create new key
            $newKey = $integration->createSecret([
                'name' => 'Auto-rotated Key ' . now()->format('Y-m-d'),
                'expires_at' => now()->addYear(),
                'metadata' => [
                    'rotation_type' => 'automatic',
                    'previous_key_expired' => true,
                ]
            ]);
            
            // Log the rotation
            Log::info('Key rotated for integration', [
                'integration_id' => $integration->id,
                'new_secret_id' => $newKey->id,
                'client_id' => $integration->client_id,
            ]);
        }
    }
}
```

### Security Monitoring

```php
// Track secret usage
class ApiAuthMiddleware
{
    public function handle($request, Closure $next)
    {
        $clientId = $request->header('X-Client-ID');
        $secretKey = $request->header('X-Secret-Key');
        
        $integration = Integration::where('client_id', $clientId)->first();
        
        if (!$integration) {
            return response()->json(['error' => 'Invalid client'], 401);
        }
        
        if (!$integration->validateSecret($secretKey)) {
            // Log security event
            Log::warning('Invalid secret key used', [
                'client_id' => $clientId,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'attempted_secret' => substr($secretKey, 0, 8) . '...',
            ]);
            
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
        
        // Mark secret as used and track usage
        $secret = $integration->getSecretByKey($secretKey);
        $secret->markAsUsed();
        
        // Add integration context to request
        $request->merge([
            'integration' => $integration,
            'secret' => $secret,
        ]);
        
        return $next($request);
    }
}
```

### Webhook Management with Secrets

```php
// Different webhook endpoints with different keys
$integration = Integration::create([
    'name' => 'Payment Provider Integration',
    'redirect_uris' => ['https://myapp.com/webhooks/payments'],
    'user_id' => auth()->id(),
]);

// Live payments webhook
$liveWebhookKey = $integration->createSecret([
    'name' => 'Live Payments Webhook',
    'metadata' => [
        'webhook_url' => 'https://myapp.com/webhooks/payments/live',
        'events' => ['payment.succeeded', 'payment.failed'],
        'environment' => 'production',
    ]
]);

// Test payments webhook
$testWebhookKey = $integration->createSecret([
    'name' => 'Test Payments Webhook',
    'expires_at' => now()->addMonths(1),
    'metadata' => [
        'webhook_url' => 'https://myapp.com/webhooks/payments/test',
        'events' => ['*'],
        'environment' => 'testing',
    ]
]);

// Validate webhook signature
public function validateWebhookSignature($payload, $signature, $integration)
{
    foreach ($integration->activeSecrets as $secret) {
        $expectedSignature = hash_hmac('sha256', $payload, $secret->secret_key);
        
        if (hash_equals($signature, $expectedSignature)) {
            $secret->markAsUsed();
            return true;
        }
    }
    
    return false;
}
```
