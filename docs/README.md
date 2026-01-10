# MAAF Tenant Dokumentáció

## Áttekintés

MAAF Tenant egy multi-tenant támogatás tenant-aware routinggal, model resolutionnel, config override-tel, cache és queue izolációval.

## Funkciók

- ✅ **Tenant Aware Routing** - Tenant-specifikus route-ok
- ✅ **Model Resolution** - Tenant-aware model példányosítás
- ✅ **Config Override** - Tenant-specifikus konfiguráció
- ✅ **Cache Izoláció** - Tenant-specifikus cache
- ✅ **Queue Izoláció** - Tenant-specifikus queue-k
- ✅ **Domain Detection** - Domain alapú tenant felismerés
- ✅ **Parameter Detection** - Paraméter alapú tenant felismerés

## Telepítés

```bash
composer require maaf/tenant
```

## Használat

### Tenant Regisztráció

```php
use MAAF\Tenant\Tenant;
use MAAF\Tenant\TenantResolver;

$resolver = new TenantResolver();

// Register tenants
$resolver->registerTenant(new Tenant(
    id: 'tenant-1',
    name: 'Tenant 1',
    domain: 'tenant1.example.com',
    subdomain: 'tenant1',
    config: ['theme' => 'blue']
));

$resolver->registerTenant(new Tenant(
    id: 'tenant-2',
    name: 'Tenant 2',
    domain: 'tenant2.example.com',
    subdomain: 'tenant2',
    config: ['theme' => 'green']
));
```

### Tenant Detection

```php
use MAAF\Tenant\TenantManager;
use MAAF\Tenant\TenantResolver;
use MAAF\Core\Http\Request;

$resolver = new TenantResolver();
$manager = new TenantManager($resolver);

// Resolve tenant from request
$request = Request::fromGlobals();
$tenant = $manager->resolveFromRequest($request);

if ($tenant !== null) {
    echo "Current tenant: " . $tenant->getName();
}
```

### Tenant-Aware Routing

```php
use MAAF\Tenant\Routing\TenantRouter;
use MAAF\Tenant\TenantManager;

$tenantRouter = new TenantRouter($baseRouter, $tenantManager);

// Route for all tenants
$tenantRouter->addTenantRoute(null, 'GET', '/api/users', [UserController::class, 'index']);

// Route for specific tenant
$tenantRouter->addTenantRoute('tenant-1', 'GET', '/api/custom', [CustomController::class, 'index']);
```

### Model Resolution

```php
use MAAF\Tenant\Model\TenantModelResolver;

$resolver = new TenantModelResolver($tenantManager);

// Register tenant-specific model
$resolver->registerMapping(
    baseModel: User::class,
    tenantModel: Tenant1User::class,
    tenantId: 'tenant-1'
);

// Resolve model for current tenant
$modelClass = $resolver->resolve(User::class);
$user = $resolver->make(User::class);
```

### Config Override

```php
use MAAF\Tenant\Config\TenantConfig;

$tenantConfig = new TenantConfig($baseConfig, $tenantManager);

// Set tenant-specific config
$tenantConfig->setTenantConfig('tenant-1', [
    'database' => [
        'name' => 'tenant1_db',
    ],
    'theme' => 'blue',
]);

// Get config (tenant-specific or base)
$dbName = $tenantConfig->get('database.name'); // Returns tenant-specific if set
```

### Cache Izoláció

```php
use MAAF\Tenant\Cache\TenantCache;

$cache = new TenantCache($tenantManager);

// Cache is automatically isolated per tenant
$cache->set('key', 'value');
$value = $cache->get('key'); // Only accessible for current tenant

// Clear cache for current tenant
$cache->clear();
```

### Queue Izoláció

```php
use MAAF\Tenant\Queue\TenantQueue;

$tenantQueue = new TenantQueue($baseQueueAdapter, $tenantManager);

// Queue names are automatically prefixed with tenant ID
$tenantQueue->publish('events', $message);
// Actually publishes to: tenant:tenant-1:events
```

### Middleware Használat

```php
use MAAF\Tenant\Middleware\TenantMiddleware;

// Auto-detect tenant from request
$app->addMiddleware(new TenantMiddleware($tenantManager));
```

## További információk

- [API Dokumentáció](api.md)
- [Példák](examples.md)
- [Best Practices](best-practices.md)
