<?php

namespace Litepie\Integration\Tests\Feature;

use Litepie\Integration\Tests\TestCase;
use Litepie\Integration\Models\Integration;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IntegrationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_their_integrations()
    {
        $user = $this->createUser();
        $otherUser = $this->createUser(['email' => 'other@example.com']);
        
        // Create integration for authenticated user
        $integration = Integration::create([
            'name' => 'My Integration',
            'redirect_uris' => ['https://example.com/callback'],
            'user_id' => $user->id,
        ]);

        // Create integration for other user
        Integration::create([
            'name' => 'Other Integration',
            'redirect_uris' => ['https://example.com/callback'],
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/integrations');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonPath('data.0.name', 'My Integration');
    }

    public function test_user_can_create_integration()
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/integrations', [
                             'name' => 'Test Integration',
                             'description' => 'A test integration',
                             'redirect_uris' => ['https://example.com/callback'],
                         ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.name', 'Test Integration')
                 ->assertJsonPath('data.status', 'active')
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'name',
                         'description',
                         'client_id',
                         'redirect_uris',
                         'status',
                         'created_at',
                     ]
                 ]);

        $this->assertDatabaseHas('integrations', [
            'name' => 'Test Integration',
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_view_their_integration()
    {
        $user = $this->createUser();
        
        $integration = Integration::create([
            'name' => 'Test Integration',
            'redirect_uris' => ['https://example.com/callback'],
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson("/api/integrations/{$integration->client_id}");

        $response->assertStatus(200)
                 ->assertJsonPath('data.name', 'Test Integration');
    }

    public function test_user_cannot_view_other_users_integration()
    {
        $user = $this->createUser();
        $otherUser = $this->createUser(['email' => 'other@example.com']);
        
        $integration = Integration::create([
            'name' => 'Other Integration',
            'redirect_uris' => ['https://example.com/callback'],
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson("/api/integrations/{$integration->client_id}");

        $response->assertStatus(403);
    }

    public function test_user_can_update_their_integration()
    {
        $user = $this->createUser();
        
        $integration = Integration::create([
            'name' => 'Test Integration',
            'redirect_uris' => ['https://example.com/callback'],
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
                         ->putJson("/api/integrations/{$integration->client_id}", [
                             'name' => 'Updated Integration',
                             'description' => 'Updated description',
                         ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.name', 'Updated Integration');

        $this->assertDatabaseHas('integrations', [
            'id' => $integration->id,
            'name' => 'Updated Integration',
        ]);
    }

    public function test_user_can_delete_their_integration()
    {
        $user = $this->createUser();
        
        $integration = Integration::create([
            'name' => 'Test Integration',
            'redirect_uris' => ['https://example.com/callback'],
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
                         ->deleteJson("/api/integrations/{$integration->client_id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('integrations', [
            'id' => $integration->id,
        ]);
    }

    public function test_user_can_activate_integration()
    {
        $user = $this->createUser();
        
        $integration = Integration::create([
            'name' => 'Test Integration',
            'redirect_uris' => ['https://example.com/callback'],
            'user_id' => $user->id,
            'status' => 'inactive',
        ]);

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson("/api/integrations/{$integration->client_id}/activate");

        $response->assertStatus(200)
                 ->assertJsonPath('data.status', 'active');
    }

    public function test_user_can_deactivate_integration()
    {
        $user = $this->createUser();
        
        $integration = Integration::create([
            'name' => 'Test Integration',
            'redirect_uris' => ['https://example.com/callback'],
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson("/api/integrations/{$integration->client_id}/deactivate");

        $response->assertStatus(200)
                 ->assertJsonPath('data.status', 'inactive');
    }

    public function test_user_can_regenerate_secret()
    {
        $user = $this->createUser();
        
        $integration = Integration::create([
            'name' => 'Test Integration',
            'redirect_uris' => ['https://example.com/callback'],
            'user_id' => $user->id,
        ]);

        $originalSecret = $integration->client_secret;

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson("/api/integrations/{$integration->client_id}/regenerate-secret");

        $response->assertStatus(200)
                 ->assertJsonStructure(['client_secret']);

        $newSecret = $response->json('client_secret');
        $this->assertNotEquals($originalSecret, $newSecret);
    }

    public function test_validation_errors_on_create()
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/integrations', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'redirect_uris']);
    }

    public function test_pagination_works()
    {
        $user = $this->createUser();

        // Create multiple integrations
        for ($i = 1; $i <= 20; $i++) {
            Integration::create([
                'name' => "Integration {$i}",
                'redirect_uris' => ['https://example.com/callback'],
                'user_id' => $user->id,
            ]);
        }

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/integrations?per_page=5');

        $response->assertStatus(200)
                 ->assertJsonCount(5, 'data')
                 ->assertJsonStructure([
                     'data',
                     'meta' => [
                         'total',
                         'per_page',
                         'current_page',
                         'last_page',
                     ],
                     'links'
                 ]);
    }

    public function test_search_functionality()
    {
        $user = $this->createUser();

        Integration::create([
            'name' => 'Facebook Integration',
            'description' => 'Social media integration',
            'redirect_uris' => ['https://example.com/callback'],
            'user_id' => $user->id,
        ]);

        Integration::create([
            'name' => 'Google Integration',
            'description' => 'Search and analytics',
            'redirect_uris' => ['https://example.com/callback'],
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/integrations?search=Facebook');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonPath('data.0.name', 'Facebook Integration');
    }
}
