<?php

use Illuminate\Support\Facades\Route;
use Litepie\Integration\Http\Controllers\IntegrationController;
use Litepie\Integration\Http\Controllers\IntegrationSecretController;

Route::group([
    'prefix' => config('integration.route_prefix.api', 'api/integrations'),
    'middleware' => config('integration.middleware.api', ['api', 'auth:sanctum']),
], function () {
    
    // Standard CRUD routes for integrations
    Route::apiResource('integrations', IntegrationController::class)
         ->parameters(['integrations' => 'integration']);

    // Integration action routes
    Route::post('integrations/{integration}/activate', [IntegrationController::class, 'activate'])
         ->name('integrations.activate');
         
    Route::post('integrations/{integration}/deactivate', [IntegrationController::class, 'deactivate'])
         ->name('integrations.deactivate');
         
    Route::post('integrations/{integration}/regenerate-secret', [IntegrationController::class, 'regenerateSecret'])
         ->name('integrations.regenerate-secret');

    // Secret management routes
    Route::get('integrations/{integration}/secrets', [IntegrationSecretController::class, 'index'])
         ->name('integrations.secrets.index');
         
    Route::post('integrations/{integration}/secrets', [IntegrationSecretController::class, 'store'])
         ->name('integrations.secrets.store');
         
    Route::get('integrations/{integration}/secrets/{secret}', [IntegrationSecretController::class, 'show'])
         ->name('integrations.secrets.show');
         
    Route::put('integrations/{integration}/secrets/{secret}', [IntegrationSecretController::class, 'update'])
         ->name('integrations.secrets.update');
         
    Route::delete('integrations/{integration}/secrets/{secret}', [IntegrationSecretController::class, 'destroy'])
         ->name('integrations.secrets.destroy');

    // Secret action routes
    Route::post('integrations/{integration}/secrets/{secret}/activate', [IntegrationSecretController::class, 'activate'])
         ->name('integrations.secrets.activate');
         
    Route::post('integrations/{integration}/secrets/{secret}/deactivate', [IntegrationSecretController::class, 'deactivate'])
         ->name('integrations.secrets.deactivate');
         
    Route::post('integrations/{integration}/secrets/{secret}/set-expiration', [IntegrationSecretController::class, 'setExpiration'])
         ->name('integrations.secrets.set-expiration');
         
    Route::post('integrations/{integration}/secrets/{secret}/remove-expiration', [IntegrationSecretController::class, 'removeExpiration'])
         ->name('integrations.secrets.remove-expiration');

    // Bulk secret operations
    Route::post('integrations/{integration}/secrets/rotate', [IntegrationSecretController::class, 'rotate'])
         ->name('integrations.secrets.rotate');
         
    Route::post('integrations/{integration}/secrets/cleanup', [IntegrationSecretController::class, 'cleanup'])
         ->name('integrations.secrets.cleanup');
});
