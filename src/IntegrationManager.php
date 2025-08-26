<?php

namespace Litepie\Integration;

use Illuminate\Contracts\Foundation\Application;
use Litepie\Integration\Models\Integration;

class IntegrationManager
{
    /**
     * The application instance.
     */
    protected Application $app;

    /**
     * Create a new integration manager instance.
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Create a new integration.
     */
    public function create(array $attributes): Integration
    {
        return Integration::create($attributes);
    }

    /**
     * Find an integration by client ID.
     */
    public function findByClientId(string $clientId): ?Integration
    {
        return Integration::where('client_id', $clientId)->first();
    }

    /**
     * Validate client credentials.
     */
    public function validateCredentials(string $clientId, string $clientSecret): bool
    {
        $integration = $this->findByClientId($clientId);

        if (!$integration || !$integration->isActive()) {
            return false;
        }

        // Check legacy client_secret first
        if ($integration->client_secret === $clientSecret) {
            return true;
        }

        // Check multiple secrets
        return $integration->validateSecret($clientSecret);
    }

    /**
     * Validate credentials and return the integration.
     */
    public function validateCredentialsAndGet(string $clientId, string $clientSecret): ?Integration
    {
        if ($this->validateCredentials($clientId, $clientSecret)) {
            $integration = $this->findByClientId($clientId);
            
            // Mark secret as used if it's from the multiple secrets system
            $secret = $integration->getSecretByKey($clientSecret);
            if ($secret) {
                $secret->markAsUsed();
            }
            
            return $integration;
        }

        return null;
    }

    /**
     * Validate redirect URI for an integration.
     */
    public function validateRedirectUri(string $clientId, string $redirectUri): bool
    {
        $integration = $this->findByClientId($clientId);

        return $integration && $integration->isValidRedirectUri($redirectUri);
    }

    /**
     * Get integrations for a user.
     */
    public function getUserIntegrations(int $userId, array $filters = [])
    {
        $query = Integration::forUser($userId);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'LIKE', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'LIKE', '%' . $filters['search'] . '%');
            });
        }

        return $query->latest();
    }

    /**
     * Generate a new client ID.
     */
    public function generateClientId(): string
    {
        return Integration::generateClientId();
    }

    /**
     * Generate a new client secret.
     */
    public function generateClientSecret(): string
    {
        return Integration::generateClientSecret();
    }
}
