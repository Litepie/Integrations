<?php

namespace Litepie\Integration\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Litepie\Integration\IntegrationServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [
            IntegrationServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Configure basic auth instead of Sanctum for testing
        config()->set('auth.defaults.guard', 'web');
        config()->set('auth.guards.web', [
            'driver' => 'session',
            'provider' => 'users',
        ]);
        config()->set('auth.providers.users', [
            'driver' => 'eloquent',
            'model' => \Illuminate\Foundation\Auth\User::class,
        ]);

        // Override integration middleware for testing
        config()->set('integration.middleware.api', ['api', 'auth']);

        // Create users table for testing
        $app['db']->connection()->getSchemaBuilder()->create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    protected function createUser(array $attributes = []): \Illuminate\Foundation\Auth\User
    {
        return new class($attributes) extends \Illuminate\Foundation\Auth\User {
            protected $table = 'users';
            protected $fillable = ['name', 'email', 'password'];
            
            public function __construct(array $attributes = [])
            {
                parent::__construct();
                $this->fill(array_merge([
                    'name' => 'Test User',
                    'email' => 'test-' . uniqid() . '@example.com',
                    'password' => bcrypt('password'),
                ], $attributes));
                $this->save();
            }
        };
    }
}
