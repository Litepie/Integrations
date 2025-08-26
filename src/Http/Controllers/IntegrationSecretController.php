<?php

namespace Litepie\Integration\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Litepie\Integration\Models\Integration;
use Litepie\Integration\Models\IntegrationSecret;
use Litepie\Integration\Http\Requests\StoreSecretRequest;
use Litepie\Integration\Http\Requests\UpdateSecretRequest;
use Litepie\Integration\Http\Resources\IntegrationSecretResource;
use Litepie\Integration\Http\Resources\IntegrationSecretCollection;

class IntegrationSecretController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(config('integration.middleware.api', ['api', 'auth:sanctum']));
    }

    /**
     * Display a listing of secrets for an integration.
     */
    public function index(Request $request, Integration $integration): JsonResponse
    {
        $this->authorize('view', $integration);

        $secrets = $integration->secrets()
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->when($request->filled('include_expired'), function ($query) use ($request) {
                if (!$request->boolean('include_expired')) {
                    $query->notExpired();
                }
            }, function ($query) {
                $query->notExpired(); // Default: exclude expired
            })
            ->latest()
            ->get();

        return response()->json(new IntegrationSecretCollection($secrets));
    }

    /**
     * Store a newly created secret.
     */
    public function store(StoreSecretRequest $request, Integration $integration): JsonResponse
    {
        $this->authorize('update', $integration);

        $secret = $integration->createSecret($request->validated());

        return response()->json(
            new IntegrationSecretResource($secret),
            201
        );
    }

    /**
     * Display the specified secret.
     */
    public function show(Integration $integration, IntegrationSecret $secret): JsonResponse
    {
        $this->authorize('view', $integration);

        // Ensure the secret belongs to this integration
        if ($secret->integration_id !== $integration->id) {
            abort(404);
        }

        return response()->json(new IntegrationSecretResource($secret));
    }

    /**
     * Update the specified secret.
     */
    public function update(UpdateSecretRequest $request, Integration $integration, IntegrationSecret $secret): JsonResponse
    {
        $this->authorize('update', $integration);

        // Ensure the secret belongs to this integration
        if ($secret->integration_id !== $integration->id) {
            abort(404);
        }

        $secret->update($request->validated());

        return response()->json(new IntegrationSecretResource($secret));
    }

    /**
     * Remove the specified secret.
     */
    public function destroy(Integration $integration, IntegrationSecret $secret): JsonResponse
    {
        $this->authorize('update', $integration);

        // Ensure the secret belongs to this integration
        if ($secret->integration_id !== $integration->id) {
            abort(404);
        }

        $secret->delete();

        return response()->json(['message' => 'Secret deleted successfully.']);
    }

    /**
     * Activate the secret.
     */
    public function activate(Integration $integration, IntegrationSecret $secret): JsonResponse
    {
        $this->authorize('update', $integration);

        if ($secret->integration_id !== $integration->id) {
            abort(404);
        }

        $secret->activate();

        return response()->json([
            'message' => 'Secret activated successfully.',
            'data' => new IntegrationSecretResource($secret->fresh()),
        ]);
    }

    /**
     * Deactivate the secret.
     */
    public function deactivate(Integration $integration, IntegrationSecret $secret): JsonResponse
    {
        $this->authorize('update', $integration);

        if ($secret->integration_id !== $integration->id) {
            abort(404);
        }

        $secret->deactivate();

        return response()->json([
            'message' => 'Secret deactivated successfully.',
            'data' => new IntegrationSecretResource($secret->fresh()),
        ]);
    }

    /**
     * Set expiration date for the secret.
     */
    public function setExpiration(Request $request, Integration $integration, IntegrationSecret $secret): JsonResponse
    {
        $this->authorize('update', $integration);

        if ($secret->integration_id !== $integration->id) {
            abort(404);
        }

        $request->validate([
            'expires_at' => 'required|date|after:now',
        ]);

        $secret->setExpiration($request->input('expires_at'));

        return response()->json([
            'message' => 'Secret expiration set successfully.',
            'data' => new IntegrationSecretResource($secret->fresh()),
        ]);
    }

    /**
     * Remove expiration date from the secret.
     */
    public function removeExpiration(Integration $integration, IntegrationSecret $secret): JsonResponse
    {
        $this->authorize('update', $integration);

        if ($secret->integration_id !== $integration->id) {
            abort(404);
        }

        $secret->removeExpiration();

        return response()->json([
            'message' => 'Secret expiration removed successfully.',
            'data' => new IntegrationSecretResource($secret->fresh()),
        ]);
    }

    /**
     * Rotate all secrets for an integration.
     */
    public function rotate(Request $request, Integration $integration): JsonResponse
    {
        $this->authorize('update', $integration);

        $request->validate([
            'name' => 'nullable|string|max:255',
        ]);

        $newSecret = $integration->rotateSecrets($request->input('name'));

        return response()->json([
            'message' => 'Secrets rotated successfully.',
            'data' => new IntegrationSecretResource($newSecret),
        ]);
    }

    /**
     * Clean up expired secrets.
     */
    public function cleanup(Integration $integration): JsonResponse
    {
        $this->authorize('update', $integration);

        $deletedCount = $integration->cleanupExpiredSecrets();

        return response()->json([
            'message' => "Cleaned up {$deletedCount} expired secrets.",
            'deleted_count' => $deletedCount,
        ]);
    }
}
