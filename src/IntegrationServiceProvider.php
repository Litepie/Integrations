<?php

namespace Litepie\Integration;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Event;
use Litepie\Integration\Models\Integration;
use Litepie\Integration\Policies\IntegrationPolicy;
use Litepie\Integration\Console\Commands\ListIntegrationsCommand;
use Litepie\Integration\Events\IntegrationCreated;
use Litepie\Integration\Events\IntegrationUpdated;
use Litepie\Integration\Events\IntegrationDeleted;
use Litepie\Integration\Listeners\LogIntegrationActivity;

class IntegrationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        
        $this->publishes([
            __DIR__.'/../config/integration.php' => config_path('integration.php'),
        ], 'integration-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'integration-migrations');

        $this->loadRoutesFrom(__DIR__.'/routes/api.php');

        // Register policies
        Gate::policy(Integration::class, IntegrationPolicy::class);

        // Register event listeners
        Event::listen(IntegrationCreated::class, LogIntegrationActivity::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                ListIntegrationsCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/integration.php',
            'integration'
        );

        $this->app->singleton('integration', function ($app) {
            return new IntegrationManager($app);
        });
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return ['integration'];
    }
}
