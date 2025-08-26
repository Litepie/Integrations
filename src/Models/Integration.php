<?php

namespace Litepie\Integration\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Integration extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'client_id',
        'client_secret',
        'redirect_uris',
        'status',
        'user_id',
        'metadata',
        'role',
        'permissions',
        'allowed_scopes',
        'default_scopes',
        'ip_whitelist',
        'geo_restrictions',
        'time_restrictions',
        'rate_limits',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'client_secret',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'redirect_uris' => 'array',
        'metadata' => 'array',
        'permissions' => 'array',
        'allowed_scopes' => 'array',
        'default_scopes' => 'array',
        'ip_whitelist' => 'array',
        'geo_restrictions' => 'array',
        'time_restrictions' => 'array',
        'rate_limits' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Integration $integration) {
            if (empty($integration->client_id)) {
                $integration->client_id = static::generateClientId();
            }
            
            if (empty($integration->client_secret)) {
                $integration->client_secret = static::generateClientSecret();
            }

            if (empty($integration->status)) {
                $integration->status = config('integration.default_status', 'active');
            }
        });

        static::created(function (Integration $integration) {
            event(new \Litepie\Integration\Events\IntegrationCreated($integration));
        });

        static::updated(function (Integration $integration) {
            event(new \Litepie\Integration\Events\IntegrationUpdated($integration));
        });

        static::deleted(function (Integration $integration) {
            event(new \Litepie\Integration\Events\IntegrationDeleted($integration));
        });
    }

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return config('integration.table_names.integrations', parent::getTable());
    }

    /**
     * Get the user that owns the integration.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    /**
     * Get the secrets for this integration.
     */
    public function secrets(): HasMany
    {
        return $this->hasMany(IntegrationSecret::class);
    }

    /**
     * Get only active secrets for this integration.
     */
    public function activeSecrets(): HasMany
    {
        return $this->hasMany(IntegrationSecret::class)->active()->notExpired();
    }

    /**
     * Scope to filter by active status.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter by inactive status.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope to filter by user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Check if the integration is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the integration is inactive.
     */
    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    /**
     * Activate the integration.
     */
    public function activate(): bool
    {
        return $this->update(['status' => 'active']);
    }

    /**
     * Deactivate the integration.
     */
    public function deactivate(): bool
    {
        return $this->update(['status' => 'inactive']);
    }

    /**
     * Regenerate the client secret.
     */
    public function regenerateSecret(): bool
    {
        return $this->update(['client_secret' => static::generateClientSecret()]);
    }

    /**
     * Create a new secret for this integration.
     */
    public function createSecret(array $attributes = []): IntegrationSecret
    {
        return $this->secrets()->create($attributes);
    }

    /**
     * Get a secret by its key.
     */
    public function getSecretByKey(string $secretKey): ?IntegrationSecret
    {
        return $this->secrets()->where('secret_key', $secretKey)->first();
    }

    /**
     * Validate a secret key for this integration.
     */
    public function validateSecret(string $secretKey): bool
    {
        $secret = $this->getSecretByKey($secretKey);
        return $secret && $secret->isValid();
    }

    /**
     * Get all valid secrets (active and not expired).
     */
    public function getValidSecrets()
    {
        return $this->activeSecrets;
    }

    /**
     * Rotate secrets - deactivate old ones and create a new one.
     */
    public function rotateSecrets(string $newSecretName = null): IntegrationSecret
    {
        // Deactivate all existing secrets
        $this->secrets()->update(['status' => 'inactive']);
        
        // Create new secret
        return $this->createSecret([
            'name' => $newSecretName ?: 'Rotated Secret ' . now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Clean up expired secrets.
     */
    public function cleanupExpiredSecrets(): int
    {
        return $this->secrets()
            ->where('expires_at', '<=', now())
            ->delete();
    }

    /**
     * Check if a redirect URI is valid for this integration.
     */
    public function isValidRedirectUri(string $uri): bool
    {
        return in_array($uri, $this->redirect_uris ?? []);
    }

    /**
     * Add a redirect URI.
     */
    public function addRedirectUri(string $uri): bool
    {
        $uris = $this->redirect_uris ?? [];
        
        if (!in_array($uri, $uris)) {
            $uris[] = $uri;
            return $this->update(['redirect_uris' => $uris]);
        }

        return true;
    }

    /**
     * Remove a redirect URI.
     */
    public function removeRedirectUri(string $uri): bool
    {
        $uris = $this->redirect_uris ?? [];
        
        if (($key = array_search($uri, $uris)) !== false) {
            unset($uris[$key]);
            return $this->update(['redirect_uris' => array_values($uris)]);
        }

        return true;
    }

    /**
     * Generate a unique client ID.
     */
    protected static function generateClientId(): string
    {
        $length = config('integration.client.id_length', 40);
        
        do {
            $clientId = Str::random($length);
        } while (static::where('client_id', $clientId)->exists());

        return $clientId;
    }

    /**
     * Generate a client secret.
     */
    protected static function generateClientSecret(): string
    {
        $length = config('integration.client.secret_length', 80);
        return Str::random($length);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'client_id';
    }

    // Permission Management Methods

    /**
     * Check if the integration has a specific permission.
     */
    public function hasPermission(string $resource, string $action): bool
    {
        $permissions = $this->permissions ?? [];
        
        if (!isset($permissions[$resource])) {
            return false;
        }
        
        return in_array($action, $permissions[$resource]);
    }

    /**
     * Grant a permission to the integration.
     */
    public function grantPermission(string $resource, string $action): bool
    {
        $permissions = $this->permissions ?? [];
        
        if (!isset($permissions[$resource])) {
            $permissions[$resource] = [];
        }
        
        if (!in_array($action, $permissions[$resource])) {
            $permissions[$resource][] = $action;
        }
        
        return $this->update(['permissions' => $permissions]);
    }

    /**
     * Revoke a permission from the integration.
     */
    public function revokePermission(string $resource, string $action): bool
    {
        $permissions = $this->permissions ?? [];
        
        if (isset($permissions[$resource])) {
            $permissions[$resource] = array_values(
                array_filter($permissions[$resource], fn($perm) => $perm !== $action)
            );
            
            if (empty($permissions[$resource])) {
                unset($permissions[$resource]);
            }
        }
        
        return $this->update(['permissions' => $permissions]);
    }

    /**
     * Get all permissions for a specific resource.
     */
    public function getResourcePermissions(string $resource): array
    {
        $permissions = $this->permissions ?? [];
        return $permissions[$resource] ?? [];
    }

    /**
     * Set permissions for a resource.
     */
    public function setResourcePermissions(string $resource, array $actions): bool
    {
        $permissions = $this->permissions ?? [];
        $permissions[$resource] = array_unique($actions);
        
        return $this->update(['permissions' => $permissions]);
    }

    // Role Management Methods

    /**
     * Check if the integration has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Set the role for the integration.
     */
    public function setRole(string $role): bool
    {
        return $this->update(['role' => $role]);
    }

    /**
     * Check if the integration has admin role.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if the integration has user role.
     */
    public function isUser(): bool
    {
        return $this->hasRole('user');
    }

    /**
     * Check if the integration has guest role.
     */
    public function isGuest(): bool
    {
        return $this->hasRole('guest');
    }

    // Scope Management Methods

    /**
     * Check if a scope is allowed for this integration.
     */
    public function hasScope(string $scope): bool
    {
        $allowedScopes = $this->allowed_scopes ?? [];
        return in_array($scope, $allowedScopes);
    }

    /**
     * Add an allowed scope.
     */
    public function addScope(string $scope): bool
    {
        $scopes = $this->allowed_scopes ?? [];
        
        if (!in_array($scope, $scopes)) {
            $scopes[] = $scope;
            return $this->update(['allowed_scopes' => $scopes]);
        }
        
        return true;
    }

    /**
     * Remove an allowed scope.
     */
    public function removeScope(string $scope): bool
    {
        $scopes = $this->allowed_scopes ?? [];
        
        if (($key = array_search($scope, $scopes)) !== false) {
            unset($scopes[$key]);
            return $this->update(['allowed_scopes' => array_values($scopes)]);
        }
        
        return true;
    }

    /**
     * Validate requested scopes against allowed scopes.
     */
    public function validateScopes(array $requestedScopes): array
    {
        $allowedScopes = $this->allowed_scopes ?? [];
        return array_intersect($requestedScopes, $allowedScopes);
    }

    // IP Restriction Methods

    /**
     * Check if an IP address is allowed.
     */
    public function isIpAllowed(string $ip): bool
    {
        $ipConfig = $this->ip_whitelist ?? [];
        
        if (!($ipConfig['require_whitelist'] ?? false)) {
            return true;
        }
        
        $allowedIps = $ipConfig['allowed_ips'] ?? [];
        $blockedIps = $ipConfig['blocked_ips'] ?? [];
        
        // Check if IP is blocked
        foreach ($blockedIps as $blockedIp) {
            if ($this->ipMatches($ip, $blockedIp)) {
                return false;
            }
        }
        
        // Check if IP is in whitelist
        foreach ($allowedIps as $allowedIp) {
            if ($this->ipMatches($ip, $allowedIp)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Add an IP to the whitelist.
     */
    public function addAllowedIp(string $ip): bool
    {
        $ipConfig = $this->ip_whitelist ?? [];
        $allowedIps = $ipConfig['allowed_ips'] ?? [];
        
        if (!in_array($ip, $allowedIps)) {
            $allowedIps[] = $ip;
            $ipConfig['allowed_ips'] = $allowedIps;
            return $this->update(['ip_whitelist' => $ipConfig]);
        }
        
        return true;
    }

    /**
     * Remove an IP from the whitelist.
     */
    public function removeAllowedIp(string $ip): bool
    {
        $ipConfig = $this->ip_whitelist ?? [];
        $allowedIps = $ipConfig['allowed_ips'] ?? [];
        
        if (($key = array_search($ip, $allowedIps)) !== false) {
            unset($allowedIps[$key]);
            $ipConfig['allowed_ips'] = array_values($allowedIps);
            return $this->update(['ip_whitelist' => $ipConfig]);
        }
        
        return true;
    }

    // Geographic Restriction Methods

    /**
     * Check if a country is allowed.
     */
    public function isCountryAllowed(string $countryCode): bool
    {
        $geoConfig = $this->geo_restrictions ?? [];
        
        $allowedCountries = $geoConfig['allowed_countries'] ?? [];
        $blockedCountries = $geoConfig['blocked_countries'] ?? [];
        
        // Check if country is blocked
        if (in_array($countryCode, $blockedCountries)) {
            return false;
        }
        
        // If no allowed countries specified, allow all (except blocked)
        if (empty($allowedCountries)) {
            return true;
        }
        
        // Check if country is in allowed list
        return in_array($countryCode, $allowedCountries);
    }

    // Time Restriction Methods

    /**
     * Check if current time is within allowed hours.
     */
    public function isTimeAllowed(): bool
    {
        $timeConfig = $this->time_restrictions ?? [];
        
        if (empty($timeConfig)) {
            return true;
        }
        
        $timezone = $timeConfig['timezone'] ?? 'UTC';
        $now = now()->setTimezone($timezone);
        
        // Check allowed days
        if (isset($timeConfig['allowed_days'])) {
            $currentDay = strtolower($now->format('l'));
            if (!in_array($currentDay, $timeConfig['allowed_days'])) {
                return false;
            }
        }
        
        // Check allowed hours
        if (isset($timeConfig['allowed_hours'])) {
            $startTime = $timeConfig['allowed_hours']['start'] ?? '00:00';
            $endTime = $timeConfig['allowed_hours']['end'] ?? '23:59';
            
            $currentTime = $now->format('H:i');
            
            return $currentTime >= $startTime && $currentTime <= $endTime;
        }
        
        return true;
    }

    // Rate Limiting Methods

    /**
     * Get rate limit for a specific permission or scope.
     */
    public function getRateLimit(string $key = 'default'): ?array
    {
        $rateLimits = $this->rate_limits ?? [];
        
        if (isset($rateLimits['scope_limits'][$key])) {
            return $this->parseRateLimit($rateLimits['scope_limits'][$key]);
        }
        
        return [
            'requests_per_minute' => $rateLimits['requests_per_minute'] ?? null,
            'requests_per_hour' => $rateLimits['requests_per_hour'] ?? null,
            'requests_per_day' => $rateLimits['requests_per_day'] ?? null,
            'burst_limit' => $rateLimits['burst_limit'] ?? null,
        ];
    }

    // Helper Methods

    /**
     * Check if an IP matches a pattern (supports CIDR notation).
     */
    private function ipMatches(string $ip, string $pattern): bool
    {
        if ($ip === $pattern) {
            return true;
        }
        
        if (strpos($pattern, '/') !== false) {
            list($subnet, $bits) = explode('/', $pattern);
            $ip_long = ip2long($ip);
            $subnet_long = ip2long($subnet);
            $mask = -1 << (32 - $bits);
            
            return ($ip_long & $mask) === ($subnet_long & $mask);
        }
        
        return false;
    }

    /**
     * Parse rate limit string (e.g., "100/minute").
     */
    private function parseRateLimit(string $rateLimit): array
    {
        if (preg_match('/(\d+)\/(minute|hour|day)/', $rateLimit, $matches)) {
            $count = (int) $matches[1];
            $period = $matches[2];
            
            return [
                "requests_per_{$period}" => $count,
            ];
        }
        
        return [];
    }
}
