<?php

namespace Litepie\Integration\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Authenticatable;
use Litepie\Integration\Models\Integration;

class IntegrationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Authenticatable $user): bool
    {
        return true; // Users can view their own integrations
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Authenticatable $user, Integration $integration): bool
    {
        return $user->getKey() === $integration->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Authenticatable $user): bool
    {
        return true; // Authenticated users can create integrations
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Authenticatable $user, Integration $integration): bool
    {
        return $user->getKey() === $integration->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Authenticatable $user, Integration $integration): bool
    {
        return $user->getKey() === $integration->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Authenticatable $user, Integration $integration): bool
    {
        return $user->getKey() === $integration->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Authenticatable $user, Integration $integration): bool
    {
        return $user->getKey() === $integration->user_id;
    }
}
