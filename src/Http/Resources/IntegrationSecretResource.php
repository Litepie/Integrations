<?php

namespace Litepie\Integration\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IntegrationSecretResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'integration_id' => $this->integration_id,
            'name' => $this->name,
            'secret_key' => $this->when(
                $request->user()?->id === $this->integration->user_id,
                $this->secret_key
            ),
            'masked_secret' => $this->masked_secret,
            'status' => $this->status,
            'last_used_at' => $this->last_used_at,
            'expires_at' => $this->expires_at,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_active' => $this->isActive(),
            'is_expired' => $this->isExpired(),
            'is_valid' => $this->isValid(),
        ];
    }
}
