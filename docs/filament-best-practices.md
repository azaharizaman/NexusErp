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

## Summary

The key principle is: **Make the UI user-friendly by displaying meaningful data instead of technical database IDs.** Users should never have to mentally map numeric IDs to understand what they're looking at.
