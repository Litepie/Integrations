<?php

namespace Litepie\Integration\Database\Seeders;

use Illuminate\Database\Seeder;
use Litepie\Integration\Models\Integration;

class IntegrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample integrations for testing
        $integrations = [
            [
                'name' => 'Facebook Integration',
                'description' => 'Integration with Facebook Graph API',
                'redirect_uris' => [
                    'https://example.com/auth/facebook/callback',
                    'https://app.example.com/facebook/callback',
                ],
                'status' => 'active',
                'user_id' => 1,
                'metadata' => [
                    'webhook_url' => 'https://example.com/webhooks/facebook',
                    'scopes' => ['email', 'public_profile', 'pages_read_engagement'],
                ],
            ],
            [
                'name' => 'Google Analytics Integration',
                'description' => 'Integration with Google Analytics API',
                'redirect_uris' => [
                    'https://example.com/auth/google/callback',
                ],
                'status' => 'active',
                'user_id' => 1,
                'metadata' => [
                    'webhook_url' => 'https://example.com/webhooks/google',
                    'scopes' => ['analytics.readonly'],
                ],
            ],
            [
                'name' => 'Slack Integration',
                'description' => 'Integration with Slack API for notifications',
                'redirect_uris' => [
                    'https://example.com/auth/slack/callback',
                ],
                'status' => 'inactive',
                'user_id' => 2,
                'metadata' => [
                    'webhook_url' => 'https://example.com/webhooks/slack',
                    'scopes' => ['chat:write', 'channels:read'],
                ],
            ],
        ];

        foreach ($integrations as $integration) {
            Integration::create($integration);
        }
    }
}
