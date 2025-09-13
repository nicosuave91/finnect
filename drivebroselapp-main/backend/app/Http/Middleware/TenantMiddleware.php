<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get tenant from subdomain or header
        $tenant = $this->resolveTenant($request);

        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        if (!$tenant->is_active) {
            return response()->json(['error' => 'Tenant is inactive'], 403);
        }

        // Set tenant context
        app()->instance('tenant', $tenant);
        
        // Switch to tenant database
        $this->switchToTenantDatabase($tenant);

        // Add tenant context to request
        $request->merge(['tenant_id' => $tenant->id]);

        return $next($request);
    }

    /**
     * Resolve tenant from request.
     */
    private function resolveTenant(Request $request): ?Tenant
    {
        // Try to get tenant from subdomain
        $host = $request->getHost();
        $subdomain = explode('.', $host)[0];
        
        if ($subdomain !== 'www' && $subdomain !== 'api') {
            return Tenant::where('domain', $subdomain)->first();
        }

        // Try to get tenant from header
        $tenantId = $request->header('X-Tenant-ID');
        if ($tenantId) {
            return Tenant::find($tenantId);
        }

        // Try to get tenant from query parameter
        $tenantId = $request->query('tenant_id');
        if ($tenantId) {
            return Tenant::find($tenantId);
        }

        return null;
    }

    /**
     * Switch to tenant database.
     */
    private function switchToTenantDatabase(Tenant $tenant): void
    {
        $config = config('database.connections.tenant');
        $config['database'] = $tenant->database;
        
        config(['database.connections.tenant' => $config]);
        
        DB::purge('tenant');
        DB::setDefaultConnection('tenant');
    }
}