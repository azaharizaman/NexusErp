# NexusERP Coding Standards and Guidelines

This document outlines coding standards and best practices for the NexusERP project. These guidelines ensure code consistency, quality, and maintainability.

## Table of Contents
- [Decimal and Float Handling](#decimal-and-float-handling)
- [Database Migrations](#database-migrations)
- [Serial Number Patterns](#serial-number-patterns)
- [PHPDoc Comments](#phpdoc-comments)

## Decimal and Float Handling

### Use bcmath Functions for Precision

**Rule**: Always use `bcmath` functions (`bcadd`, `bcsub`, `bcmul`, `bcdiv`, `bccomp`) for decimal arithmetic operations instead of standard PHP arithmetic operators.

**Reason**: Standard PHP arithmetic operators can introduce floating-point precision errors when dealing with monetary values and other decimal calculations.

**Examples**:

✅ **Correct**:
```php
// Comparison
if (bccomp($this->paid_amount, '0', 4) > 0) {
    // ...
}

// Subtraction
$this->unallocated_amount = bcsub($this->amount, $this->allocated_amount, 4);

// Addition
$invoice->paid_amount = bcadd($invoice->paid_amount, $amount, 4);
```

❌ **Incorrect**:
```php
// Don't use direct comparison with floats
if ($this->paid_amount > 0) {
    // ...
}

// Don't use arithmetic operators for decimals
$this->unallocated_amount = $this->amount - $this->allocated_amount;

// Don't use += for decimal fields
$invoice->paid_amount += $amount;
```

**Key Points**:
- Use precision parameter (typically `4` for 4 decimal places)
- Always pass values as strings to bcmath functions
- Use `bccomp()` for comparisons, which returns: -1 (less than), 0 (equal), or 1 (greater than)

## Database Migrations

### Avoid Duplicate Migrations

**Rule**: Ensure each database table has only one migration file. Remove any duplicate migrations before pushing changes.

**Why This Matters**: 
- Multiple migrations for the same table cause errors when running `php artisan migrate`
- Creates confusion about which migration is the source of truth
- Can lead to inconsistent database states across environments

**How to Check**:
```bash
# Find potential duplicate migrations
find database/migrations -name "*supplier_invoice*"
find database/migrations -name "*payment_voucher*"
```

**Resolution**:
- Keep the latest migration file with the most complete schema
- Remove older duplicate migration files
- Verify migrations run successfully: `php artisan migrate:fresh`

### Optimize Database Indexes

**Rule**: Avoid creating redundant standalone indexes when a composite index already covers the same column(s).

**Reason**: Composite indexes can be used for queries on their leftmost columns, making standalone indexes on those columns unnecessary and wasteful of storage.

**Examples**:

✅ **Correct**:
```php
// Only the composite unique index is needed
$table->unique(['payment_voucher_id', 'supplier_invoice_id']);
```

❌ **Incorrect**:
```php
// This standalone index is redundant
$table->index('payment_voucher_id');
// Because this composite index already covers it
$table->unique(['payment_voucher_id', 'supplier_invoice_id']);
```

**Exception**: Keep standalone indexes if specific query patterns require them. Document why with a comment.

## Serial Number Patterns

### Use Unique Prefixes for Document Types

**Rule**: Each document type must have a unique serial number prefix to avoid conflicts and confusion.

**Examples**:

✅ **Correct**:
```php
'salesinvoice' => [
    'pattern' => 'SI-{year}-{number}',
    // ...
],
'supplierinvoice' => [
    'pattern' => 'SINV-{year}-{number}', // or 'PI-', 'BILL-', etc.
    // ...
],
```

❌ **Incorrect**:
```php
'salesinvoice' => [
    'pattern' => 'SI-{year}-{number}',
    // ...
],
'supplierinvoice' => [
    'pattern' => 'SI-{year}-{number}', // Conflict! Same prefix as sales invoice
    // ...
],
```

**Suggested Prefixes**:
- Sales Invoice: `SI-`
- Supplier Invoice: `SINV-` or `PI-` (Purchase Invoice)
- Payment Voucher: `PV-`
- Payment Receipt: `PR-`
- Credit Note: `CN-`
- Debit Note: `DN-`

## PHPDoc Comments

### Write Accurate and Descriptive Comments

**Rule**: PHPDoc comments should accurately describe the method's purpose and match the method name.

**Examples**:

✅ **Correct**:
```php
/**
 * Scope to filter invoices with outstanding amounts (unpaid or partially paid).
 */
public function scopeUnpaid($query)
{
    return $query->where('outstanding_amount', '>', 0);
}
```

❌ **Incorrect**:
```php
/**
 * Scope for unpaid invoices (with outstanding amount)
 */
public function scopeUnpaid($query)
{
    // Comment should be more descriptive about what "unpaid" means
    return $query->where('outstanding_amount', '>', 0);
}
```

**Best Practices**:
- Be specific about what the method does
- Explain any non-obvious logic
- Document edge cases or special behavior
- Keep comments up-to-date with code changes

## Review Checklist

Before submitting a PR, verify:

- [ ] All decimal operations use `bcmath` functions
- [ ] No duplicate migration files exist
- [ ] Database indexes are not redundant
- [ ] Serial number patterns have unique prefixes
- [ ] PHPDoc comments are accurate and descriptive
- [ ] Code follows existing patterns in the codebase
- [ ] Tests pass successfully
- [ ] Migrations run without errors

## References

- [PHP bcmath Documentation](https://www.php.net/manual/en/ref.bc.php)
- [Laravel Migration Documentation](https://laravel.com/docs/migrations)
- [PSR-5 PHPDoc Standard](https://github.com/php-fig/fig-standards/blob/master/proposed/phpdoc.md)
