<?php

declare(strict_types=1);

namespace MAAF\Tenant\Routing;

use FastRoute\Dispatcher;
use MAAF\Core\Routing\Router;
use MAAF\Tenant\TenantManager;

/**
 * Tenant Router
 * 
 * Tenant-aware routing rendszer.
 * 
 * @version 1.0.0
 */
final class TenantRouter
{
    /**
     * @var array<string, Router>
     */
    private array $tenantRouters = [];

    public function __construct(
        private readonly Router $baseRouter,
        private readonly TenantManager $tenantManager
    ) {
    }

    /**
     * Add route for specific tenant
     * 
     * @param string|null $tenantId Tenant ID (null = all tenants)
     * @param string|array<string> $method HTTP method(s)
     * @param string $route Route pattern
     * @param callable|array $handler Route handler
     * @return void
     */
    public function addTenantRoute(?string $tenantId, string|array $method, string $route, callable|array $handler): void
    {
        if ($tenantId === null) {
            // Add to base router for all tenants
            $this->baseRouter->addRoute($method, $route, $handler);
        } else {
            // Add to tenant-specific router
            if (!isset($this->tenantRouters[$tenantId])) {
                $this->tenantRouters[$tenantId] = new Router();
            }
            $this->tenantRouters[$tenantId]->addRoute($method, $route, $handler);
        }
    }

    /**
     * Dispatch route
     * 
     * @param string $method HTTP method
     * @param string $uri Request URI
     * @return array{status: int, handler?: callable|array, params?: array<string, string>}
     */
    public function dispatch(string $method, string $uri): array
    {
        $tenantId = $this->tenantManager->getTenantId();

        // Try tenant-specific router first
        if ($tenantId !== null && isset($this->tenantRouters[$tenantId])) {
            $result = $this->tenantRouters[$tenantId]->dispatch($method, $uri);
            if ($result['status'] === Dispatcher::FOUND) {
                return $result;
            }
        }

        // Fallback to base router
        return $this->baseRouter->dispatch($method, $uri);
    }

    /**
     * Get base router
     * 
     * @return Router
     */
    public function getBaseRouter(): Router
    {
        return $this->baseRouter;
    }

    /**
     * Get tenant router
     * 
     * @param string $tenantId Tenant ID
     * @return Router|null
     */
    public function getTenantRouter(string $tenantId): ?Router
    {
        return $this->tenantRouters[$tenantId] ?? null;
    }
}
