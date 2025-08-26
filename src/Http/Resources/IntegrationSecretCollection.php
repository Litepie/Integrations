<?php

namespace Litepie\Integration\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class IntegrationSecretCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     */
    public $collects = IntegrationSecretResource::class;

    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->collection->count(),
                'active_count' => $this->collection->filter(fn($item) => $item->isActive())->count(),
                'expired_count' => $this->collection->filter(fn($item) => $item->isExpired())->count(),
            ],
        ];
    }
}
