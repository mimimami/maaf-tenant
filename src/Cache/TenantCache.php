<?php

declare(strict_types=1);

namespace MAAF\Tenant\Cache;

use MAAF\Tenant\TenantManager;

/**
 * Tenant Cache
 * 
 * Tenant-aware cache izoláció.
 * 
 * @version 1.0.0
 */
final class TenantCache
{
    /**
     * @var array<string, mixed>
     */
    private array $cache = [];

    public function __construct(
        private readonly TenantManager $tenantManager
    ) {
    }

    /**
     * Get cache key with tenant prefix
     * 
     * @param string $key Cache key
     * @return string
     */
    private function getTenantKey(string $key): string
    {
        $tenantId = $this->tenantManager->getTenantId() ?? 'default';
        return "tenant:{$tenantId}:{$key}";
    }

    /**
     * Get value from cache
     * 
     * @param string $key Cache key
     * @param mixed $default Default value
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $tenantKey = $this->getTenantKey($key);
        return $this->cache[$tenantKey] ?? $default;
    }

    /**
     * Set value in cache
     * 
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int|null $ttl Time to live in seconds (null = no expiration)
     * @return void
     */
    public function set(string $key, mixed $value, ?int $ttl = null): void
    {
        $tenantKey = $this->getTenantKey($key);
        $this->cache[$tenantKey] = [
            'value' => $value,
            'expires_at' => $ttl !== null ? time() + $ttl : null,
        ];
    }

    /**
     * Check if key exists in cache
     * 
     * @param string $key Cache key
     * @return bool
     */
    public function has(string $key): bool
    {
        $tenantKey = $this->getTenantKey($key);
        
        if (!isset($this->cache[$tenantKey])) {
            return false;
        }

        $item = $this->cache[$tenantKey];
        
        // Check expiration
        if ($item['expires_at'] !== null && $item['expires_at'] < time()) {
            unset($this->cache[$tenantKey]);
            return false;
        }

        return true;
    }

    /**
     * Delete value from cache
     * 
     * @param string $key Cache key
     * @return void
     */
    public function delete(string $key): void
    {
        $tenantKey = $this->getTenantKey($key);
        unset($this->cache[$tenantKey]);
    }

    /**
     * Clear all cache for current tenant
     * 
     * @return void
     */
    public function clear(): void
    {
        $tenantId = $this->tenantManager->getTenantId() ?? 'default';
        $prefix = "tenant:{$tenantId}:";

        foreach (array_keys($this->cache) as $key) {
            if (str_starts_with($key, $prefix)) {
                unset($this->cache[$key]);
            }
        }
    }

    /**
     * Clear all cache for all tenants
     * 
     * @return void
     */
    public function clearAll(): void
    {
        $this->cache = [];
    }
}
