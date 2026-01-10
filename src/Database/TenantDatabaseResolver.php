<?php

declare(strict_types=1);

namespace MAAF\Tenant\Database;

use MAAF\Tenant\TenantManager;
use PDO;

/**
 * Tenant Database Resolver
 * 
 * Tenant-aware database kapcsolat kezelés.
 * 
 * @version 1.0.0
 */
final class TenantDatabaseResolver
{
    /**
     * @var array<string, PDO>
     */
    private array $connections = [];

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $configs = [];

    public function __construct(
        private readonly TenantManager $tenantManager,
        private readonly PDO $defaultConnection
    ) {
    }

    /**
     * Register tenant database configuration
     * 
     * @param string $tenantId Tenant ID
     * @param array<string, mixed> $config Database configuration
     * @return void
     */
    public function registerTenantDatabase(string $tenantId, array $config): void
    {
        $this->configs[$tenantId] = $config;
    }

    /**
     * Get database connection for current tenant
     * 
     * @return PDO
     */
    public function getConnection(): PDO
    {
        $tenantId = $this->tenantManager->getTenantId();

        if ($tenantId === null) {
            return $this->defaultConnection;
        }

        // Return cached connection if exists
        if (isset($this->connections[$tenantId])) {
            return $this->connections[$tenantId];
        }

        // Create new connection if config exists
        if (isset($this->configs[$tenantId])) {
            $config = $this->configs[$tenantId];
            $connection = $this->createConnection($config);
            $this->connections[$tenantId] = $connection;
            return $connection;
        }

        // Fallback to default
        return $this->defaultConnection;
    }

    /**
     * Create database connection from config
     * 
     * @param array<string, mixed> $config Database configuration
     * @return PDO
     */
    private function createConnection(array $config): PDO
    {
        $driver = $config['driver'] ?? 'mysql';
        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? 3306;
        $database = $config['database'] ?? '';
        $username = $config['username'] ?? '';
        $password = $config['password'] ?? '';

        $dsn = "{$driver}:host={$host};port={$port};dbname={$database}";

        return new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
}
