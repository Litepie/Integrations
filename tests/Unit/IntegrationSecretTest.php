<?php

namespace Litepie\Integration\Tests\Unit;

use Litepie\Integration\Tests\TestCase;
use Litepie\Integration\Models\Integration;
use Litepie\Integration\Models\IntegrationSecret;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IntegrationSecretTest extends TestCase
{
    use RefreshDatabase;

    public function test_integration_can_have_multiple_secrets()
    {
        $user = $this->createUser();
        
        $integration = Integration::create([
            'name' => 'Test Integration',
            'redirect_uris' => ['https://example.com/callback'],
            'user_id' => $user->id,
        ]);

        // Create multiple secrets
        $prodSecret = $integration->createSecret([
            'name' => 'Production',
            'metadata' => ['environment' => 'production']
        ]);

        $stagingSecret = $integration->createSecret([
            'name' => 'Staging',
            'expires_at' => now()->addDays(30),
            'metadata' => ['environment' => 'staging']
        ]);

        $this->assertCount(2, $integration->secrets);
        $this->assertInstanceOf(IntegrationSecret::class, $prodSecret);
        $this->assertInstanceOf(IntegrationSecret::class, $stagingSecret);
    }

    public function test_secret_auto_generates_key_and_name()
    {
        $user = $this->createUser();
        
        $integration = Integration::create([
            'name' => 'Test Integration',
            'redirect_uris' => ['https://example.com/callback'],
            'user_id' => $user->id,
        ]);

        $secret = $integration->createSecret();

        $this->assertNotEmpty($secret->secret_key);
        $this->assertEquals(80, strlen($secret->secret_key)); // Default length
        $this->assertEquals('Secret Key 1', $secret->name);
        $this->assertEquals('active', $secret->status);
    }

    public function test_secret_validation_methods()
    {
        $user = $this->createUser();
        
        $integration = Integration::create([
            'name' => 'Test Integration',
            'redirect_uris' => ['https://example.com/callback'],
            'user_id' => $user->id,
        ]);

        $activeSecret = $integration->createSecret(['name' => 'Active Secret']);
        $inactiveSecret = $integration->createSecret([
            'name' => 'Inactive Secret',
            'status' => 'inactive'
        ]);
        $expiredSecret = $integration->createSecret([
            'name' => 'Expired Secret',
            'expires_at' => now()->subDay()
        ]);

        $this->assertTrue($activeSecret->isActive());
        $this->assertTrue($activeSecret->isValid());
        $this->assertFalse($activeSecret->isExpired());

        $this->assertFalse($inactiveSecret->isActive());
        $this->assertFalse($inactiveSecret->isValid());

        $this->assertTrue($expiredSecret->isExpired());
        $this->assertFalse($expiredSecret->isValid());
    }

    public function test_integration_can_validate_secrets()
    {
        $user = $this->createUser();
        
        $integration = Integration::create([
            'name' => 'Test Integration',
            'redirect_uris' => ['https://example.com/callback'],
            'user_id' => $user->id,
        ]);

        $validSecret = $integration->createSecret(['name' => 'Valid Secret']);
        $invalidSecret = $integration->createSecret([
            'name' => 'Invalid Secret',
            'status' => 'inactive'
        ]);

        $this->assertTrue($integration->validateSecret($validSecret->secret_key));
        $this->assertFalse($integration->validateSecret($invalidSecret->secret_key));
        $this->assertFalse($integration->validateSecret('non-existent-key'));
    }

    public function test_secret_rotation()
    {
        $user = $this->createUser();
        
        $integration = Integration::create([
            'name' => 'Test Integration',
            'redirect_uris' => ['https://example.com/callback'],
            'user_id' => $user->id,
        ]);

        // Create some secrets
        $secret1 = $integration->createSecret(['name' => 'Secret 1']);
        $secret2 = $integration->createSecret(['name' => 'Secret 2']);

        $this->assertTrue($secret1->isActive());
        $this->assertTrue($secret2->isActive());

        // Rotate secrets
        $newSecret = $integration->rotateSecrets('New Secret');

        // Old secrets should be deactivated
        $this->assertFalse($secret1->fresh()->isActive());
        $this->assertFalse($secret2->fresh()->isActive());

        // New secret should be active
        $this->assertTrue($newSecret->isActive());
        $this->assertEquals('New Secret', $newSecret->name);
    }

    public function test_expired_secret_cleanup()
    {
        $user = $this->createUser();
        
        $integration = Integration::create([
            'name' => 'Test Integration',
            'redirect_uris' => ['https://example.com/callback'],
            'user_id' => $user->id,
        ]);

        // Create active and expired secrets
        $activeSecret = $integration->createSecret(['name' => 'Active']);
        $expiredSecret1 = $integration->createSecret([
            'name' => 'Expired 1',
            'expires_at' => now()->subDay()
        ]);
        $expiredSecret2 = $integration->createSecret([
            'name' => 'Expired 2',
            'expires_at' => now()->subHour()
        ]);

        $this->assertCount(3, $integration->secrets);

        $deletedCount = $integration->cleanupExpiredSecrets();

        $this->assertEquals(2, $deletedCount);
        $this->assertCount(1, $integration->secrets()->whereNull('deleted_at')->get());
    }

    public function test_secret_masked_display()
    {
        $user = $this->createUser();
        
        $integration = Integration::create([
            'name' => 'Test Integration',
            'redirect_uris' => ['https://example.com/callback'],
            'user_id' => $user->id,
        ]);

        $secret = $integration->createSecret(['name' => 'Test Secret']);
        $maskedSecret = $secret->masked_secret;

        // Should show first 4 and last 4 characters with asterisks in between
        $originalKey = $secret->secret_key;
        $expectedMasked = substr($originalKey, 0, 4) . str_repeat('*', strlen($originalKey) - 8) . substr($originalKey, -4);
        
        $this->assertEquals($expectedMasked, $maskedSecret);
        $this->assertStringContainsString('****', $maskedSecret);
    }

    public function test_secret_expiration_management()
    {
        $user = $this->createUser();
        
        $integration = Integration::create([
            'name' => 'Test Integration',
            'redirect_uris' => ['https://example.com/callback'],
            'user_id' => $user->id,
        ]);

        $secret = $integration->createSecret(['name' => 'Test Secret']);
        
        $this->assertNull($secret->expires_at);

        // Set expiration
        $expirationDate = now()->addDays(30);
        $secret->setExpiration($expirationDate);
        
        $this->assertEquals($expirationDate->format('Y-m-d H:i:s'), $secret->fresh()->expires_at->format('Y-m-d H:i:s'));

        // Remove expiration
        $secret->removeExpiration();
        
        $this->assertNull($secret->fresh()->expires_at);
    }

    public function test_secret_usage_tracking()
    {
        $user = $this->createUser();
        
        $integration = Integration::create([
            'name' => 'Test Integration',
            'redirect_uris' => ['https://example.com/callback'],
            'user_id' => $user->id,
        ]);

        $secret = $integration->createSecret(['name' => 'Test Secret']);
        
        $this->assertNull($secret->last_used_at);

        $secret->markAsUsed();
        
        $this->assertNotNull($secret->fresh()->last_used_at);
        $this->assertTrue($secret->fresh()->last_used_at->isToday());
    }
}
