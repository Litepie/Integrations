<?php

namespace Litepie\Integration\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Litepie\Integration\Models\Integration;

class IntegrationCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The integration instance.
     */
    public Integration $integration;

    /**
     * Create a new event instance.
     */
    public function __construct(Integration $integration)
    {
        $this->integration = $integration;
    }
}
