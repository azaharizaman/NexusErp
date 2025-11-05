---
goal: Implement Accounts Payable (AP) Module Foundation
version: 1.0
date_created: 2025-11-05
last_updated: 2025-11-05
owner: Development Team
status: 'Planned'
tags: ["feature", "accounting", "ap", "payable", "supplier"]
---

# Implement Accounts Payable (AP) Module Foundation

![Status: Planned](https://img.shields.io/badge/status-Planned-blue)

This implementation plan covers the foundation of the Accounts Payable (AP) module, including supplier invoice management, payment vouchers, debit notes, and GL integration. The AP module mirrors the AR module structure but focuses on managing payments to suppliers.

## 1. Requirements & Constraints

### Functional Requirements
- **REQ-001**: Integrate with Purchase Module's SupplierInvoice model
- **REQ-002**: Implement payment voucher system with AP- prefix serial numbering
- **REQ-003**: Support batch payment processing for multiple suppliers
- **REQ-004**: Implement three-way matching (PO-GRN-Invoice) validation
- **REQ-005**: Support supplier debit notes with DN- prefix
- **REQ-006**: Implement payment terms and due date calculation
- **REQ-007**: Support payment holds and approval workflows

### Technical Constraints
- **CON-001**: Must reuse existing SupplierInvoice model from Purchase Module
- **CON-002**: Must implement HasSerialNumbering trait for transactional models
- **CON-003**: Must follow double-entry bookkeeping for GL integration
- **CON-004**: Must use BCMath for financial calculations

### Integration Requirements
- **INT-001**: Integrate with PurchaseOrder model for three-way matching
- **INT-002**: Integrate with GRN (Goods Received Note) model when available
- **INT-003**: Link to GL Account model for expense and AP accounts
- **INT-004**: Use spatie/laravel-model-status for workflow management

### Security Requirements
- **SEC-001**: Payment approval workflow must enforce authorization
- **SEC-002**: Payment hold requires specific permission
- **SEC-003**: GL posting requires dedicated permission
- **SEC-004**: Audit trail must track all approvers and posting users

## 2. Implementation Steps

### Implementation Phase 1: Supplier Invoice Integration

- **GOAL-001**: Enhance SupplierInvoice model from Purchase Module for AP integration

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-001 | Review existing SupplierInvoice model structure and relationships | | |
| TASK-002 | Add journal_entry_id field for GL integration | | |
| TASK-003 | Add is_posted_to_gl and posted_to_gl_at fields | | |
| TASK-004 | Add payment tracking fields: paid_amount, outstanding_amount | | |
| TASK-005 | Add payment_status enum (unpaid, partially_paid, paid, overdue) | | |
| TASK-006 | Implement calculateOutstanding() method | | |
| TASK-007 | Implement updatePaymentStatus() method | | |
| TASK-008 | Add isFullyPaid() and isOverdue() helper methods | | |

### Implementation Phase 2: Payment Voucher Model

- **GOAL-002**: Create PaymentVoucher model for supplier payments

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-009 | Create payment_vouchers migration with all required fields | | |
| TASK-010 | Add serial_number with PV-YYYY-XXXX format | | |
| TASK-011 | Add company_id, supplier_id, payment_date fields | | |
| TASK-012 | Add amount, currency_id, exchange_rate fields | | |
| TASK-013 | Add payment_method enum (same as PaymentReceipt) | | |
| TASK-014 | Add payment reference fields (bank_name, cheque_number, etc.) | | |
| TASK-015 | Add status enum (draft, approved, paid, cancelled) | | |
| TASK-016 | Add is_on_hold boolean flag | | |
| TASK-017 | Add approved_by, approved_at fields | | |
| TASK-018 | Add journal_entry_id, is_posted_to_gl, posted_to_gl_at fields | | |

### Implementation Phase 3: Payment Voucher Allocation

- **GOAL-003**: Implement payment allocation system linking vouchers to invoices

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-019 | Create payment_voucher_allocations migration | | |
| TASK-020 | Add payment_voucher_id, supplier_invoice_id fields | | |
| TASK-021 | Add allocated_amount field with decimal precision | | |
| TASK-022 | Add unique constraint on payment_voucher_id + supplier_invoice_id | | |
| TASK-023 | Create PaymentVoucherAllocation model with relationships | | |
| TASK-024 | Add forPayment() and forInvoice() scopes | | |
| TASK-025 | Create PaymentVoucher model with HasSerialNumbering trait | | |
| TASK-026 | Add allocations() relationship to PaymentVoucher | | |
| TASK-027 | Implement allocateToInvoice() method with validation | | |
| TASK-028 | Implement recalculateAllocations() method | | |

### Implementation Phase 4: Supplier Debit Note Model

- **GOAL-004**: Create SupplierDebitNote model for purchase returns and adjustments

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-029 | Create supplier_debit_notes migration | | |
| TASK-030 | Add serial_number with DN-YYYY-XXXX format | | |
| TASK-031 | Add company_id, supplier_id, supplier_invoice_id (nullable) fields | | |
| TASK-032 | Add debit_note_number, debit_note_date fields | | |
| TASK-033 | Add debit_amount, currency_id, exchange_rate fields | | |
| TASK-034 | Add reason enum (return, price_adjustment, quality_issue, shipping_error, other) | | |
| TASK-035 | Add description, notes fields | | |
| TASK-036 | Add status enum (draft, issued, applied, cancelled) | | |
| TASK-037 | Add journal_entry_id, is_posted_to_gl, posted_to_gl_at fields | | |
| TASK-038 | Create SupplierDebitNote model with relationships | | |
| TASK-039 | Implement applyToInvoice() method | | |
| TASK-040 | Add validation to prevent debit > invoice amount | | |

### Implementation Phase 5: GL Integration Actions

- **GOAL-005**: Create Actions for posting AP transactions to General Ledger

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-041 | Create PostSupplierInvoice Action class | | |
| TASK-042 | Implement validation for posting (invoice approved, not already posted) | | |
| TASK-043 | Create journal entry: Debit Expense, Credit AP | | |
| TASK-044 | Handle tax payable entries if applicable | | |
| TASK-045 | Create PostPaymentVoucher Action class | | |
| TASK-046 | Validate payment has allocations before posting | | |
| TASK-047 | Create journal entry: Debit AP, Credit Cash/Bank | | |
| TASK-048 | Update supplier invoice outstanding amounts | | |
| TASK-049 | Create PostSupplierDebitNote Action class | | |
| TASK-050 | Create journal entry: Debit AP, Credit Purchase Returns | | |
| TASK-051 | Update supplier invoice outstanding amount | | |
| TASK-052 | Wrap all posting operations in database transactions | | |

### Implementation Phase 6: Payment Processing Actions

- **GOAL-006**: Create Actions for payment allocation and approval workflows

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-053 | Create AllocatePaymentToSupplierInvoices Action | | |
| TASK-054 | Implement manual allocation with validation | | |
| TASK-055 | Implement automatic FIFO allocation logic | | |
| TASK-056 | Update PaymentVoucher allocated/unallocated amounts | | |
| TASK-057 | Update SupplierInvoice paid_amount and payment_status | | |
| TASK-058 | Create ApprovePaymentVoucher Action | | |
| TASK-059 | Validate payment voucher has allocations | | |
| TASK-060 | Update approved_by and approved_at fields | | |
| TASK-061 | Transition status to 'approved' | | |
| TASK-062 | Create PlacePaymentOnHold Action | | |
| TASK-063 | Update is_on_hold flag with reason | | |
| TASK-064 | Send notification to finance team | | |

### Implementation Phase 7: Three-Way Matching

- **GOAL-007**: Implement three-way matching validation for invoice approval

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-065 | Create InvoiceMatching model for tracking match status | | |
| TASK-066 | Add purchase_order_id, grn_id, supplier_invoice_id fields | | |
| TASK-067 | Add match_status enum (matched, quantity_mismatch, price_mismatch, not_matched) | | |
| TASK-068 | Add variance_amount, variance_reason fields | | |
| TASK-069 | Create ValidateThreeWayMatch Action | | |
| TASK-070 | Compare PO quantities with GRN quantities | | |
| TASK-071 | Compare PO prices with invoice prices | | |
| TASK-072 | Calculate variance amounts | | |
| TASK-073 | Generate match report with discrepancies | | |
| TASK-074 | Block invoice approval if mismatch exceeds tolerance | | |

### Implementation Phase 8: Testing and Documentation

- **GOAL-008**: Create comprehensive tests and update documentation

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-075 | Create unit tests for PaymentVoucher model methods | | |
| TASK-076 | Create unit tests for SupplierDebitNote model methods | | |
| TASK-077 | Create feature tests for payment allocation | | |
| TASK-078 | Create feature tests for GL posting actions | | |
| TASK-079 | Create feature tests for three-way matching | | |
| TASK-080 | Create feature tests for approval workflows | | |
| TASK-081 | Update ARCHITECTURAL_DECISIONS.md with AP module details | | |
| TASK-082 | Update PROGRESS_CHECKLIST.md with AP completion status | | |
| TASK-083 | Create AP module documentation in docs/ | | |

## 3. Alternatives

- **ALT-001**: Using single Payment model for both AR and AP - Rejected to maintain clear separation between customer and supplier payments
- **ALT-002**: Auto-approving payments under threshold amount - Deferred to future enhancement for risk management
- **ALT-003**: Implementing early payment discount tracking - Deferred to Phase 2 after basic AP is stable
- **ALT-004**: Integrating with external payment gateways for supplier payments - Deferred for future integration phase

## 4. Dependencies

- **DEP-001**: SupplierInvoice model must exist in Purchase Module
- **DEP-002**: PurchaseOrder model must be available for three-way matching
- **DEP-003**: GRN (Goods Received Note) model should be available but optional
- **DEP-004**: Account model must have expense and AP account types
- **DEP-005**: JournalEntry model and posting engine must be available
- **DEP-006**: azaharizaman/laravel-serial-numbering package must be installed
- **DEP-007**: Spatie Laravel Permission must be configured for approvals

## 5. Files

- **FILE-001**: `database/migrations/YYYY_MM_DD_create_payment_vouchers_table.php`
- **FILE-002**: `database/migrations/YYYY_MM_DD_create_payment_voucher_allocations_table.php`
- **FILE-003**: `database/migrations/YYYY_MM_DD_create_supplier_debit_notes_table.php`
- **FILE-004**: `database/migrations/YYYY_MM_DD_create_invoice_matching_table.php`
- **FILE-005**: `database/migrations/YYYY_MM_DD_enhance_supplier_invoices_for_ap.php`
- **FILE-006**: `app/Models/PaymentVoucher.php`
- **FILE-007**: `app/Models/PaymentVoucherAllocation.php`
- **FILE-008**: `app/Models/SupplierDebitNote.php`
- **FILE-009**: `app/Models/InvoiceMatching.php`
- **FILE-010**: `app/Actions/PostSupplierInvoice.php`
- **FILE-011**: `app/Actions/PostPaymentVoucher.php`
- **FILE-012**: `app/Actions/PostSupplierDebitNote.php`
- **FILE-013**: `app/Actions/AllocatePaymentToSupplierInvoices.php`
- **FILE-014**: `app/Actions/ApprovePaymentVoucher.php`
- **FILE-015**: `app/Actions/ValidateThreeWayMatch.php`

## 6. Testing

- **TEST-001**: Test PaymentVoucher serial number generation with PV-YYYY-XXXX format
- **TEST-002**: Test payment allocation to multiple supplier invoices
- **TEST-003**: Test automatic FIFO allocation logic
- **TEST-004**: Test validation prevents over-allocation beyond voucher amount
- **TEST-005**: Test PostSupplierInvoice creates correct GL entries (Debit Expense, Credit AP)
- **TEST-006**: Test PostPaymentVoucher creates correct GL entries (Debit AP, Credit Bank)
- **TEST-007**: Test PostSupplierDebitNote reduces AP correctly
- **TEST-008**: Test three-way matching detects quantity mismatches
- **TEST-009**: Test three-way matching detects price variances
- **TEST-010**: Test payment approval workflow transitions
- **TEST-011**: Test payment hold functionality
- **TEST-012**: Test SupplierInvoice payment status updates correctly

## 7. Risks & Assumptions

### Risks
- **RISK-001**: Three-way matching complexity may delay implementation - Mitigation: Start with basic matching, enhance incrementally
- **RISK-002**: Batch payment processing may have performance issues - Mitigation: Use queue jobs for large batches
- **RISK-003**: Currency conversion errors in multi-currency payments - Mitigation: Validate exchange rates and use BCMath
- **RISK-004**: GRN model may not be available initially - Mitigation: Make GRN matching optional, implement two-way matching first

### Assumptions
- **ASSUMPTION-001**: Supplier invoices from Purchase Module have consistent structure
- **ASSUMPTION-002**: Payment approval workflows follow company policy
- **ASSUMPTION-003**: Three-way matching tolerances are configurable per company
- **ASSUMPTION-004**: Batch payments processed sequentially to avoid race conditions

## 8. Related Specifications / Further Reading

- [System Architecture Specification](../spec/architecture-nexus-erp.md)
- [Accounting Module Planning](../ACCOUNTING_MODULE_PLANNING.md)
- [AR Module Implementation](feature-ar-filament-resources-1.md)
- [Purchase Management Planning](../PURCHASE_MANAGEMENT_MODULES_PLANNING.md)
- [Architectural Decisions](../ARCHITECTURAL_DECISIONS.md)
- [Laravel Actions Documentation](https://laravelactions.com/)