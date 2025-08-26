<?php

namespace Litepie\Integration\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Litepie\Integration\Models\Integration;

trait HandlesOAuth
{
    /**
     * Generate an authorization URL for OAuth flow.
     */
    public function generateAuthorizationUrl(Integration $integration, string $redirectUri, array $scopes = [], string $state = null): string
    {
        if (!$integration->isValidRedirectUri($redirectUri)) {
            throw new \InvalidArgumentException('Invalid redirect URI.');
        }

        $state = $state ?: Str::random(32);

        $params = http_build_query([
            'response_type' => 'code',
            'client_id' => $integration->client_id,
            'redirect_uri' => $redirectUri,
            'scope' => implode(' ', $scopes),
            'state' => $state,
        ]);

        return config('app.url') . '/oauth/authorize?' . $params;
    }

    /**
     * Validate OAuth callback request.
     */
    public function validateOAuthCallback(Request $request, Integration $integration): bool
    {
        // Check for required parameters
        if (!$request->has(['code', 'state'])) {
            return false;
        }

        // Validate redirect URI if provided
        if ($request->has('redirect_uri') && !$integration->isValidRedirectUri($request->input('redirect_uri'))) {
            return false;
        }

        // Additional validation can be added here
        return true;
    }

    /**
     * Exchange authorization code for access token.
     */
    public function exchangeCodeForToken(Integration $integration, string $code, string $redirectUri): array
    {
        // This is a simplified example - in a real implementation,
        // you would make an HTTP request to the authorization server
        return [
            'access_token' => Str::random(64),
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'refresh_token' => Str::random(64),
            'scope' => 'read write',
        ];
    }

    /**
     * Validate OAuth access token.
     */
    public function validateAccessToken(string $token): bool
    {
        // In a real implementation, you would validate the token
        // against your OAuth server or stored tokens
        return !empty($token) && strlen($token) >= 32;
    }

    /**
     * Extract client credentials from request.
     */
    public function extractClientCredentials(Request $request): array
    {
        // Check Authorization header for Basic auth
        $authorization = $request->header('Authorization');
        if ($authorization && Str::startsWith($authorization, 'Basic ')) {
            $credentials = base64_decode(substr($authorization, 6));
            if (str_contains($credentials, ':')) {
                [$clientId, $clientSecret] = explode(':', $credentials, 2);
                return ['client_id' => $clientId, 'client_secret' => $clientSecret];
            }
        }

        // Check request body
        return [
            'client_id' => $request->input('client_id'),
            'client_secret' => $request->input('client_secret'),
        ];
    }

    /**
     * Generate OAuth error response.
     */
    public function oauthError(string $error, string $description = null, string $uri = null): array
    {
        $response = ['error' => $error];

        if ($description) {
            $response['error_description'] = $description;
        }

        if ($uri) {
            $response['error_uri'] = $uri;
        }

        return $response;
    }
}
