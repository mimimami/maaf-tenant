# MAAF Tenant API Dokumentáció

## Tenant Management

### TenantInterface

```php
interface TenantInterface
{
    public function getId(): string;
    public function getName(): string;
    public function getDomain(): ?string;
    public function getSubdomain(): ?string;
    public function getConfig(): array;
    public function isActive(): bool;
}
```

### TenantResolver

```php
class TenantResolver
{
    public function registerTenant(TenantInterface $tenant): void;
    public function registerResolver(string $name, callable $resolver): void;
    public function resolve(Request $request): ?TenantInterface;
    public function getTenant(string $id): ?TenantInterface;
    public function getTenants(): array;
}
```

### TenantManager

```php
class TenantManager
{
    public function setTenant(?TenantInterface $tenant): void;
    public function getTenant(): ?TenantInterface;
    public function resolveFromRequest(Request $request): ?TenantInterface;
    public function hasTenant(): bool;
    public function getTenantId(): ?string;
    public function clear(): void;
}
```

## Routing

### TenantRouter

```php
class TenantRouter
{
    public function addTenantRoute(?string $tenantId, string|array $method, string $route, callable|array $handler): void;
    public function dispatch(string $method, string $uri): array;
    public function getBaseRouter(): Router;
    public function getTenantRouter(string $tenantId): ?Router;
}
```

## Model Resolution

### TenantModelResolver

```php
class TenantModelResolver
{
    public function registerMapping(string $baseModel, string $tenantModel, ?string $tenantId = null): void;
    public function resolve(string $baseModel): string;
    public function make(string $baseModel, array $arguments = []): object;
}
```

## Config

### TenantConfig

```php
class TenantConfig
{
    public function setTenantConfig(string $tenantId, array $config): void;
    public function get(string $key, mixed $default = null): mixed;
    public function has(string $key): bool;
    public function all(): array;
}
```

## Cache

### TenantCache

```php
class TenantCache
{
    public function get(string $key, mixed $default = null): mixed;
    public function set(string $key, mixed $value, ?int $ttl = null): void;
    public function has(string $key): bool;
    public function delete(string $key): void;
    public function clear(): void;
    public function clearAll(): void;
}
```

## Queue

### TenantQueue

```php
class TenantQueue implements QueueAdapterInterface
{
    public function publish(string $queue, EventMessage $message, array $options = []): ?string;
    public function consume(string $queue, callable $handler, array $options = []): void;
    public function acknowledge(string $messageId): void;
    public function reject(string $messageId, bool $requeue = true): void;
    public function declareQueue(string $queue, array $options = []): void;
    public function declareExchange(string $exchange, string $type = 'topic', array $options = []): void;
    public function bindQueue(string $queue, string $exchange, string $routingKey = ''): void;
}
```
