---
goal: Implement Banking & Cash Management Module
version: 1.0
date_created: 2025-11-05
last_updated: 2025-11-05
owner: Development Team
status: 'Planned'
tags: ["feature", "accounting", "banking", "cash", "reconciliation"]
---

# Implement Banking & Cash Management Module

![Status: Planned](https://img.shields.io/badge/status-Planned-blue)

This implementation plan covers the Banking & Cash Management module including bank account setup, transaction recording, bank reconciliation, and payment gateway integration. This module is critical for tracking cash flow and ensuring bank statement accuracy.

## 1. Requirements & Constraints

### Functional Requirements
- **REQ-001**: Support multiple bank accounts per company with multi-currency
- **REQ-002**: Record bank transactions (deposits, withdrawals, transfers) with GL integration
- **REQ-003**: Implement bank reconciliation with auto-matching and manual matching
- **REQ-004**: Import bank statements from CSV/Excel formats
- **REQ-005**: Track outstanding cheques and deposits in transit
- **REQ-006**: Support payment gateway integration (Stripe, PayPal) for online payments
- **REQ-007**: Generate bank reconciliation reports

### Technical Constraints
- **CON-001**: Bank transactions must use HasSerialNumbering with BT- prefix
- **CON-002**: Reconciliation must handle timing differences (outstanding items)
- **CON-003**: Multi-currency accounts must track balances in both account and base currency
- **CON-004**: Import must validate CSV structure before processing

### Performance Requirements
- **PERF-001**: Auto-matching algorithm must handle 10,000+ transactions efficiently
- **PERF-002**: Import must process large bank statements (50,000+ rows) without timeout
- **PERF-003**: Reconciliation matching should complete within 30 seconds

### Integration Requirements
- **INT-001**: Link to GL accounts for cash/bank asset accounts
- **INT-002**: Integrate with payment gateways via webhooks
- **INT-003**: Support scheduled jobs for automatic statement imports
- **INT-004**: Link to PaymentReceipt and PaymentVoucher for reconciliation

## 2. Implementation Steps

### Implementation Phase 1: Bank Account Model

- **GOAL-001**: Create BankAccount model with multi-currency support

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-001 | Create bank_accounts migration | | |
| TASK-002 | Add company_id, account_name, account_number fields | | |
| TASK-003 | Add bank_name, bank_branch, swift_code, iban fields | | |
| TASK-004 | Add account_type enum (savings, checking, credit_card, money_market) | | |
| TASK-005 | Add currency_id for account native currency | | |
| TASK-006 | Add gl_account_id linking to GL cash/bank account | | |
| TASK-007 | Add opening_balance, opening_balance_date fields | | |
| TASK-008 | Add current_balance field (updated by transactions) | | |
| TASK-009 | Add is_active boolean flag | | |
| TASK-010 | Create BankAccount model with relationships | | |

### Implementation Phase 2: Bank Transaction Model

- **GOAL-002**: Create BankTransaction model for recording bank activities

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-011 | Create bank_transactions migration | | |
| TASK-012 | Add serial_number with BT-YYYY-XXXX format | | |
| TASK-013 | Add bank_account_id, transaction_date fields | | |
| TASK-014 | Add transaction_type enum (deposit, withdrawal, transfer, bank_charge, interest) | | |
| TASK-015 | Add amount, description, reference_number fields | | |
| TASK-016 | Add payee_payer field for tracking transaction counterparty | | |
| TASK-017 | Add transfer_to_bank_account_id (nullable, for inter-account transfers) | | |
| TASK-018 | Add journal_entry_id, is_posted_to_gl, posted_to_gl_at fields | | |
| TASK-019 | Add is_reconciled, reconciled_at fields | | |
| TASK-020 | Create BankTransaction model with HasSerialNumbering trait | | |

### Implementation Phase 3: Bank Reconciliation Model

- **GOAL-003**: Create BankReconciliation model for statement matching

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-021 | Create bank_reconciliations migration | | |
| TASK-022 | Add serial_number with BR-YYYY-XXXX format | | |
| TASK-023 | Add bank_account_id, reconciliation_date fields | | |
| TASK-024 | Add statement_opening_balance, statement_closing_balance fields | | |
| TASK-025 | Add book_opening_balance, book_closing_balance fields | | |
| TASK-026 | Add total_deposits, total_withdrawals fields | | |
| TASK-027 | Add outstanding_deposits, outstanding_cheques fields | | |
| TASK-028 | Add status enum (draft, completed, approved) | | |
| TASK-029 | Add approved_by, approved_at fields | | |
| TASK-030 | Create BankReconciliation model with relationships | | |

### Implementation Phase 4: Reconciliation Matching

- **GOAL-004**: Implement matching system for reconciliation items

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-031 | Create reconciliation_items migration | | |
| TASK-032 | Add bank_reconciliation_id, bank_transaction_id fields | | |
| TASK-033 | Add statement_amount, statement_date, statement_reference fields | | |
| TASK-034 | Add match_status enum (matched, unmatched, outstanding_cheque, outstanding_deposit) | | |
| TASK-035 | Add difference_amount for discrepancies | | |
| TASK-036 | Create ReconciliationItem model | | |
| TASK-037 | Create AutoMatchBankTransactions Action | | |
| TASK-038 | Implement matching by amount and date proximity (±3 days) | | |
| TASK-039 | Implement matching by reference number | | |
| TASK-040 | Handle outstanding items separately | | |

### Implementation Phase 5: Statement Import

- **GOAL-005**: Implement CSV/Excel bank statement import functionality

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-041 | Create ImportBankStatement Action | | |
| TASK-042 | Validate CSV structure (required columns: date, description, amount) | | |
| TASK-043 | Parse dates in various formats (YYYY-MM-DD, DD/MM/YYYY, etc.) | | |
| TASK-044 | Detect transaction type from description keywords | | |
| TASK-045 | Create temporary statement import records | | |
| TASK-046 | Preview import with validation errors | | |
| TASK-047 | Confirm import to create reconciliation items | | |
| TASK-048 | Handle duplicate detection by reference and amount | | |
| TASK-049 | Support mapping custom CSV columns to fields | | |

### Implementation Phase 6: GL Integration Actions

- **GOAL-006**: Create Actions for posting bank transactions to GL

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-050 | Create PostBankDeposit Action | | |
| TASK-051 | Create journal entry: Debit Bank, Credit source_account (AR/Revenue/Other) | | |
| TASK-052 | Create PostBankWithdrawal Action | | |
| TASK-053 | Create journal entry: Debit Expense/AP, Credit Bank | | |
| TASK-054 | Create PostBankTransfer Action | | |
| TASK-055 | Create journal entries for both accounts (Debit ToBank, Credit FromBank) | | |
| TASK-056 | Handle currency conversion for multi-currency transfers | | |
| TASK-057 | Create PostBankCharge Action for bank fees | | |
| TASK-058 | Create PostBankInterest Action for interest income | | |

### Implementation Phase 7: Payment Gateway Integration

- **GOAL-007**: Integrate Stripe and PayPal payment gateways

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-059 | Create payment_gateway_transactions migration | | |
| TASK-060 | Add gateway_type enum (stripe, paypal, other) | | |
| TASK-061 | Add gateway_transaction_id, gateway_status, gateway_response fields | | |
| TASK-062 | Add bank_transaction_id linking to auto-created transaction | | |
| TASK-063 | Create ProcessStripeWebhook Action | | |
| TASK-064 | Parse Stripe payment success events | | |
| TASK-065 | Auto-create BankTransaction for successful payment | | |
| TASK-066 | Calculate and record gateway fees | | |
| TASK-067 | Create ProcessPayPalWebhook Action (similar to Stripe) | | |
| TASK-068 | Handle refunds and chargebacks | | |

### Implementation Phase 8: Cheque Management

- **GOAL-008**: Implement cheque book and post-dated cheque tracking

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-069 | Create cheques migration | | |
| TASK-070 | Add bank_account_id, cheque_number, cheque_date fields | | |
| TASK-071 | Add payee_name, amount fields | | |
| TASK-072 | Add is_post_dated boolean flag | | |
| TASK-073 | Add maturity_date for PDC | | |
| TASK-074 | Add status enum (pending, cleared, bounced, cancelled) | | |
| TASK-075 | Add payment_voucher_id or payment_receipt_id linkage | | |
| TASK-076 | Create Cheque model with relationships | | |
| TASK-077 | Create scheduled job for PDC maturity alerts | | |
| TASK-078 | Create UpdateChequeStatus Action for clearance tracking | | |

### Implementation Phase 9: Testing and Documentation

- **GOAL-009**: Create tests and documentation for banking module

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-079 | Create unit tests for BankAccount model calculations | | |
| TASK-080 | Create unit tests for auto-matching algorithm | | |
| TASK-081 | Create feature test for bank transaction posting to GL | | |
| TASK-082 | Create feature test for statement import workflow | | |
| TASK-083 | Create feature test for bank reconciliation process | | |
| TASK-084 | Create feature test for payment gateway webhook processing | | |
| TASK-085 | Update ARCHITECTURAL_DECISIONS.md with banking module | | |
| TASK-086 | Update PROGRESS_CHECKLIST.md with completion status | | |
| TASK-087 | Create banking module documentation | | |

## 3. Alternatives

- **ALT-001**: Using third-party reconciliation service (e.g., Plaid) - Deferred for future integration to control costs
- **ALT-002**: Real-time balance updates vs scheduled - Chose scheduled updates to reduce database load
- **ALT-003**: Manual cheque entry vs scanning - Started with manual, OCR deferred to future phase
- **ALT-004**: Automatic GL posting on transaction vs manual - Chose manual posting for control and review

## 4. Dependencies

- **DEP-001**: Account model must have cash/bank account types
- **DEP-002**: JournalEntry model for GL integration
- **DEP-003**: PaymentReceipt and PaymentVoucher for reconciliation linking
- **DEP-004**: Spatie Laravel Media Library for statement file attachments
- **DEP-005**: Laravel Queue for processing large imports
- **DEP-006**: Payment gateway API keys configured in environment

## 5. Files

- **FILE-001**: `database/migrations/YYYY_MM_DD_create_bank_accounts_table.php`
- **FILE-002**: `database/migrations/YYYY_MM_DD_create_bank_transactions_table.php`
- **FILE-003**: `database/migrations/YYYY_MM_DD_create_bank_reconciliations_table.php`
- **FILE-004**: `database/migrations/YYYY_MM_DD_create_reconciliation_items_table.php`
- **FILE-005**: `database/migrations/YYYY_MM_DD_create_payment_gateway_transactions_table.php`
- **FILE-006**: `database/migrations/YYYY_MM_DD_create_cheques_table.php`
- **FILE-007**: `app/Models/BankAccount.php`
- **FILE-008**: `app/Models/BankTransaction.php`
- **FILE-009**: `app/Models/BankReconciliation.php`
- **FILE-010**: `app/Models/ReconciliationItem.php`
- **FILE-011**: `app/Models/Cheque.php`
- **FILE-012**: `app/Actions/ImportBankStatement.php`
- **FILE-013**: `app/Actions/AutoMatchBankTransactions.php`
- **FILE-014**: `app/Actions/PostBankTransaction.php`
- **FILE-015**: `app/Actions/ProcessStripeWebhook.php`

## 6. Testing

- **TEST-001**: Test BankAccount balance updates correctly with deposits/withdrawals
- **TEST-002**: Test BankTransaction serial number generation with BT- prefix
- **TEST-003**: Test auto-matching finds transactions by amount and date range
- **TEST-004**: Test auto-matching by reference number
- **TEST-005**: Test statement import validates CSV structure
- **TEST-006**: Test import handles various date formats correctly
- **TEST-007**: Test reconciliation calculates book vs bank balance correctly
- **TEST-008**: Test outstanding cheques and deposits tracked separately
- **TEST-009**: Test GL posting creates correct journal entries for all transaction types
- **TEST-010**: Test Stripe webhook creates bank transaction automatically
- **TEST-011**: Test payment gateway fees calculated and recorded
- **TEST-012**: Test PDC maturity alert scheduled job

## 7. Risks & Assumptions

### Risks
- **RISK-001**: Auto-matching may have false positives - Mitigation: Allow manual override and review before finalization
- **RISK-002**: Large statement imports may timeout - Mitigation: Use queue jobs and batch processing
- **RISK-003**: Payment gateway webhooks may fail - Mitigation: Implement retry logic and manual verification
- **RISK-004**: Multi-currency conversion errors - Mitigation: Validate exchange rates and use BCMath precision

### Assumptions
- **ASSUMPTION-001**: Bank statements follow consistent format from each bank
- **ASSUMPTION-002**: Reconciliation performed monthly as standard practice
- **ASSUMPTION-003**: Payment gateway webhooks are reliable for most transactions
- **ASSUMPTION-004**: Users review auto-matched transactions before approval

## 8. Related Specifications / Further Reading

- [System Architecture Specification](../spec/architecture-nexus-erp.md)
- [Accounting Module Planning](../ACCOUNTING_MODULE_PLANNING.md)
- [Stripe API Documentation](https://stripe.com/docs/api)
- [PayPal Webhooks Guide](https://developer.paypal.com/api/rest/webhooks/)
- [CSV Import Best Practices](https://laravelexcel.com/)