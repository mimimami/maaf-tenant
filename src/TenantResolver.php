<?php

declare(strict_types=1);

namespace MAAF\Tenant;

use MAAF\Core\Http\Request;

/**
 * Tenant Resolver
 * 
 * Tenant felismerés domain és paraméter alapján.
 * 
 * @version 1.0.0
 */
final class TenantResolver
{
    /**
     * @var array<string, TenantInterface>
     */
    private array $tenants = [];

    /**
     * @var array<string, callable>
     */
    private array $resolvers = [];

    public function __construct()
    {
        $this->registerDefaultResolvers();
    }

    /**
     * Register tenant
     * 
     * @param TenantInterface $tenant Tenant instance
     * @return void
     */
    public function registerTenant(TenantInterface $tenant): void
    {
        $this->tenants[$tenant->getId()] = $tenant;
    }

    /**
     * Register custom resolver
     * 
     * @param string $name Resolver name
     * @param callable $resolver Resolver callback
     * @return void
     */
    public function registerResolver(string $name, callable $resolver): void
    {
        $this->resolvers[$name] = $resolver;
    }

    /**
     * Resolve tenant from request
     * 
     * @param Request $request HTTP request
     * @return TenantInterface|null
     */
    public function resolve(Request $request): ?TenantInterface
    {
        // Try domain-based resolution
        $tenant = $this->resolveByDomain($request);
        if ($tenant !== null) {
            return $tenant;
        }

        // Try subdomain-based resolution
        $tenant = $this->resolveBySubdomain($request);
        if ($tenant !== null) {
            return $tenant;
        }

        // Try parameter-based resolution
        $tenant = $this->resolveByParameter($request);
        if ($tenant !== null) {
            return $tenant;
        }

        // Try header-based resolution
        $tenant = $this->resolveByHeader($request);
        if ($tenant !== null) {
            return $tenant;
        }

        // Try custom resolvers
        foreach ($this->resolvers as $resolver) {
            $tenant = $resolver($request);
            if ($tenant !== null) {
                return $tenant;
            }
        }

        return null;
    }

    /**
     * Resolve tenant by domain
     * 
     * @param Request $request HTTP request
     * @return TenantInterface|null
     */
    private function resolveByDomain(Request $request): ?TenantInterface
    {
        $host = $request->server['HTTP_HOST'] ?? '';
        
        foreach ($this->tenants as $tenant) {
            if ($tenant->getDomain() === $host) {
                return $tenant;
            }
        }

        return null;
    }

    /**
     * Resolve tenant by subdomain
     * 
     * @param Request $request HTTP request
     * @return TenantInterface|null
     */
    private function resolveBySubdomain(Request $request): ?TenantInterface
    {
        $host = $request->server['HTTP_HOST'] ?? '';
        $parts = explode('.', $host);

        if (count($parts) < 3) {
            return null;
        }

        $subdomain = $parts[0];

        foreach ($this->tenants as $tenant) {
            if ($tenant->getSubdomain() === $subdomain) {
                return $tenant;
            }
        }

        return null;
    }

    /**
     * Resolve tenant by parameter
     * 
     * @param Request $request HTTP request
     * @return TenantInterface|null
     */
    private function resolveByParameter(Request $request): ?TenantInterface
    {
        $tenantId = $request->getQuery('tenant_id') 
            ?? $request->getPost('tenant_id')
            ?? $request->get('tenant_id');

        if ($tenantId === null) {
            return null;
        }

        return $this->tenants[$tenantId] ?? null;
    }

    /**
     * Resolve tenant by header
     * 
     * @param Request $request HTTP request
     * @return TenantInterface|null
     */
    private function resolveByHeader(Request $request): ?TenantInterface
    {
        $tenantId = $request->getHeader('X-Tenant-ID');

        if ($tenantId === null) {
            return null;
        }

        return $this->tenants[$tenantId] ?? null;
    }

    /**
     * Register default resolvers
     * 
     * @return void
     */
    private function registerDefaultResolvers(): void
    {
        // Default resolvers are implemented as methods
    }

    /**
     * Get tenant by ID
     * 
     * @param string $id Tenant ID
     * @return TenantInterface|null
     */
    public function getTenant(string $id): ?TenantInterface
    {
        return $this->tenants[$id] ?? null;
    }

    /**
     * Get all tenants
     * 
     * @return array<string, TenantInterface>
     */
    public function getTenants(): array
    {
        return $this->tenants;
    }
}
