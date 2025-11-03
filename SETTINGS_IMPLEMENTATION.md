# Settings Implementation Documentation

## Overview

This document describes the implementation of a comprehensive settings management system using the Spatie Laravel Settings plugin integrated with Filament administration interface.

## Architecture

### Settings Classes

The system includes 5 main settings groups, each implemented as a separate class:

#### 1. GeneralSettings (`app/Settings/GeneralSettings.php`)
Manages application-wide settings:
- Application name and description
- Environment configuration
- Default timezone and locale
- Items per page for listings
- Debug and maintenance mode settings

#### 2. CompanySettings (`app/Settings/CompanySettings.php`)
Manages company-specific information:
- Company identity (name, email, phone)
- Physical addresses
- Registration details
- Legal information
- Business hours

#### 3. FinancialSettings (`app/Settings/FinancialSettings.php`)
Manages financial and currency settings:
- Currency configuration (symbol, position, formatting)
- Tax settings
- Pricing preferences
- Invoice/document numbering
- Financial year configuration

#### 4. UomSettings (`app/Settings/UomSettings.php`)
Manages Unit of Measurement settings:
- Default units for weight, length, volume, area, temperature
- Conversion settings and precision
- Display preferences for unit codes/names
- Custom and compound unit options

#### 5. NotificationSettings (`app/Settings/NotificationSettings.php`)
Manages notification preferences:
- Email, SMS, and browser notification toggles
- Specific notification triggers
- Admin contact information
- Notification frequency and batching

### Filament Administration Pages

Each settings group has a dedicated Filament page for management:

- **General Settings**: `/nexus/manage-general-settings`
- **Company Settings**: `/nexus/manage-company-settings`
- **Financial Settings**: `/nexus/manage-financial-settings`
- **UOM Settings**: `/nexus/manage-uom-settings`
- **Notification Settings**: `/nexus/manage-notification-settings`

All pages are organized under the "Settings" navigation group in the Filament admin panel.

### Helper Classes and Functions

#### SettingsHelper Class (`app/Helpers/SettingsHelper.php`)
Provides convenient static methods for accessing settings:

```php
// Application settings
SettingsHelper::appName()
SettingsHelper::appDescription()
SettingsHelper::timezone()

// Currency and formatting
SettingsHelper::defaultCurrency()
SettingsHelper::currencySymbol()
SettingsHelper::formatCurrency($amount)

// Company information
SettingsHelper::companyName()

// UOM defaults
SettingsHelper::defaultWeightUnit()
SettingsHelper::defaultLengthUnit()
SettingsHelper::defaultVolumeUnit()

// Notifications
SettingsHelper::emailNotificationsEnabled()
```

#### Global Helper Functions (`app/helpers.php`)
Provides global functions for common settings access:

```php
app_name()              // Get application name
company_name()          // Get company name
default_currency()      // Get default currency
format_currency($amount) // Format currency amount
is_maintenance_mode()   // Check maintenance mode
```

## Database Structure

Settings are stored in a single `settings` table with the following structure:
- `group`: Settings category (general, company, financial, uom, notifications)
- `name`: Setting property name
- `payload`: JSON-encoded setting value
- `locked`: Whether the setting can be modified
- Timestamps for tracking changes

## Installation and Setup

### 1. Package Installation
```bash
composer require filament/spatie-laravel-settings-plugin:"^4.0" -W
```

### 2. Configuration
The settings are automatically registered via `SettingsServiceProvider` and can be accessed through:
- Dependency injection
- SettingsHelper class
- Global helper functions

### 3. Database Migration
Run the migrations to create the settings table:
```bash
php artisan migrate
```

### 4. Initial Data Population
Settings are populated with sensible defaults. The system includes 58 individual settings across all groups.

## Usage Examples

### Accessing Settings in Code

```php
// Via Helper Class
$currencySymbol = SettingsHelper::currencySymbol();
$formattedPrice = SettingsHelper::formatCurrency(199.99);

// Via Global Functions
$appName = app_name();
$companyName = company_name();

// Direct Access
$settings = app(GeneralSettings::class);
$itemsPerPage = $settings->items_per_page;
```

### Modifying Settings via Filament
1. Access the admin panel at `/nexus`
2. Navigate to the Settings section
3. Choose the appropriate settings category
4. Modify values using the form interface
5. Save changes

### Programmatic Updates

```php
// Update via settings instance
$general = app(GeneralSettings::class);
$general->app_name = 'New App Name';
$general->save();

// Update via SettingsHelper
$financial = SettingsHelper::financial();
$financial->default_currency = 'EUR';
$financial->save();
```

## Features

### Form Validation
All settings forms include appropriate validation rules for data integrity.

### Organized Interface
Settings are logically grouped and presented with clear sections and descriptions.

### Type Safety
All settings properties are strongly typed in PHP classes.

### Default Values
Each settings class provides sensible default values for all properties.

### Caching
The Spatie Laravel Settings package provides automatic caching for optimal performance.

### Integration Ready
The system integrates seamlessly with other Laravel and Filament components.

## Navigation

The settings pages are accessible through the Filament admin panel navigation:
- **Settings** (main group)
  - General Settings
  - Company Settings
  - Financial Settings
  - UOM Settings
  - Notification Settings

## Security

- Settings access can be controlled via Filament's built-in authorization
- Sensitive settings can be marked as `locked` to prevent modification
- Form validation ensures data integrity
- Access logging is available through Filament's activity log features

## Performance

- Settings are cached automatically by the Spatie package
- Helper methods provide optimized access patterns
- Global functions minimize repeated container resolution
- Database queries are minimized through intelligent caching

## Future Extensions

The modular design allows for easy addition of new settings groups:
1. Create new settings class extending `Spatie\LaravelSettings\Settings`
2. Generate corresponding Filament page
3. Add helper methods as needed
4. Register in service provider if required

This architecture provides a scalable foundation for application configuration management.