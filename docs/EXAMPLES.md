# MAAF Tenant Példák

## Tenant Regisztráció

### Manuális Regisztráció

```php
use MAAF\Tenant\Tenant;
use MAAF\Tenant\TenantResolver;

$resolver = new TenantResolver();

// Register tenant with domain
$resolver->registerTenant(new Tenant(
    id: 'acme-corp',
    name: 'Acme Corporation',
    domain: 'acme.example.com',
    config: [
        'database' => ['name' => 'acme_db'],
        'theme' => 'blue',
    ]
));

// Register tenant with subdomain
$resolver->registerTenant(new Tenant(
    id: 'beta-inc',
    name: 'Beta Inc',
    subdomain: 'beta',
    config: [
        'database' => ['name' => 'beta_db'],
        'theme' => 'green',
    ]
));
```

### Database-ből Betöltés

```php
use MAAF\Tenant\Repository\TenantRepository;
use MAAF\Tenant\TenantService;
use PDO;

$pdo = new PDO('sqlite:database.sqlite');
$repository = new TenantRepository($pdo);
$service = new TenantService($repository, new TenantResolver());

$tenantManager = $service->getTenantManager();
```

## Tenant Detection

### Domain Alapú

```php
// Request: https://acme.example.com/api/users
// Automatikusan felismeri: acme-corp tenant
```

### Subdomain Alapú

```php
// Request: https://beta.example.com/api/users
// Automatikusan felismeri: beta-inc tenant
```

### Paraméter Alapú

```php
// Request: /api/users?tenant_id=acme-corp
// Felismeri: acme-corp tenant
```

### Header Alapú

```php
// Request header: X-Tenant-ID: acme-corp
// Felismeri: acme-corp tenant
```

## Tenant-Aware Routing

```php
use MAAF\Tenant\Routing\TenantRouter;

$tenantRouter = new TenantRouter($baseRouter, $tenantManager);

// Route minden tenant számára
$tenantRouter->addTenantRoute(null, 'GET', '/api/users', [
    UserController::class,
    'index'
]);

// Route csak acme-corp tenant számára
$tenantRouter->addTenantRoute('acme-corp', 'GET', '/api/custom-feature', [
    CustomController::class,
    'index'
]);

// Dispatch
$result = $tenantRouter->dispatch('GET', '/api/users');
```

## Model Resolution

```php
use MAAF\Tenant\Model\TenantModelResolver;

$resolver = new TenantModelResolver($tenantManager);

// Register tenant-specific models
$resolver->registerMapping(
    baseModel: User::class,
    tenantModel: AcmeUser::class,
    tenantId: 'acme-corp'
);

$resolver->registerMapping(
    baseModel: User::class,
    tenantModel: BetaUser::class,
    tenantId: 'beta-inc'
);

// Resolve model for current tenant
$user = $resolver->make(User::class);
// Returns AcmeUser if current tenant is acme-corp
// Returns BetaUser if current tenant is beta-inc
// Returns User if no mapping found
```

## Config Override

```php
use MAAF\Tenant\Config\TenantConfig;

$tenantConfig = new TenantConfig($baseConfig, $tenantManager);

// Set tenant-specific configs
$tenantConfig->setTenantConfig('acme-corp', [
    'database' => [
        'name' => 'acme_db',
        'host' => 'acme-db.example.com',
    ],
    'theme' => 'blue',
    'features' => [
        'advanced_reporting' => true,
    ],
]);

$tenantConfig->setTenantConfig('beta-inc', [
    'database' => [
        'name' => 'beta_db',
    ],
    'theme' => 'green',
]);

// Get config (tenant-specific or base)
$dbName = $tenantConfig->get('database.name');
// Returns 'acme_db' for acme-corp tenant
// Returns 'beta_db' for beta-inc tenant
// Returns base config value for other tenants
```

## Cache Izoláció

```php
use MAAF\Tenant\Cache\TenantCache;

$cache = new TenantCache($tenantManager);

// Set cache (automatically prefixed with tenant ID)
$cache->set('user:123', ['name' => 'John'], 3600);

// Get cache (only accessible for current tenant)
$user = $cache->get('user:123');

// Clear cache for current tenant only
$cache->clear();

// Clear all cache for all tenants
$cache->clearAll();
```

## Queue Izoláció

```php
use MAAF\Tenant\Queue\TenantQueue;
use MAAF\Core\EventBus\EventMessage;

$tenantQueue = new TenantQueue($baseQueueAdapter, $tenantManager);

// Publish message (queue name automatically prefixed)
$message = new EventMessage(
    id: 'msg-123',
    eventName: 'user.created',
    payload: ['id' => 123]
);

$tenantQueue->publish('events', $message);
// Actually publishes to: tenant:acme-corp:events

// Consume from tenant-specific queue
$tenantQueue->consume('events', function ($message) {
    // Process message
});
```

## Teljes Példa

```php
use MAAF\Tenant\Tenant;
use MAAF\Tenant\TenantResolver;
use MAAF\Tenant\TenantManager;
use MAAF\Tenant\Middleware\TenantMiddleware;
use MAAF\Core\Application;

// Setup tenants
$resolver = new TenantResolver();
$resolver->registerTenant(new Tenant(
    id: 'acme-corp',
    name: 'Acme Corporation',
    domain: 'acme.example.com',
    subdomain: 'acme',
    config: ['theme' => 'blue']
));

$tenantManager = new TenantManager($resolver);

// Setup application
$app = new Application(__DIR__);

// Add tenant middleware (auto-detects tenant)
$app->addMiddleware(new TenantMiddleware($tenantManager));

// Use tenant-aware components
$tenantConfig = new TenantConfig($app->getConfig(), $tenantManager);
$tenantCache = new TenantCache($tenantManager);
$tenantQueue = new TenantQueue($queueAdapter, $tenantManager);

$app->run();
```
