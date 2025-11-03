# Settings Architecture (Config-Driven)

## Overview

NexusERP now sources application settings from configuration instead of the Spatie settings package. All defaults live in `config/nexus-settings.php`, grouped by concern:

- `general`
- `company`
- `financial`
- `uom`
- `notifications`

The structure mirrors the prior Spatie configuration to ease migration and to keep helper APIs intact.

## Access Layer

### SettingsHelper

`App\Helpers\SettingsHelper` exposes both instance and static methods. The helper resolves values from the config file at runtime, provides derived convenience methods (e.g., `appName()`, `currencySymbol()`), and surfaces raw config arrays when necessary.

### Global Helpers

`app/helpers.php` continues to register global helpers (`settings()`, `app_name()`, etc.). The `settings()` helper returns the `SettingsHelper` singleton, preserving existing call-sites while delivering config-backed values.

## Customization Flow

1. **Environment variables** – each notable setting has an `APP_*` (or `COMPANY_*`) override. Update `.env`, then run `php artisan config:clear`.
2. **Config override** – publish or copy `config/nexus-settings.php` and adjust arrays directly if you need more complex logic.

## Business Logic Integration

- `App\Actions\Utils\FormatCurrency` consumes `SettingsHelper::financial()` yet still honours per-call currency overrides.
- Any domain action or service should consume the helper instead of reading `config()` directly to keep behaviour consistent.

## Migration Impact

- Removed: Spatie settings classes, seeders, and composer dependencies.
- Added: `config/nexus-settings.php` for centralized defaults.
- Docs updated: `SETTINGS.md`, architectural decision log, and progress checklist reflect the new approach.

## Next Steps

- Revisit module-specific settings requirements once the Purchase Module design is finalised.
- Add tests around helper accessors if future work introduces dynamic mutation or caching.