<?php

namespace Litepie\Integration\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Litepie\Integration\Models\Integration;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CheckIntegrationRestrictions
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$permissions): SymfonyResponse
    {
        $integration = $this->getIntegration($request);
        
        if (!$integration) {
            return $this->unauthorizedResponse('Invalid integration credentials');
        }
        
        // Check if integration is active
        if (!$integration->isActive()) {
            return $this->unauthorizedResponse('Integration is inactive');
        }
        
        // Check role-based access
        if (!$this->checkRoleAccess($integration, $request)) {
            return $this->forbiddenResponse('Insufficient role permissions');
        }
        
        // Check permissions
        if (!empty($permissions) && !$this->checkPermissions($integration, $permissions)) {
            return $this->forbiddenResponse('Insufficient permissions');
        }
        
        // Check IP restrictions
        if (!$this->checkIpRestrictions($integration, $request)) {
            return $this->forbiddenResponse('IP address not allowed');
        }
        
        // Check geographic restrictions
        if (!$this->checkGeographicRestrictions($integration, $request)) {
            return $this->forbiddenResponse('Geographic location not allowed');
        }
        
        // Check time restrictions
        if (!$this->checkTimeRestrictions($integration)) {
            return $this->forbiddenResponse('Access not allowed at this time');
        }
        
        // Add integration to request for use in controllers
        $request->merge(['integration' => $integration]);
        
        return $next($request);
    }
    
    /**
     * Get integration from request.
     */
    protected function getIntegration(Request $request): ?Integration
    {
        $clientId = $request->header('X-Client-ID') ?? $request->input('client_id');
        $clientSecret = $request->header('X-Client-Secret') ?? $request->input('client_secret');
        
        if (!$clientId || !$clientSecret) {
            return null;
        }
        
        $integration = Integration::where('client_id', $clientId)->first();
        
        if (!$integration) {
            return null;
        }
        
        // Validate either legacy client_secret or new multiple secrets
        if ($integration->client_secret === $clientSecret || $integration->validateSecret($clientSecret)) {
            return $integration;
        }
        
        return null;
    }
    
    /**
     * Check role-based access.
     */
    protected function checkRoleAccess(Integration $integration, Request $request): bool
    {
        // If no role is set, allow access
        if (!$integration->role) {
            return true;
        }
        
        // Define role hierarchy
        $roleHierarchy = [
            'guest' => 1,
            'user' => 2,
            'service' => 3,
            'readonly' => 3,
            'admin' => 4,
        ];
        
        $requiredRole = $this->getRequiredRoleFromRoute($request);
        
        if (!$requiredRole) {
            return true;
        }
        
        $integrationRoleLevel = $roleHierarchy[$integration->role] ?? 0;
        $requiredRoleLevel = $roleHierarchy[$requiredRole] ?? 0;
        
        return $integrationRoleLevel >= $requiredRoleLevel;
    }
    
    /**
     * Check permissions.
     */
    protected function checkPermissions(Integration $integration, array $requiredPermissions): bool
    {
        foreach ($requiredPermissions as $permission) {
            if (str_contains($permission, ':')) {
                [$resource, $action] = explode(':', $permission, 2);
                if (!$integration->hasPermission($resource, $action)) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Check IP restrictions.
     */
    protected function checkIpRestrictions(Integration $integration, Request $request): bool
    {
        $clientIp = $request->ip();
        return $integration->isIpAllowed($clientIp);
    }
    
    /**
     * Check geographic restrictions.
     */
    protected function checkGeographicRestrictions(Integration $integration, Request $request): bool
    {
        $countryCode = $this->getCountryFromRequest($request);
        
        if (!$countryCode) {
            return true; // If we can't determine country, allow access
        }
        
        return $integration->isCountryAllowed($countryCode);
    }
    
    /**
     * Check time restrictions.
     */
    protected function checkTimeRestrictions(Integration $integration): bool
    {
        return $integration->isTimeAllowed();
    }
    
    /**
     * Get required role from route.
     */
    protected function getRequiredRoleFromRoute(Request $request): ?string
    {
        $route = $request->route();
        
        if (!$route) {
            return null;
        }
        
        // Check route action for role requirements
        $action = $route->getAction();
        
        if (isset($action['role'])) {
            return $action['role'];
        }
        
        // Check middleware parameters
        $middleware = $route->gatherMiddleware();
        
        foreach ($middleware as $m) {
            if (str_starts_with($m, 'integration.role:')) {
                return substr($m, 17);
            }
        }
        
        return null;
    }
    
    /**
     * Get country code from request.
     */
    protected function getCountryFromRequest(Request $request): ?string
    {
        // Try to get country from header (if set by load balancer/CDN)
        $country = $request->header('X-Country-Code') ?? 
                  $request->header('CF-IPCountry') ?? // Cloudflare
                  $request->header('X-Forwarded-Country');
        
        if ($country) {
            return strtoupper($country);
        }
        
        // You could integrate with a GeoIP service here
        // For now, return null to allow access
        return null;
    }
    
    /**
     * Return unauthorized response.
     */
    protected function unauthorizedResponse(string $message): Response
    {
        return response()->json([
            'error' => 'unauthorized',
            'message' => $message,
        ], 401);
    }
    
    /**
     * Return forbidden response.
     */
    protected function forbiddenResponse(string $message): Response
    {
        return response()->json([
            'error' => 'forbidden',
            'message' => $message,
        ], 403);
    }
}
