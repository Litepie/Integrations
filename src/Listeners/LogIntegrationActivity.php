<?php

namespace Litepie\Integration\Listeners;

use Illuminate\Support\Facades\Log;
use Litepie\Integration\Events\IntegrationCreated;

class LogIntegrationActivity
{
    /**
     * Handle the event.
     */
    public function handle(IntegrationCreated $event): void
    {
        Log::info('Integration created', [
            'integration_id' => $event->integration->id,
            'client_id' => $event->integration->client_id,
            'name' => $event->integration->name,
            'user_id' => $event->integration->user_id,
        ]);
    }
}
