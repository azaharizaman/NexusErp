---
goal: Complete Accounts Receivable Module Filament Resources
version: 1.0
date_created: 2025-11-05
last_updated: 2025-11-05
owner: Development Team
status: 'In progress'
tags: ["feature", "accounting", "ar", "filament", "ui"]
---

# Complete Accounts Receivable Module Filament Resources

![Status: In progress](https://img.shields.io/badge/status-In_progress-yellow)

This implementation plan covers the completion of the Accounts Receivable (AR) module by implementing the remaining Filament resources for PaymentReceipt and CustomerCreditNote models. These resources will provide user interfaces for managing customer payments and credit notes with full GL integration.

## 1. Requirements & Constraints

### Functional Requirements
- **REQ-001**: PaymentReceiptResource must support all payment methods (cash, bank, card, cheque, online, other)
- **REQ-002**: Payment allocation interface must allow manual and automatic FIFO allocation to invoices
- **REQ-003**: CustomerCreditNoteResource must support reason tracking and invoice linkage
- **REQ-004**: Both resources must integrate with GL posting actions
- **REQ-005**: Resources must follow Filament v4.2 compatibility patterns established in SalesInvoiceResource

### UI/UX Requirements
- **UX-001**: Forms must display relationship data (customer names, invoice numbers) not raw IDs
- **UX-002**: Status badges must use consistent color coding across AR module
- **UX-003**: Validation messages must be clear and actionable
- **UX-004**: Currency amounts must display with proper formatting and precision

### Technical Constraints
- **CON-001**: Must use Filament v4.2+ form schema pattern: `form(Schema $schema): Schema`
- **CON-002**: Component imports must use correct namespaces (Forms\Components vs Components)
- **CON-003**: Must follow PSR-12 coding standards
- **CON-004**: All methods must have return type declarations

### Security Requirements
- **SEC-001**: Only authorized users can post to GL
- **SEC-002**: Audit fields (created_by, updated_by) must be automatically populated
- **SEC-003**: Financial data must be validated before saving

### Integration Requirements
- **INT-001**: Resources must call Post*Action classes for GL integration
- **INT-002**: Payment allocation must update invoice outstanding amounts atomically
- **INT-003**: Status transitions must follow defined workflows

## 2. Implementation Steps

### Implementation Phase 1: PaymentReceiptResource Foundation

- **GOAL-001**: Create PaymentReceiptResource with basic CRUD operations and form structure

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-001 | Create PaymentReceiptResource class in `app/Filament/Accounting/Resources/` | | |
| TASK-002 | Implement form() method with Schema pattern following Filament v4.2 standards | | |
| TASK-003 | Add company selection field (disabled, default to current company) | | |
| TASK-004 | Add customer relationship select with ->relationship('customer', 'name') | | |
| TASK-005 | Add payment_date DatePicker with default to today | | |
| TASK-006 | Add payment_method Select with all 7 options (cash, bank_transfer, credit_card, debit_card, cheque, online, other) | | |
| TASK-007 | Add amount TextInput with numeric validation, prefix from currency | | |
| TASK-008 | Add currency relationship select with ->relationship('currency', 'code') | | |

### Implementation Phase 2: PaymentReceiptResource Payment Details

- **GOAL-002**: Add payment method specific fields and reference tracking

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-009 | Add reference_number TextInput for transaction references | | |
| TASK-010 | Add bank_name and bank_branch TextInputs (visible when payment_method is bank/card) | | |
| TASK-011 | Add account_number TextInput (visible when payment_method is bank) | | |
| TASK-012 | Add cheque_number and cheque_date fields (visible when payment_method is cheque) | | |
| TASK-013 | Add transaction_id TextInput (visible when payment_method is online) | | |
| TASK-014 | Add notes Textarea for additional information | | |
| TASK-015 | Add status Select with options: draft, cleared, bounced, cancelled | | |

### Implementation Phase 3: PaymentReceiptResource Allocation Interface

- **GOAL-003**: Implement payment allocation to invoices with manual and automatic options

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-016 | Add Repeater field for manual invoice allocations | | |
| TASK-017 | In Repeater: Add sales_invoice_id Select filtered by customer and unpaid status | | |
| TASK-018 | In Repeater: Add invoice details display (invoice_number, due_date, outstanding_amount) | | |
| TASK-019 | In Repeater: Add allocated_amount TextInput with validation against outstanding | | |
| TASK-020 | Add calculated field for total allocated amount | | |
| TASK-021 | Add calculated field for unallocated amount (payment amount - allocated) | | |
| TASK-022 | Add validation to ensure allocated_amount <= payment amount | | |
| TASK-023 | Create custom Action for automatic FIFO allocation to oldest invoices | | |

### Implementation Phase 4: PaymentReceiptResource Table and Actions

- **GOAL-004**: Configure table display, filters, and GL posting actions

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-024 | Create table() method with payment_receipt_number, customer.name, payment_date columns | | |
| TASK-025 | Add amount column with Money formatting from currency | | |
| TASK-026 | Add payment_method column with badge styling | | |
| TASK-027 | Add status column with color-coded badges (draft=gray, cleared=success, bounced=danger, cancelled=warning) | | |
| TASK-028 | Add allocated_amount and unallocated_amount columns | | |
| TASK-029 | Add is_posted_to_gl boolean column with badge | | |
| TASK-030 | Add table filters: status, payment_method, date range, customer, posted to GL | | |
| TASK-031 | Add "Post to GL" Action calling PostPaymentReceipt with validation | | |
| TASK-032 | Add "Allocate to Invoices" bulk action for automatic allocation | | |

### Implementation Phase 5: CustomerCreditNoteResource Foundation

- **GOAL-005**: Create CustomerCreditNoteResource with form and validation

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-033 | Create CustomerCreditNoteResource class in `app/Filament/Accounting/Resources/` | | |
| TASK-034 | Implement form() method with Schema pattern following Filament v4.2 standards | | |
| TASK-035 | Add company selection field (disabled, default to current company) | | |
| TASK-036 | Add customer relationship select with ->relationship('customer', 'name') | | |
| TASK-037 | Add credit_note_number TextInput (disabled, auto-generated) | | |
| TASK-038 | Add credit_note_date DatePicker with default to today | | |
| TASK-039 | Add sales_invoice_id Select (nullable) with ->relationship('salesInvoice', 'invoice_number') | | |
| TASK-040 | Add reason Select with 6 options (return, price_adjustment, discount, error_correction, service_issue, other) | | |

### Implementation Phase 6: CustomerCreditNoteResource Amount and Details

- **GOAL-006**: Add credit amount fields and GL account linkage

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-041 | Add credit_amount TextInput with numeric validation and currency prefix | | |
| TASK-042 | Add currency relationship select with ->relationship('currency', 'code') | | |
| TASK-043 | Add exchange_rate TextInput (default 1.0, disabled if base currency) | | |
| TASK-044 | Add credit_amount_base_currency calculated field | | |
| TASK-045 | Add return_account_id Select linking to Account model for GL posting | | |
| TASK-046 | Add description Textarea for credit note details | | |
| TASK-047 | Add notes Textarea for internal notes | | |
| TASK-048 | Add status Select with options: draft, issued, applied, cancelled | | |

### Implementation Phase 7: CustomerCreditNoteResource Table and Actions

- **GOAL-007**: Configure table display, filters, and GL integration actions

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-049 | Create table() method with credit_note_number, customer.name, credit_note_date columns | | |
| TASK-050 | Add salesInvoice.invoice_number column (nullable) | | |
| TASK-051 | Add credit_amount column with Money formatting | | |
| TASK-052 | Add reason column with badge styling | | |
| TASK-053 | Add status column with color-coded badges | | |
| TASK-054 | Add is_posted_to_gl boolean column with badge | | |
| TASK-055 | Add table filters: status, reason, customer, date range, posted to GL, with/without invoice | | |
| TASK-056 | Add "Post to GL" Action calling PostCreditNote with validation | | |
| TASK-057 | Add "Apply to Invoice" Action if credit note is linked to invoice | | |

### Implementation Phase 8: Testing and Documentation

- **GOAL-008**: Create tests and update documentation for new resources

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-058 | Create Feature test for PaymentReceiptResource CRUD operations | | |
| TASK-059 | Create Feature test for payment allocation (manual and automatic) | | |
| TASK-060 | Create Feature test for GL posting from PaymentReceipt | | |
| TASK-061 | Create Feature test for CustomerCreditNoteResource CRUD operations | | |
| TASK-062 | Create Feature test for credit note application to invoice | | |
| TASK-063 | Create Feature test for GL posting from CustomerCreditNote | | |
| TASK-064 | Update ARCHITECTURAL_DECISIONS.md with resource implementation details | | |
| TASK-065 | Update PROGRESS_CHECKLIST.md marking PaymentReceiptResource and CustomerCreditNoteResource as complete | | |

## 3. Alternatives

- **ALT-001**: Using separate pages for payment allocation instead of Repeater field in form - Rejected because inline allocation provides better UX and immediate validation
- **ALT-002**: Implementing custom Livewire components instead of Filament resources - Rejected to maintain consistency with existing FilamentPHP patterns
- **ALT-003**: Auto-posting to GL on save instead of explicit action - Rejected to give users control and prevent accidental GL posts
- **ALT-004**: Using modal forms for quick payment entry - Deferred to future enhancement phase after basic CRUD is stable

## 4. Dependencies

- **DEP-001**: Filament v4.2+ must be installed and configured
- **DEP-002**: PaymentReceipt and CustomerCreditNote models must exist with all relationships
- **DEP-003**: PostPaymentReceipt and PostCreditNote Actions must be implemented and tested
- **DEP-004**: AllocatePaymentToInvoices Action must be available
- **DEP-005**: SalesInvoice model must have updatePaymentStatus() method
- **DEP-006**: Account model must be available for GL account selection
- **DEP-007**: Spatie Laravel Permission must be configured for authorization

## 5. Files

- **FILE-001**: `app/Filament/Accounting/Resources/PaymentReceiptResource.php` - Main resource class
- **FILE-002**: `app/Filament/Accounting/Resources/PaymentReceiptResource/Pages/ListPaymentReceipts.php` - List page
- **FILE-003**: `app/Filament/Accounting/Resources/PaymentReceiptResource/Pages/CreatePaymentReceipt.php` - Create page
- **FILE-004**: `app/Filament/Accounting/Resources/PaymentReceiptResource/Pages/EditPaymentReceipt.php` - Edit page
- **FILE-005**: `app/Filament/Accounting/Resources/CustomerCreditNoteResource.php` - Main resource class
- **FILE-006**: `app/Filament/Accounting/Resources/CustomerCreditNoteResource/Pages/ListCustomerCreditNotes.php` - List page
- **FILE-007**: `app/Filament/Accounting/Resources/CustomerCreditNoteResource/Pages/CreateCustomerCreditNote.php` - Create page
- **FILE-008**: `app/Filament/Accounting/Resources/CustomerCreditNoteResource/Pages/EditCustomerCreditNote.php` - Edit page
- **FILE-009**: `tests/Feature/PaymentReceiptResourceTest.php` - Feature tests for payment receipts
- **FILE-010**: `tests/Feature/CustomerCreditNoteResourceTest.php` - Feature tests for credit notes

## 6. Testing

- **TEST-001**: Test PaymentReceipt resource can create new payment with all fields
- **TEST-002**: Test manual payment allocation creates allocation records correctly
- **TEST-003**: Test automatic FIFO allocation distributes payment to oldest invoices first
- **TEST-004**: Test validation prevents over-allocation beyond payment amount
- **TEST-005**: Test "Post to GL" action creates journal entry with correct accounts (Debit Cash/Bank, Credit AR)
- **TEST-006**: Test payment allocation updates invoice outstanding_amount and payment status
- **TEST-007**: Test CustomerCreditNote resource can create credit note with optional invoice link
- **TEST-008**: Test credit note validation ensures credit_amount <= original invoice amount
- **TEST-009**: Test "Post to GL" action creates journal entry (Debit Sales Returns, Credit AR)
- **TEST-010**: Test "Apply to Invoice" reduces invoice outstanding amount correctly
- **TEST-011**: Test status transitions follow defined workflows (draft → issued → applied/cancelled)
- **TEST-012**: Test filters correctly narrow down records in table views

## 7. Risks & Assumptions

### Risks
- **RISK-001**: Complex payment allocation interface may confuse users - Mitigation: Add clear instructions and validation messages
- **RISK-002**: Concurrent payment allocations could cause race conditions - Mitigation: Use database transactions and locking
- **RISK-003**: Currency conversion errors in multi-currency payments - Mitigation: Validate exchange rates before processing
- **RISK-004**: GL posting errors could leave data in inconsistent state - Mitigation: Wrap in transactions with proper rollback

### Assumptions
- **ASSUMPTION-001**: Users understand double-entry accounting principles for GL posting
- **ASSUMPTION-002**: Exchange rates are current and accurate when posting multi-currency transactions
- **ASSUMPTION-003**: Payment allocations are manually reviewed before GL posting
- **ASSUMPTION-004**: Credit notes require approval before being issued (implemented via status workflow)

## 8. Related Specifications / Further Reading

- [System Architecture Specification](../spec/architecture-nexus-erp.md)
- [Accounting Module Planning](../ACCOUNTING_MODULE_PLANNING.md)
- [Architectural Decisions](../ARCHITECTURAL_DECISIONS.md)
- [FilamentPHP v4 Documentation](https://filamentphp.com/docs/4.x)
- [Spatie Model Status Documentation](https://github.com/spatie/laravel-model-status)
- [Laravel Actions Documentation](https://laravelactions.com/)