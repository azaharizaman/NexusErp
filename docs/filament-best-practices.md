# Filament Best Practices for NexusERP

This document outlines the best practices and conventions for implementing Filament resources in the NexusERP project.

## Table Columns

### Display Relationship Data, Not IDs

**ALWAYS** display meaningful relationship columns instead of raw database IDs. This improves user experience and makes the interface more intuitive.

#### ❌ Incorrect - Displaying IDs
```php
TextColumn::make('supplier_id')
    ->numeric()
    ->sortable(),
TextColumn::make('currency_id')
    ->numeric()
    ->sortable(),
```

#### ✅ Correct - Displaying Relationship Attributes
```php
TextColumn::make('supplier.name')
    ->label('Supplier')
    ->searchable()
    ->sortable(),
TextColumn::make('currency.code')
    ->label('Currency')
    ->searchable()
    ->sortable(),
```

### Common Relationship Column Patterns

| Relationship | Recommended Display Column | Example |
|--------------|---------------------------|---------|
| Supplier/Vendor | `supplier.name` | `TextColumn::make('supplier.name')` |
| Customer | `customer.name` | `TextColumn::make('customer.name')` |
| Purchase Order | `purchaseOrder.po_number` | `TextColumn::make('purchaseOrder.po_number')` |
| Invoice | `invoice.invoice_number` or `supplierInvoice.invoice_number` | `TextColumn::make('supplierInvoice.invoice_number')` |
| Payment Voucher | `paymentVoucher.voucher_number` | `TextColumn::make('paymentVoucher.voucher_number')` |
| Currency | `currency.code` or `currency.name` | `TextColumn::make('currency.code')` |
| Company | `company.name` | `TextColumn::make('company.name')` |
| User | `user.name` | `TextColumn::make('user.name')` |

### Audit Fields in Tables

Display audit fields as relationship columns showing user names, not numeric IDs.

#### ❌ Incorrect
```php
TextColumn::make('created_by')
    ->numeric()
    ->sortable(),
TextColumn::make('updated_by')
    ->numeric()
    ->sortable(),
```

#### ✅ Correct
```php
TextColumn::make('creator.name')
    ->label('Created By')
    ->searchable()
    ->sortable()
    ->toggleable(isToggledHiddenByDefault: true),
TextColumn::make('updater.name')
    ->label('Updated By')
    ->searchable()
    ->sortable()
    ->toggleable(isToggledHiddenByDefault: true),
```

**Note:** Use `toggleable(isToggledHiddenByDefault: true)` for audit columns if they don't need to be visible by default but should be available when needed.

## Form Fields

### Select Field Relationships

When using select fields for relationships, **ALWAYS** specify a meaningful display column, not the ID.

#### ❌ Incorrect - Displaying IDs in Dropdown
```php
Select::make('supplier_id')
    ->relationship('supplier', 'id')  // Users will only see numeric IDs
    ->required()
    ->searchable(),
```

#### ✅ Correct - Displaying Meaningful Data
```php
Select::make('supplier_id')
    ->relationship('supplier', 'name')  // Users will see supplier names
    ->label('Supplier')
    ->required()
    ->searchable()
    ->preload()
    ->native(false),
```

### Audit Fields in Forms

**NEVER** include audit fields (`created_by`, `updated_by`, `created_at`, `updated_at`) in forms as they are managed automatically by the system.

#### ❌ Incorrect - Including Audit Fields
```php
Section::make('Audit Information')
    ->schema([
        TextInput::make('created_by')
            ->numeric()
            ->required(),
        TextInput::make('updated_by')
            ->numeric(),
    ]),
```

#### ✅ Correct - Removing Audit Fields
```php
// Audit fields should not be in forms at all
// They are automatically managed by the application
```

If you absolutely must display audit information for reference (rare cases), use placeholders or disabled fields:

```php
Section::make('Audit Information')
    ->schema([
        Placeholder::make('creator.name')
            ->label('Created By')
            ->content(fn ($record) => $record?->creator?->name ?? 'N/A'),
        Placeholder::make('created_at')
            ->label('Created At')
            ->content(fn ($record) => $record?->created_at?->format('Y-m-d H:i:s') ?? 'N/A'),
    ])
    ->visible(fn ($record) => $record !== null),  // Only show on edit, not create
```

## JSON and Array Input Fields

### Use Appropriate Filament Components for Structured Data

