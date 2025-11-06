# Accounts Payable (AP) Module

The Accounts Payable (AP) Module in NexusERP provides comprehensive management of supplier invoices, payment vouchers, debit notes, and GL integration.

## Table of Contents

- [Overview](#overview)
- [Core Components](#core-components)
- [Workflows](#workflows)
- [Three-Way Matching](#three-way-matching)
- [GL Integration](#gl-integration)
- [Payment Processing](#payment-processing)
- [API Reference](#api-reference)
- [Examples](#examples)

## Overview

The AP module handles the complete lifecycle of accounts payable operations, from receiving supplier invoices to making payments and posting transactions to the general ledger.

### Key Features

- **Supplier Invoice Management**: Track and manage supplier invoices with multi-currency support
- **Three-Way Matching**: Validate invoices against purchase orders and goods received notes
- **Payment Processing**: Create and manage payment vouchers with allocation tracking
- **Debit Note Management**: Handle returns, adjustments, and credits from suppliers
- **GL Integration**: Automatic posting of AP transactions to the general ledger
- **Status Workflows**: Comprehensive status tracking with approval workflows
- **Multi-Currency Support**: Handle invoices and payments in multiple currencies

## Core Components

### Models

#### SupplierInvoice

Represents invoices received from suppliers.

**Key Fields:**
- `invoice_number`: Unique invoice identifier (auto-generated)
- `supplier_id`: Reference to the supplier (BusinessPartner)
- `total_amount`: Total invoice amount including tax
- `paid_amount`: Amount paid against this invoice
- `outstanding_amount`: Remaining unpaid amount
- `status`: Invoice status (draft, submitted, approved, paid, etc.)

**Status Workflow:**
```
draft → submitted → approved → partially_paid → paid
                  ↓
              rejected
```

**Key Methods:**
- `calculateOutstanding()`: Recalculates outstanding amount
- `isFullyPaid()`: Checks if invoice is fully paid
- `isOverdue()`: Checks if invoice is past due date
- `recordPayment($amount)`: Records a payment against the invoice
- `updatePaymentStatus()`: Updates status based on payment

#### PaymentVoucher

Represents payments made to suppliers.

**Key Fields:**
- `voucher_number`: Unique voucher identifier (auto-generated)
- `supplier_id`: Reference to the supplier
- `amount`: Total payment amount
- `allocated_amount`: Amount allocated to invoices
- `unallocated_amount`: Amount not yet allocated
- `payment_method`: Method of payment (cash, bank_transfer, etc.)
- `is_on_hold`: Whether payment is on hold

**Status Workflow:**
```
draft → submitted → approved → paid → posted_to_gl
      ↓
  on_hold
      ↓
  voided
```

**Key Methods:**
- `allocateToInvoice($invoice, $amount)`: Allocates payment to an invoice
- `isFullyAllocated()`: Checks if payment is fully allocated
- `recalculateAllocations()`: Recalculates allocation amounts
- `canApprove()`: Checks if voucher can be approved
- `canPay()`: Checks if voucher can be paid
- `canVoid()`: Checks if voucher can be voided

#### SupplierDebitNote

Represents debit notes for returns, adjustments, or credits from suppliers.

**Key Fields:**
- `debit_note_number`: Unique debit note identifier (auto-generated)
- `supplier_id`: Reference to the supplier
- `supplier_invoice_id`: Optional reference to related invoice
- `debit_amount`: Amount to be debited
- `reason`: Reason for debit note (return, price_adjustment, quality_issue, etc.)
- `status`: Debit note status (draft, issued, applied, cancelled)

**Status Values:**
- `draft`: Being prepared
- `issued`: Sent to supplier
- `applied`: Applied to invoice (reduces outstanding amount)
- `cancelled`: Cancelled debit note

**Key Methods:**
- `applyToInvoice()`: Applies debit note to linked invoice
- `canBeApplied()`: Checks if debit note can be applied

## Workflows

### Supplier Invoice Processing

1. **Create Invoice**: Create new supplier invoice in draft status
2. **Add Line Items**: Add invoice line items with quantities and prices
3. **Three-Way Matching** (if applicable): Validate against PO and GRN
4. **Submit for Approval**: Submit invoice for approval
5. **Approve**: Approve invoice if validation passes
6. **Post to GL**: Post approved invoice to general ledger
7. **Payment**: Create payment voucher and allocate to invoice
8. **Close**: Invoice marked as paid when fully paid

### Payment Voucher Processing

1. **Create Voucher**: Create new payment voucher in draft status
2. **Allocate to Invoices**: Allocate payment amount to one or more invoices
3. **Submit for Approval**: Submit voucher for approval
4. **Approve**: Approve voucher for payment
5. **Record Payment**: Mark voucher as paid when payment is made
6. **Post to GL**: Post payment to general ledger
7. **Update Invoices**: Automatically update invoice paid amounts

### Debit Note Processing

1. **Create Debit Note**: Create debit note with reason
2. **Link to Invoice** (optional): Link to related supplier invoice
3. **Issue**: Issue debit note to supplier
4. **Apply to Invoice**: Apply debit note to reduce invoice outstanding
5. **Post to GL**: Post debit note to general ledger

## Three-Way Matching

Three-way matching validates supplier invoices against purchase orders and goods received notes to ensure accuracy.

### Validation Rules

1. **Quantity Matching**: Invoice quantities must match GRN received quantities
2. **Price Matching**: Invoice unit prices must match PO agreed prices
3. **Tolerance Check**: Variances within tolerance percentage are acceptable
4. **Mismatch Reporting**: Detailed reporting of any discrepancies

### Tolerance-Based Approval

- **Default Tolerance**: 5% variance allowed
- **Within Tolerance**: Invoice can be approved automatically
- **Exceeds Tolerance**: Invoice approval is blocked pending review
- **Override**: Authorized users can override tolerance blocking

### Implementation

```php
use App\Actions\AccountsPayable\ValidateThreeWayMatch;

// Validate invoice against PO and GRN
$matching = ValidateThreeWayMatch::run($invoice);

if ($matching->shouldBlockApproval()) {
    // Cannot approve - variance exceeds tolerance
    $report = $matching->getMatchingReport();
    // Display mismatches to user
} else {
    // Can approve invoice
    $invoice->setStatus('approved', 'Approved via three-way matching');
}
```

## GL Integration

The AP module integrates with the general ledger to maintain accurate financial records.

### Posting Rules

#### Supplier Invoice Posting

```
Dr. Expense Accounts (per line item)     Line totals
Dr. Tax Payable (Input Tax Credit)       Tax amount
    Cr. Accounts Payable                 Total invoice amount
```

#### Payment Voucher Posting

```
Dr. Accounts Payable                     Payment amount
    Cr. Cash/Bank Account                Payment amount
```

#### Debit Note Posting

```
Dr. Accounts Payable                     Debit note amount
    Cr. Purchase Returns/Allowances      Debit note amount
```

### Posting Actions

```php
use App\Actions\AccountsPayable\PostSupplierInvoice;
use App\Actions\AccountsPayable\PostPaymentVoucher;
use App\Actions\AccountsPayable\PostSupplierDebitNote;

// Post supplier invoice to GL
$journalEntry = PostSupplierInvoice::run($invoice, $taxPayableAccountId);

// Post payment voucher to GL
$journalEntry = PostPaymentVoucher::run($voucher, $cashAccountId, $apAccountId);

// Post debit note to GL
$journalEntry = PostSupplierDebitNote::run($debitNote, $apAccountId, $purchaseReturnsAccountId);
```

### Validation

Before posting to GL, the system validates:
- Document status is correct (approved for invoices, paid for vouchers)
- Not already posted to GL
- Accounting period is open
- Required accounts are specified
- Invoice has line items (for invoices)

## Payment Processing

### Payment Allocation

Payments can be allocated to one or more supplier invoices.

```php
// Create payment voucher
$voucher = PaymentVoucher::create([
    'company_id' => $company->id,
    'supplier_id' => $supplier->id,
    'currency_id' => $currency->id,
    'payment_date' => now(),
    'payment_method' => 'bank_transfer',
    'amount' => 1500.00,
    'allocated_amount' => 0.00,
    'unallocated_amount' => 1500.00,
]);

// Allocate to invoice 1
$voucher->allocateToInvoice($invoice1, 1000.00);

// Allocate remaining to invoice 2
$voucher->allocateToInvoice($invoice2, 500.00);

// Check if fully allocated
if ($voucher->isFullyAllocated()) {
    // Ready for approval
}
```

### Payment Methods

Supported payment methods:
- `cash`: Cash payment
- `bank_transfer`: Bank transfer
- `credit_card`: Credit card
- `debit_card`: Debit card
- `cheque`: Cheque payment
- `online`: Online payment
- `other`: Other payment methods

### Payment On Hold

Payments can be placed on hold for review:

```php
use App\Actions\PaymentVoucher\PlacePaymentOnHold;

PlacePaymentOnHold::run($voucher, 'Pending vendor verification', $userId);

// Payment is now on hold
// Release by updating is_on_hold flag
```

## API Reference

### PaymentVoucher Model

#### Scopes

- `draft()`: Draft vouchers
- `submitted()`: Submitted vouchers
- `approved()`: Approved vouchers
- `paid()`: Paid vouchers
- `voided()`: Voided vouchers
- `onHold()`: Vouchers on hold
- `notOnHold()`: Vouchers not on hold
- `unallocated()`: Vouchers with unallocated amount
- `postedToGl()`: Vouchers posted to GL

#### Relationships

- `company()`: Company relationship
- `supplier()`: Supplier (BusinessPartner) relationship
- `supplierInvoice()`: Related invoice relationship
- `currency()`: Currency relationship
- `journalEntry()`: GL journal entry relationship
- `allocations()`: Payment allocations relationship
- `requester()`, `approver()`, `payer()`, `voider()`: User relationships
- `creator()`, `updater()`, `holder()`: Audit relationships

### SupplierDebitNote Model

#### Scopes

- `draft()`: Draft debit notes
- `issued()`: Issued debit notes
- `applied()`: Applied debit notes
- `cancelled()`: Cancelled debit notes
- `forSupplier($supplierId)`: Debit notes for specific supplier
- `forCompany($companyId)`: Debit notes for specific company
- `forInvoice($invoiceId)`: Debit notes for specific invoice
- `postedToGl()`: Debit notes posted to GL
- `byReason($reason)`: Debit notes by reason

#### Reason Values

- `return`: Product return
- `price_adjustment`: Price adjustment
- `quality_issue`: Quality issue
- `shipping_error`: Shipping error
- `other`: Other reasons

## Examples

### Complete Invoice Payment Flow

```php
// 1. Create supplier invoice
$invoice = SupplierInvoice::create([
    'company_id' => $company->id,
    'supplier_id' => $supplier->id,
    'currency_id' => $currency->id,
    'invoice_date' => now(),
    'due_date' => now()->addDays(30),
    'total_amount' => 10000.00,
    'outstanding_amount' => 10000.00,
]);

// 2. Add line items
$invoice->items()->create([
    'description' => 'Office Supplies',
    'quantity' => 100,
    'unit_price' => 100.00,
    'line_total' => 10000.00,
]);

// 3. Submit and approve invoice
$invoice->setStatus('submitted', 'Submitted for approval');
$invoice->setStatus('approved', 'Approved by manager');

// 4. Post to GL
$journalEntry = PostSupplierInvoice::run($invoice, $taxPayableAccountId);

// 5. Create payment voucher
$voucher = PaymentVoucher::create([
    'company_id' => $company->id,
    'supplier_id' => $supplier->id,
    'currency_id' => $currency->id,
    'payment_date' => now(),
    'amount' => 10000.00,
    'unallocated_amount' => 10000.00,
]);

// 6. Allocate to invoice
$voucher->allocateToInvoice($invoice, 10000.00);

// 7. Submit, approve, and pay
$voucher->setStatus('submitted', 'Submitted for approval');
$voucher->setStatus('approved', 'Approved for payment');
$voucher->setStatus('paid', 'Payment made');

// 8. Post payment to GL
$paymentJournalEntry = PostPaymentVoucher::run($voucher, $cashAccountId, $apAccountId);

// 9. Invoice is now fully paid
$invoice->refresh();
// $invoice->latestStatus() === 'paid'
// $invoice->outstanding_amount === 0.00
```

### Handling Returns with Debit Note

```php
// Create debit note for returned goods
$debitNote = SupplierDebitNote::create([
    'company_id' => $company->id,
    'supplier_id' => $supplier->id,
    'supplier_invoice_id' => $invoice->id,
    'currency_id' => $currency->id,
    'debit_note_date' => now(),
    'reason' => 'return',
    'debit_amount' => 500.00,
    'status' => 'draft',
    'description' => 'Returning 5 defective units',
]);

// Issue debit note
$debitNote->status = 'issued';
$debitNote->save();

// Apply to invoice (reduces outstanding)
$debitNote->applyToInvoice();

// Invoice outstanding is now reduced by debit amount
$invoice->refresh();
// $invoice->outstanding_amount === 9500.00

// Post debit note to GL
$journalEntry = PostSupplierDebitNote::run($debitNote, $apAccountId, $purchaseReturnsAccountId);
```

## Best Practices

1. **Always use three-way matching** when POs and GRNs are available
2. **Allocate payments immediately** to maintain accurate outstanding balances
3. **Review on-hold payments regularly** to avoid delayed payments
4. **Post to GL promptly** to maintain accurate financial records
5. **Use appropriate debit note reasons** for proper categorization
6. **Validate currency matching** when allocating payments to invoices
7. **Check status transitions** before performing actions
8. **Handle exceptions gracefully** with proper error messages
9. **Maintain audit trail** by recording user actions
10. **Regular reconciliation** of AP balances with GL

## Troubleshooting

### Common Issues

**Issue**: Payment allocation fails
- **Check**: Ensure payment and invoice use same currency
- **Check**: Verify allocation amount doesn't exceed unallocated payment amount
- **Check**: Verify allocation amount doesn't exceed invoice outstanding amount

**Issue**: Cannot approve invoice
- **Check**: Verify invoice is in 'submitted' status
- **Check**: Check if three-way matching blocks approval
- **Check**: Review matching report for variances

**Issue**: GL posting fails
- **Check**: Verify accounting period is open
- **Check**: Ensure document is in correct status
- **Check**: Confirm document is not already posted
- **Check**: Validate required accounts are specified

**Issue**: Debit note application fails
- **Check**: Verify debit note is in 'issued' status
- **Check**: Confirm debit note is linked to an invoice
- **Check**: Ensure debit amount doesn't exceed invoice outstanding

## Future Enhancements

- Bulk payment processing
- Payment voucher allocation to multiple invoices in single action
- Automated payment scheduling
- Early payment discounts
- Currency exchange rate variance handling
- Multi-level approval workflows
- Integration with bank feeds
- Automated recurring payments
- Advanced reporting and analytics

## Related Documentation

- [Purchase Management Module](./purchase-management.md)
- [General Ledger Integration](./general-ledger.md)
- [Multi-Currency Support](./multi-currency.md)
- [Approval Workflows](./approval-workflows.md)
