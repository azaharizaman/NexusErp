# Laravel Actions Implementation Guide

## Overview

Laravel Actions have been successfully implemented in your NexusERP application. This guide shows you how to use them effectively throughout your application.

## Installation Status

✅ **Laravel Actions Package**: Already installed (`lorisleiva/laravel-actions: ^2.9`)

## Actions Structure

Your actions are organized in the `app/Actions` directory with the following structure:

```
app/Actions/
├── Company/
│   ├── CreateCompany.php
│   ├── UpdateCompany.php
│   ├── DeleteCompany.php
│   └── ToggleCompanyStatus.php    # NEW: Toggle active/inactive status
├── User/
│   ├── CreateUser.php
│   └── UpdateUserPassword.php
├── Settings/
│   ├── UpdateGeneralSettings.php
│   └── UpdateFinancialSettings.php
└── Utils/
    ├── FormatCurrency.php
    ├── GenerateInvoiceNumber.php
    └── ConvertUnits.php
```

## How to Use Actions

### 1. Basic Usage

```php
// In your controllers, services, or anywhere in your application
use App\Actions\Company\CreateCompany;

// Simple usage
$company = CreateCompany::run([
    'name' => 'ACME Corporation',
    'code' => 'ACME001',
    'email' => 'info@acme.com',
]);

// With validation
$companyData = request()->validate([
    'name' => 'required|string|max:255',
    'code' => 'required|string|unique:companies,code',
    'email' => 'required|email',
]);

$company = CreateCompany::run($companyData);
```

### 2. Using Actions in Filament Resources

Your Filament resources have been updated to use actions:

**CreateCompany Page** (`app/Filament/Resources/Companies/Pages/CreateCompany.php`):
```php
protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
{
    return CreateCompanyAction::run($data);
}
```

**EditCompany Page** (`app/Filament/Resources/Companies/Pages/EditCompany.php`):
```php
protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
{
    return UpdateCompanyAction::run($record, $data);
}
```

**ViewCompany Page** (`app/Filament/Resources/Companies/Pages/ViewCompany.php`):
```php
// Toggle Company Status Action Button
Action::make('toggleStatus')
    ->label(fn () => $this->record->is_active ? 'Deactivate Company' : 'Activate Company')
    ->icon(fn () => $this->record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
    ->color(fn () => $this->record->is_active ? 'danger' : 'success')
    ->requiresConfirmation()
    ->action(function () {
        $company = ToggleCompanyStatus::run($this->record);
        $this->notify('success', $message);
    })
```

**CompaniesTable** (`app/Filament/Resources/Companies/Tables/CompaniesTable.php`):
- **Row Actions**: Toggle status for individual companies
- **Bulk Actions**: Activate or deactivate multiple companies at once

### 3. Using Actions as Jobs

Actions can be dispatched as background jobs:

```php
use App\Actions\Company\CreateCompany;

// Dispatch to queue
CreateCompany::dispatch([
    'name' => 'Background Company',
    'code' => 'BG001',
    'email' => 'bg@company.com',
]);

// Dispatch with specific queue and delay
CreateCompany::dispatch($companyData)
    ->onQueue('companies')
    ->delay(now()->addMinutes(5));
```

### 4. Using Actions as Commands

Register actions as Artisan commands in your `ConsoleKernel`:

```php
// In app/Console/Kernel.php
protected function commands()
{
    // Register all actions as commands
    \App\Actions\Company\CreateCompany::commandSignature('company:create');
}
```

Then use via CLI:
```bash
php artisan company:create --name="CLI Company" --code="CLI001"
```

### 5. Utility Actions Examples

**Currency Formatting**:
```php
use App\Actions\Utils\FormatCurrency;

$price = 1234.56;
$formatted = FormatCurrency::run($price); // Returns: $1,234.56

// With custom currency
$formatted = FormatCurrency::run($price, 'EUR'); // Returns: €1,234.56
```

**Invoice Number Generation**:
```php
use App\Actions\Utils\GenerateInvoiceNumber;

// Generate next invoice number
$invoiceNumber = GenerateInvoiceNumber::run('INV-', 6); // Returns: INV-000001

// Generate from last number
$nextNumber = GenerateInvoiceNumber::run('INV-', 6, 'INV-000005'); // Returns: INV-000006

// Generate multiple numbers
$action = new GenerateInvoiceNumber();
$numbers = $action->handleBatch(5, 'QUO-', 4);
// Returns: ['QUO-0001', 'QUO-0002', 'QUO-0003', 'QUO-0004', 'QUO-0005']
```

**Unit Conversion**:
```php
use App\Actions\Utils\ConvertUnits;

$result = ConvertUnits::run(100, 'cm', 'm');
// Returns conversion result with detailed information
```