**NEVER** use `Textarea` for JSON or array input. Use proper Filament components that provide better UX, validation, and error prevention.

#### ❌ Incorrect - Using Textarea for JSON
```php
Textarea::make('required_roles')
    ->label('Required Roles (JSON array)')
    ->helperText('e.g., ["manager", "finance_head"]')
    ->rows(2),

Textarea::make('staff_ids')
    ->label('Specific Staff IDs (JSON array)')
    ->helperText('e.g., [1, 2, 3]')
    ->rows(2),

Textarea::make('condition')
    ->label('Transition Conditions (JSON)')
    ->helperText('Optional: Define conditions in JSON format')
    ->rows(3),
```

**Problems:**
- Error-prone: Users can easily make syntax errors
- No validation: Invalid JSON can be saved
- Poor UX: Users need to remember JSON syntax
- No autocomplete or suggestions

#### ✅ Correct - Using Proper Components

**For Key-Value Pairs:** Use `KeyValue` component
```php
KeyValue::make('condition')
    ->label('Transition Conditions')
    ->helperText('Optional: Define conditions as key-value pairs')
    ->keyLabel('Condition Key')
    ->valueLabel('Condition Value')
    ->addActionLabel('Add Condition')
    ->reorderable()
    ->columnSpanFull(),
```

**For Simple Arrays/Tags:** Use `TagsInput` with suggestions
```php
use Spatie\Permission\Models\Role;

TagsInput::make('required_roles')
    ->label('Required Roles')
    ->helperText('Select or type role names')
    ->suggestions(fn () => Role::pluck('name')->toArray())
    ->placeholder('Add role'),
```

**For Multiple Selection from Database:** Use `Select` with `multiple()`
```php
use App\Models\User;

Select::make('staff_ids')
    ->label('Specific Staff Members')
    ->helperText('Select specific staff members (optional)')
    ->multiple()
    ->searchable()
    ->preload()
    ->options(fn () => User::pluck('name', 'id'))
    ->placeholder('Select staff members'),
```

**For Complex Nested Data:** Use `Repeater` with proper field schema
```php
Repeater::make('items')
    ->schema([
        TextInput::make('name')->required(),
        TextInput::make('quantity')->numeric()->required(),
        Select::make('unit')->options(['kg', 'pcs', 'box'])->required(),
    ])
    ->columns(3)
    ->addActionLabel('Add Item'),
```

### When Textarea is Absolutely Necessary

If you must use `Textarea` for JSON input (very rare cases), **ALWAYS** add proper validation and transformation:

```php
Textarea::make('metadata')
    ->label('Metadata (JSON)')
    ->rows(5)
    ->rules(['json'])  // Validates JSON syntax
    ->dehydrateStateUsing(fn ($state) => json_decode($state, true))  // Convert to array
    ->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT)),  // Format for display
```

### Component Selection Guide

| Data Type | Recommended Component | Example Use Case |
|-----------|----------------------|------------------|
| Key-value pairs | `KeyValue` | Configuration options, metadata |
| Simple string array | `TagsInput` | Tags, roles, categories |
| Selection from DB | `Select::multiple()` | Users, products, categories |
| Complex nested data | `Repeater` | Line items, addresses, contacts |
| Boolean flags | `CheckboxList` | Feature flags, permissions |

## Exception Handling Best Practices

### Use Specific Exception Types

**NEVER** use generic `\Exception`. Always use the most specific exception type that describes the error condition.

#### ❌ Incorrect - Generic Exception
```php
if (! in_array(HasStatuses::class, class_uses_recursive($model))) {
    throw new \Exception('Model must use HasStatuses trait');
}
```

**Problems:**
- Doesn't indicate the error category
- Hard to catch specific error types
- Doesn't help with debugging

#### ✅ Correct - Specific Exception with Context
```php
if (! in_array(HasStatuses::class, class_uses_recursive($model))) {
    throw new \InvalidArgumentException(
        'Model '.get_class($model).' must use HasStatuses trait'
    );
}
```

### Exception Type Guidelines

| Error Type | Exception Class | When to Use |
|------------|----------------|-------------|
| Invalid arguments/parameters | `InvalidArgumentException` | Wrong type, missing required data |
| Logic errors | `LogicException` | Programming errors, wrong state |
| Runtime errors | `RuntimeException` | File not found, permission denied |
| Database errors | `PDOException` | Query failures, constraint violations |
| Domain-specific errors | Custom Exception | Business rule violations |

