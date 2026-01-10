<?php

declare(strict_types=1);

namespace MAAF\Tenant\Repository;

use MAAF\Tenant\TenantInterface;
use PDO;

/**
 * Tenant Repository
 * 
 * Tenant repository adatbázis műveletekhez.
 * 
 * @version 1.0.0
 */
final class TenantRepository
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly string $tableName = 'tenants'
    ) {
    }

    /**
     * Find tenant by ID
     * 
     * @param string $id Tenant ID
     * @return TenantInterface|null
     */
    public function findById(string $id): ?TenantInterface
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->tableName} WHERE id = :id AND active = 1");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data !== false ? $this->createTenantFromData($data) : null;
    }

    /**
     * Find tenant by domain
     * 
     * @param string $domain Domain name
     * @return TenantInterface|null
     */
    public function findByDomain(string $domain): ?TenantInterface
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->tableName} WHERE domain = :domain AND active = 1");
        $stmt->execute(['domain' => $domain]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data !== false ? $this->createTenantFromData($data) : null;
    }

    /**
     * Find tenant by subdomain
     * 
     * @param string $subdomain Subdomain name
     * @return TenantInterface|null
     */
    public function findBySubdomain(string $subdomain): ?TenantInterface
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->tableName} WHERE subdomain = :subdomain AND active = 1");
        $stmt->execute(['subdomain' => $subdomain]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data !== false ? $this->createTenantFromData($data) : null;
    }

    /**
     * Get all active tenants
     * 
     * @return array<int, TenantInterface>
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM {$this->tableName} WHERE active = 1");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            fn($data) => $this->createTenantFromData($data),
            $results
        );
    }

    /**
     * Create tenant from database data
     * 
     * @param array<string, mixed> $data Tenant data
     * @return TenantInterface
     */
    private function createTenantFromData(array $data): TenantInterface
    {
        return new \MAAF\Tenant\Tenant(
            id: $data['id'],
            name: $data['name'],
            domain: $data['domain'] ?? null,
            subdomain: $data['subdomain'] ?? null,
            config: json_decode($data['config'] ?? '{}', true),
            active: (bool) ($data['active'] ?? true)
        );
    }
}
