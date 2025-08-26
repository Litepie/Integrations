<?php

namespace Litepie\Integration\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IntegrationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'client_id' => $this->client_id,
            'client_secret' => $this->when(
                $request->user()?->id === $this->user_id,
                $this->client_secret
            ),
            'redirect_uris' => $this->redirect_uris,
            'status' => $this->status,
            'metadata' => $this->metadata,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_active' => $this->isActive(),
            'is_inactive' => $this->isInactive(),
        ];
    }
}
