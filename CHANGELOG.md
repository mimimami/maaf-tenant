# Changelog

## [1.0.0] - 2024-01-XX

### Added

- ✅ **Tenant Management**
  - `TenantInterface` - Tenant interface
  - `Tenant` - Tenant implementáció
  - `TenantResolver` - Tenant felismerés
  - `TenantManager` - Tenant kezelés
  - `TenantRepository` - Tenant repository

- ✅ **Tenant Aware Routing**
  - `TenantRouter` - Tenant-aware routing rendszer
  - Tenant-specifikus route-ok
  - Fallback base router

- ✅ **Model Resolution**
  - `TenantModelResolver` - Tenant-aware model példányosítás
  - Tenant-specifikus model mapping

- ✅ **Config Override**
  - `TenantConfig` - Tenant-aware konfiguráció
  - Tenant-specifikus config override
  - Fallback base config

- ✅ **Cache Izoláció**
  - `TenantCache` - Tenant-aware cache
  - Automatikus tenant prefix
  - Tenant-specifikus cache törlés

- ✅ **Queue Izoláció**
  - `TenantQueue` - Tenant-aware queue adapter
  - Automatikus tenant prefix queue nevekhez
  - Tenant-specifikus routing keys

- ✅ **Tenant Detection**
  - Domain alapú detection
  - Subdomain alapú detection
  - Parameter alapú detection
  - Header alapú detection
  - Custom resolver támogatás

- ✅ **Middleware**
  - `TenantMiddleware` - Automatikus tenant detection

### Changed
- N/A (első kiadás)

### Fixed
- N/A (első kiadás)
