# Application Settings Overview

NexusERP centralizes default application configuration in `config/nexus-settings.php`. Each setting group (general, company, financial, unit of measure, notifications) exposes sensible defaults that can be overridden with environment variables or by publishing a custom config file.

## Configuration Structure

```php
return [
    'general' => [...],
    'company' => [...],
    'financial' => [...],
    'uom' => [...],
    'notifications' => [...],
];
```

Key defaults align with the former Spatie settings seeder, ensuring continuity during the migration to config-based storage.

## Access Patterns

### Helper Class

```php
use App\Helpers\SettingsHelper;

SettingsHelper::appName();
SettingsHelper::companyName();
SettingsHelper::defaultCurrency();
SettingsHelper::currencySymbol();
SettingsHelper::formatCurrency(199.99);
```

### Global Helpers

```php
app_name();
company_name();
default_currency();
format_currency(199.99);
is_maintenance_mode();
```

### Resolving Arrays

```php
settings()->general();
settings()->financial();
settings()->notifications();

settings()->get('financial.invoice_prefix'); // "INV-"
```

Returned values are associative arrays, making them straightforward to pass into view models, Livewire components, or actions.

## Customization

1. Override specific keys via `.env`:
   ```env
   APP_DEFAULT_CURRENCY=MYR
   APP_CURRENCY_SYMBOL=RM
   APP_MAINTENANCE_MODE=true
   APP_MAINTENANCE_MESSAGE="Scheduled update in progress"
   ```
2. For more involved changes, publish the config file and adjust values directly.

After changing environment variables or the config file, clear the configuration cache with `php artisan config:clear` (and re-cache with `php artisan config:cache` in production).

## Usage Examples

### Blade Templates

```blade
<h1>{{ app_name() }}</h1>
<p>{{ company_name() }}</p>
<span class="price">{{ format_currency($price) }}</span>

@if (settings()->isMaintenanceMode())
    <div class="alert alert-warning">
        {{ settings()->maintenanceMessage() ?? 'Maintenance in progress.' }}
    </div>
@endif
```

### Actions & Services

```php
use App\Helpers\SettingsHelper;

$invoicePrefix = data_get(SettingsHelper::financial(), 'invoice_prefix', 'INV-');
$adminEmail = SettingsHelper::adminEmail();
```

### Livewire Components

```php
public function render()
{
    return view('livewire.product-list', [
        'defaultCurrency' => SettingsHelper::defaultCurrency(),
        'defaultWeightUnit' => SettingsHelper::defaultWeightUnit(),
    ]);
}
```

## Migration Notes

- The `settings` database table and related seeders have been removed.
- No Spatie `Settings` classes remain; configuration is fully declarative.
- Existing helper APIs remain available to minimize downstream changes.