**Company Status Management**:
```php
use App\Actions\Company\ToggleCompanyStatus;

// Toggle company status (active ↔ inactive)
$company = ToggleCompanyStatus::run($company);

// Specifically mark as inactive
$action = new ToggleCompanyStatus();
$company = $action->markInactive($company);

// Specifically mark as active
$company = $action->markActive($company);

// Force set specific status
$company = ToggleCompanyStatus::run($company, false); // Force inactive
$company = ToggleCompanyStatus::run($company, true);  // Force active

// Get success message
$message = $action->getSuccessMessage($company);
```

### 6. Settings Actions

Update application settings using actions:

```php
use App\Actions\Settings\UpdateGeneralSettings;
use App\Actions\Settings\UpdateFinancialSettings;

// Update general settings
UpdateGeneralSettings::run([
    'app_name' => 'NexusERP Pro',
    'timezone' => 'Asia/Kuala_Lumpur',
    'items_per_page' => 25,
]);

// Update financial settings
UpdateFinancialSettings::run([
    'default_currency' => 'MYR',
    'currency_symbol' => 'RM',
    'decimal_places' => 2,
]);
```

## Action Features

### 1. Validation
Actions include built-in validation rules:

```php
// In CreateCompany action
public function rules(): array
{
    return [
        'name' => ['required', 'string', 'max:255'],
        'code' => ['required', 'string', 'max:50', 'unique:companies,code'],
        'email' => ['nullable', 'email', 'max:255'],
        // ... more rules
    ];
}
```

### 2. Authorization
Actions can include authorization logic:

```php
public function authorize(): bool
{
    return auth()->check() && auth()->user()->can('create_companies');
}
```

### 3. Multiple Execution Contexts
Each action can be used in different contexts:
- **Direct calls**: `Action::run($data)`
- **Controller methods**: `Action::asController()`
- **Queued jobs**: `Action::dispatch($data)`
- **Artisan commands**: `Action::asCommand()`

### 4. Database Transactions
Actions automatically handle database transactions:

```php
public function handle(array $data): Company
{
    return DB::transaction(function () use ($data) {
        // All database operations here are wrapped in a transaction
        $company = Company::create($data);
        // Additional operations...
        return $company;
    });
}
```

## Best Practices

### 1. Single Responsibility
Each action should do one thing and do it well:
- ✅ `CreateCompany` - Creates a company
- ✅ `UpdateCompany` - Updates a company
- ❌ `ManageCompany` - Too broad, unclear purpose

### 2. Granular Actions
Keep actions granular and composable:
```php
// Good: Compose multiple actions
$company = CreateCompany::run($companyData);
$admin = CreateUser::run($userData);
AssignUserToCompany::run($user, $company);

// Avoid: One large action that does everything
```

### 3. Consistent Naming
Follow consistent naming patterns:
- `Create{Model}` - Creates a new record
- `Update{Model}` - Updates an existing record
- `Delete{Model}` - Deletes a record
- `{Verb}{Subject}` - For utility actions

### 4. Error Handling
Handle errors gracefully in actions:

```php
public function handle(array $data): Company
{
    try {
        return DB::transaction(function () use ($data) {
            // Business logic here
        });
    } catch (QueryException $e) {
        throw new \Exception('Failed to create company: ' . $e->getMessage());
    }
}
```

## Testing Actions

Test file has been created at `tests/Feature/Actions/ActionsTest.php` with examples:

```bash
# Run action tests
php artisan test tests/Feature/Actions/ActionsTest.php

# Run specific test
php artisan test --filter=it_can_create_a_company_using_action
```

## Integration with ERP Features

### 1. Inventory Management
```php
// Future actions you might create
App\Actions\Inventory\CreateProduct::run($productData);
App\Actions\Inventory\UpdateStock::run($product, $quantity);
App\Actions\Inventory\CheckLowStock::run($threshold);
```

### 2. Financial Operations
```php
// Future financial actions
App\Actions\Finance\CreateInvoice::run($invoiceData);
App\Actions\Finance\ProcessPayment::run($payment);
App\Actions\Finance\GenerateReport::run($period);
```

### 3. UOM Integration
```php
// Using with your UOM package
App\Actions\Utils\ConvertUnits::run(100, 'kg', 'lb');
App\Actions\Product\ConvertProductUnits::run($product, $targetUnit);
```

## Console Commands

Create custom commands using actions:

```bash
# Generate action command
php artisan make:action Orders/CreateOrder

# Use UOM commands (already available)
php artisan uom:convert 100 kg lb
php artisan uom:units
php artisan uom:seed
```

## Performance Considerations

1. **Queue Heavy Operations**: Use `Action::dispatch()` for time-consuming tasks
2. **Batch Operations**: Create batch methods for multiple records
3. **Caching**: Implement caching within actions when appropriate
4. **Database Optimization**: Use proper eager loading and avoid N+1 queries

## Conclusion

Laravel Actions provide a clean, testable, and reusable way to organize your business logic. They integrate seamlessly with:

- ✅ Filament admin interface
- ✅ Queue system for background processing
- ✅ Artisan commands
- ✅ Your existing settings system
- ✅ UOM package integration
- ✅ Testing framework

This implementation follows your architectural guidelines and provides a solid foundation for building robust ERP functionality.