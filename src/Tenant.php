<?php

declare(strict_types=1);

namespace MAAF\Tenant;

/**
 * Tenant Implementation
 * 
 * Tenant implementáció.
 * 
 * @version 1.0.0
 */
final class Tenant implements TenantInterface
{
    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly ?string $domain = null,
        private readonly ?string $subdomain = null,
        private readonly array $config = [],
        private readonly bool $active = true
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function getSubdomain(): ?string
    {
        return $this->subdomain;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function isActive(): bool
    {
        return $this->active;
    }
}
