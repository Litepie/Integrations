<?php

namespace Litepie\Integration\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Litepie\Integration\Models\Integration create(array $attributes)
 * @method static \Litepie\Integration\Models\Integration|null findByClientId(string $clientId)
 * @method static bool validateCredentials(string $clientId, string $clientSecret)
 * @method static bool validateRedirectUri(string $clientId, string $redirectUri)
 * @method static \Illuminate\Database\Eloquent\Builder getUserIntegrations(int $userId, array $filters = [])
 * @method static string generateClientId()
 * @method static string generateClientSecret()
 */
class Integration extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'integration';
    }
}
