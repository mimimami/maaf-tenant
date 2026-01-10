<?php

declare(strict_types=1);

namespace MAAF\Tenant\Model;

use MAAF\Tenant\TenantManager;

/**
 * Tenant Model Resolver
 * 
 * Tenant-aware model példányosítás.
 * 
 * @version 1.0.0
 */
final class TenantModelResolver
{
    /**
     * @var array<string, string>
     */
    private array $modelMappings = [];

    public function __construct(
        private readonly TenantManager $tenantManager
    ) {
    }

    /**
     * Register model mapping for tenant
     * 
     * @param string $baseModel Base model class name
     * @param string $tenantModel Tenant-specific model class name
     * @param string|null $tenantId Tenant ID (null = default for all tenants)
     * @return void
     */
    public function registerMapping(string $baseModel, string $tenantModel, ?string $tenantId = null): void
    {
        $key = $tenantId !== null ? "{$tenantId}:{$baseModel}" : "default:{$baseModel}";
        $this->modelMappings[$key] = $tenantModel;
    }

    /**
     * Resolve model class for current tenant
     * 
     * @param string $baseModel Base model class name
     * @return string Model class name
     */
    public function resolve(string $baseModel): string
    {
        $tenantId = $this->tenantManager->getTenantId();

        // Try tenant-specific mapping
        if ($tenantId !== null) {
            $key = "{$tenantId}:{$baseModel}";
            if (isset($this->modelMappings[$key])) {
                return $this->modelMappings[$key];
            }
        }

        // Try default mapping
        $defaultKey = "default:{$baseModel}";
        if (isset($this->modelMappings[$defaultKey])) {
            return $this->modelMappings[$defaultKey];
        }

        // Return base model
        return $baseModel;
    }

    /**
     * Create model instance for current tenant
     * 
     * @param string $baseModel Base model class name
     * @param array<int, mixed> $arguments Constructor arguments
     * @return object Model instance
     */
    public function make(string $baseModel, array $arguments = []): object
    {
        $modelClass = $this->resolve($baseModel);
        
        if (!class_exists($modelClass)) {
            throw new \RuntimeException("Model class '{$modelClass}' not found");
        }

        return new $modelClass(...$arguments);
    }
}
