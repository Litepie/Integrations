<?php

namespace Litepie\Integration\Tests\Unit;

use Litepie\Integration\Tests\TestCase;
use Litepie\Integration\Models\Integration;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IntegrationModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_integration_can_be_created()
    {
        $user = $this->createUser();
        
        $integration = Integration::create([
            'name' => 'Test Integration',
            'description' => 'A test integration',
            'redirect_uris' => ['https://example.com/callback'],
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(Integration::class, $integration);
        $this->assertEquals('Test Integration', $integration->name);
        $this->assertNotEmpty($integration->client_id);
        $this->assertNotEmpty($integration->client_secret);
        $this->assertEquals('active', $integration->status);
    }

    public function test_client_id_and_secret_are_auto_generated()
    {
        $user = $this->createUser();
        
        $integration = Integration::create([
            'name' => 'Test Integration',
            'redirect_uris' => ['https://example.com/callback'],
            'user_id' => $user->id,
        ]);

        $this->assertNotEmpty($integration->client_id);
        $this->assertNotEmpty($integration->client_secret);
        $this->assertEquals(40, strlen($integration->client_id));
        $this->assertEquals(80, strlen($integration->client_secret));
    }

    public function test_integration_can_be_activated_and_deactivated()
    {
        $user = $this->createUser();
        
        $integration = Integration::create([
            'name' => 'Test Integration',
            'redirect_uris' => ['https://example.com/callback'],
            'user_id' => $user->id,
        ]);

        $this->assertTrue($integration->isActive());
        $this->assertFalse($integration->isInactive());

        $integration->deactivate();
        $this->assertFalse($integration->isActive());
        $this->assertTrue($integration->isInactive());

        $integration->activate();
        $this->assertTrue($integration->isActive());
        $this->assertFalse($integration->isInactive());
    }

    public function test_redirect_uri_validation()
    {
        $user = $this->createUser();
        
        $integration = Integration::create([
            'name' => 'Test Integration',
            'redirect_uris' => ['https://example.com/callback', 'https://app.com/auth'],
            'user_id' => $user->id,
        ]);

        $this->assertTrue($integration->isValidRedirectUri('https://example.com/callback'));
        $this->assertTrue($integration->isValidRedirectUri('https://app.com/auth'));
        $this->assertFalse($integration->isValidRedirectUri('https://malicious.com/callback'));
    }

    public function test_redirect_uri_management()
    {
        $user = $this->createUser();
        
        $integration = Integration::create([
            'name' => 'Test Integration',
            'redirect_uris' => ['https://example.com/callback'],
            'user_id' => $user->id,
        ]);

        // Add a new URI
        $integration->addRedirectUri('https://new.com/callback');
        $this->assertTrue($integration->isValidRedirectUri('https://new.com/callback'));

        // Remove a URI
        $integration->removeRedirectUri('https://example.com/callback');
        $this->assertFalse($integration->isValidRedirectUri('https://example.com/callback'));
    }

    public function test_secret_can_be_regenerated()
    {
        $user = $this->createUser();
        
        $integration = Integration::create([
            'name' => 'Test Integration',
            'redirect_uris' => ['https://example.com/callback'],
            'user_id' => $user->id,
        ]);

        $originalSecret = $integration->client_secret;
        $integration->regenerateSecret();
        
        $this->assertNotEquals($originalSecret, $integration->fresh()->client_secret);
    }

    public function test_scope_filters()
    {
        $user1 = $this->createUser(['email' => 'user1@example.com']);
        $user2 = $this->createUser(['email' => 'user2@example.com']);
        
        $integration1 = Integration::create([
            'name' => 'User 1 Integration',
            'redirect_uris' => ['https://example.com/callback'],
            'user_id' => $user1->id,
        ]);

        $integration2 = Integration::create([
            'name' => 'User 2 Integration',
            'redirect_uris' => ['https://example.com/callback'],
            'user_id' => $user2->id,
            'status' => 'inactive',
        ]);

        // Test forUser scope
        $user1Integrations = Integration::forUser($user1->id)->get();
        $this->assertCount(1, $user1Integrations);
        $this->assertEquals($integration1->id, $user1Integrations->first()->id);

        // Test active scope
        $activeIntegrations = Integration::active()->get();
        $this->assertCount(1, $activeIntegrations);
        $this->assertEquals($integration1->id, $activeIntegrations->first()->id);

        // Test inactive scope
        $inactiveIntegrations = Integration::inactive()->get();
        $this->assertCount(1, $inactiveIntegrations);
        $this->assertEquals($integration2->id, $inactiveIntegrations->first()->id);
    }
}
