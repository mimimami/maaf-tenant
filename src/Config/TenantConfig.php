<?php

declare(strict_types=1);

namespace MAAF\Tenant\Config;

use MAAF\Core\Config\ConfigInterface;
use MAAF\Tenant\TenantManager;

/**
 * Tenant Config
 * 
 * Tenant-aware konfiguráció override.
 * 
 * @version 1.0.0
 */
final class TenantConfig
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $tenantConfigs = [];

    public function __construct(
        private readonly ConfigInterface $baseConfig,
        private readonly TenantManager $tenantManager
    ) {
    }

    /**
     * Set tenant configuration
     * 
     * @param string $tenantId Tenant ID
     * @param array<string, mixed> $config Configuration
     * @return void
     */
    public function setTenantConfig(string $tenantId, array $config): void
    {
        $this->tenantConfigs[$tenantId] = $config;
    }

    /**
     * Get configuration value
     * 
     * @param string $key Configuration key (supports dot notation)
     * @param mixed $default Default value
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $tenantId = $this->tenantManager->getTenantId();

        // Try tenant-specific config first
        if ($tenantId !== null && isset($this->tenantConfigs[$tenantId])) {
            $tenantConfig = $this->tenantConfigs[$tenantId];
            $value = $this->getFromArray($tenantConfig, $key);
            
            if ($value !== null) {
                return $value;
            }
        }

        // Fallback to base config
        return $this->baseConfig->get($key, $default);
    }

    /**
     * Check if configuration key exists
     * 
     * @param string $key Configuration key
     * @return bool
     */
    public function has(string $key): bool
    {
        $tenantId = $this->tenantManager->getTenantId();

        // Check tenant-specific config first
        if ($tenantId !== null && isset($this->tenantConfigs[$tenantId])) {
            $tenantConfig = $this->tenantConfigs[$tenantId];
            if ($this->getFromArray($tenantConfig, $key) !== null) {
                return true;
            }
        }

        // Check base config
        return $this->baseConfig->has($key);
    }

    /**
     * Get value from array using dot notation
     * 
     * @param array<string, mixed> $array Array to search
     * @param string $key Key (supports dot notation)
     * @return mixed|null
     */
    private function getFromArray(array $array, string $key): mixed
    {
        $keys = explode('.', $key);
        $value = $array;

        foreach ($keys as $k) {
            if (!is_array($value) || !isset($value[$k])) {
                return null;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Get all configuration
     * 
     * @return array<string, mixed>
     */
    public function all(): array
    {
        $baseConfig = $this->baseConfig->all();
        $tenantId = $this->tenantManager->getTenantId();

        if ($tenantId !== null && isset($this->tenantConfigs[$tenantId])) {
            return array_merge($baseConfig, $this->tenantConfigs[$tenantId]);
        }

        return $baseConfig;
    }
}
