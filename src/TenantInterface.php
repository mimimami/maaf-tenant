<?php

declare(strict_types=1);

namespace MAAF\Tenant;

/**
 * Tenant Interface
 * 
 * Tenant interface.
 * 
 * @version 1.0.0
 */
interface TenantInterface
{
    /**
     * Get tenant ID
     * 
     * @return string
     */
    public function getId(): string;

    /**
     * Get tenant name
     * 
     * @return string
     */
    public function getName(): string;

    /**
     * Get tenant domain
     * 
     * @return string|null
     */
    public function getDomain(): ?string;

    /**
     * Get tenant subdomain
     * 
     * @return string|null
     */
    public function getSubdomain(): ?string;

    /**
     * Get tenant configuration
     * 
     * @return array<string, mixed>
     */
    public function getConfig(): array;

    /**
     * Check if tenant is active
     * 
     * @return bool
     */
    public function isActive(): bool;
}
