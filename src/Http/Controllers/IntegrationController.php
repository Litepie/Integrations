<?php

namespace Litepie\Integration\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Litepie\Integration\Models\Integration;
use Litepie\Integration\Http\Requests\StoreIntegrationRequest;
use Litepie\Integration\Http\Requests\UpdateIntegrationRequest;
use Litepie\Integration\Http\Resources\IntegrationResource;
use Litepie\Integration\Http\Resources\IntegrationCollection;

class IntegrationController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(config('integration.middleware.api', ['api', 'auth:sanctum']));
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Integration::class);

        $perPage = min(
            $request->input('per_page', config('integration.pagination.per_page', 15)),
            config('integration.pagination.max_per_page', 100)
        );

        $integrations = Integration::forUser(auth()->id())
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'LIKE', '%' . $request->input('search') . '%')
                      ->orWhere('description', 'LIKE', '%' . $request->input('search') . '%');
                });
            })
            ->latest()
            ->paginate($perPage);

        return response()->json(new IntegrationCollection($integrations));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreIntegrationRequest $request): JsonResponse
    {
        $this->authorize('create', Integration::class);

        $integration = Integration::create([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        return response()->json(
            new IntegrationResource($integration),
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Integration $integration): JsonResponse
    {
        $this->authorize('view', $integration);

        return response()->json(new IntegrationResource($integration));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateIntegrationRequest $request, Integration $integration): JsonResponse
    {
        $this->authorize('update', $integration);

        $integration->update($request->validated());

        return response()->json(new IntegrationResource($integration));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Integration $integration): JsonResponse
    {
        $this->authorize('delete', $integration);

        $integration->delete();

        return response()->json(['message' => 'Integration deleted successfully.']);
    }

    /**
     * Activate the integration.
     */
    public function activate(Integration $integration): JsonResponse
    {
        $this->authorize('update', $integration);

        $integration->activate();

        return response()->json([
            'message' => 'Integration activated successfully.',
            'data' => new IntegrationResource($integration->fresh()),
        ]);
    }

    /**
     * Deactivate the integration.
     */
    public function deactivate(Integration $integration): JsonResponse
    {
        $this->authorize('update', $integration);

        $integration->deactivate();

        return response()->json([
            'message' => 'Integration deactivated successfully.',
            'data' => new IntegrationResource($integration->fresh()),
        ]);
    }

    /**
     * Regenerate the client secret.
     */
    public function regenerateSecret(Integration $integration): JsonResponse
    {
        $this->authorize('update', $integration);

        $integration->regenerateSecret();

        return response()->json([
            'message' => 'Client secret regenerated successfully.',
            'client_secret' => $integration->fresh()->client_secret,
        ]);
    }
}