### Include Helpful Context

**ALWAYS** include helpful information in exception messages:
- Variable values
- Class names
- Expected vs. actual values
- Stack trace hints

```php
// ✅ Good - Includes context
throw new \InvalidArgumentException(
    "Expected model to implement ".HasStatuses::class.", but ".get_class($model)." does not"
);

// ✅ Good - Includes state information
throw new \LogicException(
    "Cannot approve voucher with status '{$this->latestStatus()}'. Expected 'submitted'"
);

// ✅ Good - Includes expected and actual
throw new \RuntimeException(
    "Failed to process payment. Expected amount: {$expected}, Got: {$actual}"
);
```

## Additional Best Practices

### Searchable and Sortable

Make relationship columns searchable and sortable when appropriate:

```php
TextColumn::make('supplier.name')
    ->label('Supplier')
    ->searchable()  // Enables search on supplier name
    ->sortable(),   // Enables sorting by supplier name
```

### Labels

Always provide clear, user-friendly labels:

```php
TextColumn::make('supplier.name')
    ->label('Supplier'),  // Clear label instead of 'Supplier.Name'
```

### Preloading Select Options

For select fields with relationships, use `preload()` to improve UX:

```php
Select::make('supplier_id')
    ->relationship('supplier', 'name')
    ->searchable()
    ->preload()  // Preloads options for better performance
    ->native(false),  // Uses Filament's custom select UI
```

## Working with Carbon Dates

### Avoid Mutating Carbon Instances

Carbon date methods like `addDays()`, `subDays()`, `addMonths()`, etc., **mutate the original instance**. Always use `copy()` to prevent unintended side effects.

#### ❌ Incorrect - Mutating Original Date
```php
public function generateSchedules($baseDate)
{
    $schedules = [
        ['due_date' => $baseDate->addDays(30)],  // Mutates $baseDate!
        ['due_date' => $baseDate->addDays(30)],  // Now adds 60 days total
    ];
}
```

#### ✅ Correct - Using copy()
```php
public function generateSchedules($baseDate)
{
    $schedules = [
        ['due_date' => $baseDate->copy()->addDays(30)],  // Creates a copy
        ['due_date' => $baseDate->copy()->addDays(60)],  // Original unchanged
    ];
}
```

**Key Rule:** Always use `$date->copy()` before calling any mutation method when you need to preserve the original date.

## Working with Spatie ModelStatus

### Check Current Status with Strict Comparison

When checking if a model has a specific status, use strict equality comparison (`===`) with `latestStatus()`, not null checks.

#### ❌ Incorrect - Checking for Status Existence
```php
public function canApprove(): bool
{
    // This checks if there's ANY 'submitted' status in history
    return $this->latestStatus('submitted') !== null;
}
```

#### ✅ Correct - Checking Current Status
```php
public function canApprove(): bool
{
    // This checks if the CURRENT status is 'submitted'
    return $this->latestStatus() === 'submitted';
}
```

**Key Rule:** Use `$this->latestStatus() === 'status_name'` to check the current status, not `$this->latestStatus('status_name') !== null`.

### Understanding latestStatus() Behavior

The `latestStatus()` method from Spatie ModelStatus:
- `latestStatus()` - Returns the name of the current status as a string, or null if no status exists
- `latestStatus('name')` - Checks if the latest status matches the given name and returns the name or null

**Example:**
```php
// Get current status
$currentStatus = $model->latestStatus();  // Returns 'draft', 'approved', etc.

// Check if current status is 'approved'
if ($model->latestStatus() === 'approved') {
    // Current status is approved
}

// AVOID: Using parameter for current status check
if ($model->latestStatus('approved') !== null) {
    // This works but is less clear
}
```

## Summary

The key principles are:
1. **Make the UI user-friendly** by displaying meaningful data instead of technical database IDs
2. **Use appropriate input components** - Never use Textarea for JSON/arrays; use KeyValue, TagsInput, or Select
3. **Preserve Carbon dates** by using `copy()` before mutating
4. **Check current status clearly** using strict equality with `latestStatus()`
5. **Use specific exceptions** with helpful context for better debugging and error handling

Users should never have to:
- Manually write JSON syntax
- Mentally map numeric IDs to understand what they're looking at
- Debug generic exception messages without context

Code should be explicit about its intent and provide the best possible user experience.
