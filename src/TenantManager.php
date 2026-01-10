<?php

declare(strict_types=1);

namespace MAAF\Tenant;

use MAAF\Core\Http\Request;

/**
 * Tenant Manager
 * 
 * Tenant kezelő osztály.
 * 
 * @version 1.0.0
 */
final class TenantManager
{
    private ?TenantInterface $currentTenant = null;

    public function __construct(
        private readonly TenantResolver $resolver
    ) {
    }

    /**
     * Set current tenant
     * 
     * @param TenantInterface|null $tenant Tenant instance
     * @return void
     */
    public function setTenant(?TenantInterface $tenant): void
    {
        $this->currentTenant = $tenant;
    }

    /**
     * Get current tenant
     * 
     * @return TenantInterface|null
     */
    public function getTenant(): ?TenantInterface
    {
        return $this->currentTenant;
    }

    /**
     * Resolve and set tenant from request
     * 
     * @param Request $request HTTP request
     * @return TenantInterface|null
     */
    public function resolveFromRequest(Request $request): ?TenantInterface
    {
        $tenant = $this->resolver->resolve($request);
        $this->setTenant($tenant);
        return $tenant;
    }

    /**
     * Check if tenant is set
     * 
     * @return bool
     */
    public function hasTenant(): bool
    {
        return $this->currentTenant !== null;
    }

    /**
     * Get tenant ID
     * 
     * @return string|null
     */
    public function getTenantId(): ?string
    {
        return $this->currentTenant?->getId();
    }

    /**
     * Clear current tenant
     * 
     * @return void
     */
    public function clear(): void
    {
        $this->currentTenant = null;
    }
}
