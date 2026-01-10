<?php

declare(strict_types=1);

namespace MAAF\Tenant;

use MAAF\Tenant\Repository\TenantRepository;

/**
 * Tenant Service
 * 
 * Tenant szolgáltatás osztály.
 * 
 * @version 1.0.0
 */
final class TenantService
{
    public function __construct(
        private readonly TenantRepository $repository,
        private readonly TenantResolver $resolver
    ) {
        $this->loadTenants();
    }

    /**
     * Load tenants from repository
     * 
     * @return void
     */
    private function loadTenants(): void
    {
        $tenants = $this->repository->findAll();
        
        foreach ($tenants as $tenant) {
            $this->resolver->registerTenant($tenant);
        }
    }

    /**
     * Get tenant manager
     * 
     * @return TenantManager
     */
    public function getTenantManager(): TenantManager
    {
        return new TenantManager($this->resolver);
    }
}
