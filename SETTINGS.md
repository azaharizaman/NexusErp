# Settings Management System

This NexusERP application includes a comprehensive settings management system built with Spatie Laravel Settings and Filament.

## Available Settings Categories

### 1. General Settings (`App\Settings\GeneralSettings`)
- Application name and description
- Logo and favicon
- Timezone and localization settings
- Date/time formats
- Maintenance mode settings

### 2. Company Settings (`App\Settings\CompanySettings`)
- Company information (name, registration number, tax number)
- Contact details (phone, email, website)
- Company address
- Company logo and branding

### 3. Financial Settings (`App\Settings\FinancialSettings`)
- Default currency and formatting
- Tax settings
- Document numbering (invoices, quotes, POs)
- Financial year configuration

### 4. UOM Settings (`App\Settings\UomSettings`)
- Default units for weight, length, volume, area, temperature
- UOM features (compound units, custom units, auto-conversion)
- Display preferences for units

### 5. Notification Settings (`App\Settings\NotificationSettings`)
- Notification methods (email, SMS, browser)
- Notification types and triggers
- Admin contact information
- Notification configuration

## Accessing Settings in Code

### Using the Settings Helper Class

```php
use App\Helpers\SettingsHelper;

// Get settings instances
$general = SettingsHelper::general();
$company = SettingsHelper::company();
$financial = SettingsHelper::financial();
$uom = SettingsHelper::uom();
$notifications = SettingsHelper::notifications();

// Use convenience methods
$appName = SettingsHelper::appName();
$companyName = SettingsHelper::companyName();
$currency = SettingsHelper::defaultCurrency();
$formattedPrice = SettingsHelper::formatCurrency(100.50); // $100.50
```

### Using Global Helper Functions

```php
// Get settings helper instance
$settings = settings();

// Convenience functions
$appName = app_name();
$companyName = company_name();
$currency = default_currency();
$formattedPrice = format_currency(100.50);
$isMaintenanceMode = is_maintenance_mode();
```

### Direct Access to Settings Classes

```php
use App\Settings\GeneralSettings;
use App\Settings\CompanySettings;

// Direct access
$generalSettings = app(GeneralSettings::class);
$appName = $generalSettings->app_name;

$companySettings = app(CompanySettings::class);
$companyName = $companySettings->company_name;
```

## Managing Settings via Filament Admin Panel

The settings can be managed through the Filament admin panel under the "Settings" navigation group:

1. **General Settings** - Application and localization settings
2. **Company Settings** - Company information and branding
3. **Financial Settings** - Currency and financial configuration
4. **Units of Measure** - UOM defaults and preferences
5. **Notifications** - Notification configuration

## Example Usage Patterns

### In Controllers

```php
<?php

namespace App\Http\Controllers;

use App\Helpers\SettingsHelper;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard', [
            'appName' => SettingsHelper::appName(),
            'companyName' => SettingsHelper::companyName(),
            'isMaintenanceMode' => SettingsHelper::isMaintenanceMode(),
        ]);
    }
}
```

### In Blade Templates

```blade
{{-- Using helper functions --}}
<h1>{{ app_name() }}</h1>
<p>Welcome to {{ company_name() }}</p>

{{-- Formatting currency --}}
<span class="price">{{ format_currency(product.price) }}</span>

{{-- Using settings helper --}}
@if(settings()->general()->maintenance_mode)
    <div class="alert alert-warning">
        {{ settings()->general()->maintenance_message }}
    </div>
@endif
```

### In Livewire Components

```php
<?php

namespace App\Livewire;

use App\Helpers\SettingsHelper;
use Livewire\Component;

class ProductList extends Component
{
    public function render()
    {
        return view('livewire.product-list', [
            'defaultCurrency' => SettingsHelper::defaultCurrency(),
            'defaultWeightUnit' => SettingsHelper::defaultWeightUnit(),
        ]);
    }
}
```

### In Actions (Business Logic)

```php
<?php

namespace App\Actions;

use App\Helpers\SettingsHelper;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateInvoice
{
    use AsAction;

    public function handle(array $data)
    {
        $invoicePrefix = SettingsHelper::financial()->invoice_prefix;
        $invoiceNumber = $this->generateInvoiceNumber($invoicePrefix);
        
        // Create invoice with settings-based configuration...
    }
}
```

## Adding New Settings

To add new settings:

1. **Create a new Settings class**:
```php
<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class MyNewSettings extends Settings
{
    public string $my_setting;
    public bool $enable_feature;

    public static function group(): string
    {
        return 'my_new_settings';
    }

    public static function defaults(): array
    {
        return [
            'my_setting' => 'default_value',
            'enable_feature' => false,
        ];
    }
}
```

2. **Create a Filament settings page**:
```bash
php artisan make:filament-settings-page ManageMyNewSettings MyNewSettings --generate
```

3. **Add methods to SettingsHelper** (optional):
```php
public static function myNewSettings(): MyNewSettings
{
    return app(MyNewSettings::class);
}
```

4. **Discover the new settings**:
```bash
php artisan settings:discover
```

## Best Practices

1. **Use the Helper Class**: Always use `SettingsHelper` or global functions for consistent access
2. **Cache Settings**: Settings are automatically cached by Spatie Laravel Settings
3. **Provide Defaults**: Always provide sensible defaults in your settings classes
4. **Validate Input**: Use Filament form validation to ensure data integrity
5. **Document Settings**: Keep this documentation updated when adding new settings
6. **Environment Overrides**: Some settings might need environment-specific overrides during development

## Environment Configuration

Some settings can be overridden by environment variables for development:

```env
# .env file
APP_NAME="NexusERP Development"
APP_TIMEZONE="Asia/Kuala_Lumpur"
DEFAULT_CURRENCY="MYR"
```

These will be used as defaults when the settings are first initialized.