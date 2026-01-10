<?php

declare(strict_types=1);

namespace MAAF\Tenant\Middleware;

use MAAF\Core\Http\MiddlewareInterface;
use MAAF\Core\Http\Request;
use MAAF\Core\Http\Response;
use MAAF\Tenant\TenantManager;

/**
 * Tenant Middleware
 * 
 * Tenant detection és beállítás middleware.
 * 
 * @version 1.0.0
 */
final class TenantMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly TenantManager $tenantManager
    ) {
    }

    public function handle(Request $request, callable $next): Response
    {
        // Resolve tenant from request
        $tenant = $this->tenantManager->resolveFromRequest($request);

        if ($tenant === null) {
            // No tenant found - continue with default behavior
            return $next($request);
        }

        if (!$tenant->isActive()) {
            return Response::json([
                'error' => 'Forbidden',
                'message' => 'Tenant is not active',
            ], 403);
        }

        // Continue with tenant set
        return $next($request);
    }
}
