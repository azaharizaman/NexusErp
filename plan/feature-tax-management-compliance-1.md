---
goal: Implement Tax Management & Compliance Module
version: 1.0
date_created: 2025-11-05
last_updated: 2025-11-05
owner: Development Team
status: 'Planned'
tags: ["feature", "accounting", "tax", "vat", "gst", "compliance"]
---

# Implement Tax Management & Compliance Module

![Status: Planned](https://img.shields.io/badge/status-Planned-blue)

This implementation plan covers the Tax Management & Compliance module including tax code configuration (VAT/GST/Sales Tax), tax calculation on transactions, tax collection and payment tracking, reverse charge and exemption handling, tax reporting and returns generation, and multi-jurisdiction tax support.

## 1. Requirements & Constraints

### Functional Requirements
- **REQ-001**: Define tax codes with rates, types, and effective dates
- **REQ-002**: Support multiple tax types (VAT, GST, Sales Tax, Withholding Tax)
- **REQ-003**: Calculate tax on invoices, receipts, and payments automatically
- **REQ-004**: Track tax collected (output tax) and tax paid (input tax)
- **REQ-005**: Support reverse charge mechanism for B2B transactions
- **REQ-006**: Handle tax exemptions and zero-rated supplies
- **REQ-007**: Generate tax reports by jurisdiction and tax period
- **REQ-008**: Create tax returns (e.g., VAT returns, GST returns) with filing support
- **REQ-009**: Support tax adjustments, refunds, and corrections
- **REQ-010**: Provide tax audit trail for compliance

### Technical Constraints
- **CON-001**: Tax calculations must use BCMath for precision
- **CON-002**: Tax amounts must be stored separately from base amounts
- **CON-003**: Tax rates must support historical tracking for audit compliance
- **CON-004**: Tax returns must be immutable once submitted

### Performance Requirements
- **PERF-001**: Tax calculation must not add more than 100ms to transaction posting
- **PERF-002**: Tax report generation must complete within 5 seconds for 10,000+ transactions
- **PERF-003**: Tax return preparation must handle 50,000+ transactions efficiently

### Integration Requirements
- **INT-001**: Integrate with SalesInvoice and PurchaseInvoice for tax calculation
- **INT-002**: Link to JournalEntry for tax GL posting
- **INT-003**: Link to Account for tax payable/receivable accounts
- **INT-004**: Integrate with PaymentReceipt and PaymentVoucher for tax settlement tracking
- **INT-005**: Link to FiscalYear and AccountingPeriod for tax period definition

## 2. Implementation Steps

### Implementation Phase 1: Tax Jurisdiction Model

- **GOAL-001**: Create TaxJurisdiction model for tax authority definition

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-001 | Create tax_jurisdictions migration | | |
| TASK-002 | Add company_id, jurisdiction_name, jurisdiction_code fields | | |
| TASK-003 | Add jurisdiction_type enum (national, state, city, special) | | |
| TASK-004 | Add tax_authority_name, tax_id_format fields | | |
| TASK-005 | Add reporting_frequency enum (monthly, quarterly, annual) | | |
| TASK-006 | Add next_filing_due_date field | | |
| TASK-007 | Add is_active boolean flag | | |
| TASK-008 | Create TaxJurisdiction model | | |

### Implementation Phase 2: Tax Code Model

- **GOAL-002**: Create TaxCode model with rates and rules

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-009 | Create tax_codes migration | | |
| TASK-010 | Add tax_jurisdiction_id, tax_code, tax_name fields | | |
| TASK-011 | Add tax_type enum (vat, gst, sales_tax, withholding_tax, excise, customs) | | |
| TASK-012 | Add default_rate_percentage field | | |
| TASK-013 | Add is_compound boolean (calculates on subtotal + other taxes) | | |
| TASK-014 | Add is_inclusive boolean (included in price vs added on top) | | |
| TASK-015 | Add tax_gl_account_id (tax payable/receivable account) | | |
| TASK-016 | Add tax_expense_account_id (for non-recoverable tax) | | |
| TASK-017 | Add is_recoverable boolean (can claim input tax credit) | | |
| TASK-018 | Add is_active boolean flag | | |
| TASK-019 | Create TaxCode model with relationships | | |

### Implementation Phase 3: Tax Rate History Model

- **GOAL-003**: Track tax rate changes over time

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-020 | Create tax_rates migration | | |
| TASK-021 | Add tax_code_id, rate_percentage fields | | |
| TASK-022 | Add effective_from_date, effective_to_date fields | | |
| TASK-023 | Add is_active boolean flag | | |
| TASK-024 | Create TaxRate model | | |
| TASK-025 | Add validation: no overlapping effective date ranges | | |
| TASK-026 | Create GetApplicableTaxRate Action | | |
| TASK-027 | Accept tax_code_id, transaction_date | | |
| TASK-028 | Return tax rate effective on transaction date | | |

### Implementation Phase 4: Transaction Tax Lines

- **GOAL-004**: Create model for storing tax breakdown per transaction line

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-029 | Create transaction_tax_lines migration | | |
| TASK-030 | Add taxable_type, taxable_id (polymorphic to InvoiceLine, etc.) | | |
| TASK-031 | Add tax_code_id, tax_rate_percentage fields | | |
| TASK-032 | Add taxable_amount, tax_amount fields | | |
| TASK-033 | Add is_reverse_charge, is_exempt boolean flags | | |
| TASK-034 | Add exemption_reason field (if exempt) | | |
| TASK-035 | Create TransactionTaxLine model | | |
| TASK-036 | Add morphMany relationship to InvoiceLine models | | |

### Implementation Phase 5: Tax Calculation Action

- **GOAL-005**: Implement tax calculation logic

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-037 | Create CalculateTransactionTax Action | | |
| TASK-038 | Accept line_amount, tax_code_id, transaction_date, is_inclusive | | |
| TASK-039 | Get applicable tax rate for transaction date | | |
| TASK-040 | If inclusive: tax = amount - (amount / (1 + rate)) | | |
| TASK-041 | If exclusive: tax = amount * rate | | |
| TASK-042 | Use BCMath for precision | | |
| TASK-043 | Round to 2 decimal places (or currency precision) | | |
| TASK-044 | Return taxable_amount, tax_amount, total_amount | | |
| TASK-045 | Handle compound tax calculation (cascading taxes) | | |
| TASK-046 | Create CreateTransactionTaxLine Action | | |
| TASK-047 | Create TransactionTaxLine record with calculated values | | |

### Implementation Phase 6: Tax Exemption and Reverse Charge

- **GOAL-006**: Implement special tax handling rules

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-048 | Create TaxExemption model for tracking exemptions | | |
| TASK-049 | Add entity_type, entity_id (customer/supplier), exemption_type fields | | |
| TASK-050 | Add exemption_certificate_number, expiry_date fields | | |
| TASK-051 | Add tax_code_id (which tax is exempt) | | |
| TASK-052 | Create CheckTaxExemption Action | | |
| TASK-053 | Query exemptions for entity and tax code | | |
| TASK-054 | Validate exemption is active and not expired | | |
| TASK-055 | Create ApplyReverseCharge Action | | |
| TASK-056 | Set is_reverse_charge = true on tax line | | |
| TASK-057 | Set tax_amount = 0 (buyer will self-assess) | | |
| TASK-058 | Add note indicating reverse charge applies | | |

### Implementation Phase 7: Tax Return Model

- **GOAL-007**: Create TaxReturn model for filing tracking

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-059 | Create tax_returns migration | | |
| TASK-060 | Add serial_number with TR-YYYY-XXXX format | | |
| TASK-061 | Add tax_jurisdiction_id, tax_period_from, tax_period_to fields | | |
| TASK-062 | Add fiscal_year_id, accounting_period_id fields | | |
| TASK-063 | Add total_output_tax, total_input_tax, net_tax_payable fields | | |
| TASK-064 | Add status enum (draft, submitted, filed, paid) | | |
| TASK-065 | Add submitted_at, filed_at, payment_due_date fields | | |
| TASK-066 | Add payment_journal_entry_id (when paid) | | |
| TASK-067 | Create TaxReturn model with HasStatuses trait | | |

### Implementation Phase 8: Tax Return Line Items

- **GOAL-008**: Track detailed tax return line items

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-068 | Create tax_return_lines migration | | |
| TASK-069 | Add tax_return_id, tax_code_id fields | | |
| TASK-070 | Add line_type enum (output_tax, input_tax, adjustment) | | |
| TASK-071 | Add taxable_amount, tax_amount fields | | |
| TASK-072 | Add description field | | |
| TASK-073 | Create TaxReturnLine model | | |

### Implementation Phase 9: Generate Tax Return Action

- **GOAL-009**: Automate tax return preparation

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-074 | Create GenerateTaxReturn Action | | |
| TASK-075 | Accept tax_jurisdiction_id, period_from, period_to | | |
| TASK-076 | Query all sales invoices with output tax (tax collected) | | |
| TASK-077 | Sum output tax by tax code | | |
| TASK-078 | Query all purchase invoices with input tax (tax paid) | | |
| TASK-079 | Filter for recoverable input tax only | | |
| TASK-080 | Sum input tax by tax code | | |
| TASK-081 | Calculate net tax payable (output - input) | | |
| TASK-082 | Create TaxReturn record with status = draft | | |
| TASK-083 | Create TaxReturnLine records for each tax code | | |
| TASK-084 | Include reverse charge transactions appropriately | | |

### Implementation Phase 10: Tax Adjustment and Correction

- **GOAL-010**: Support manual tax adjustments

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-085 | Create TaxAdjustment model for corrections | | |
| TASK-086 | Add tax_return_id, adjustment_type enum fields | | |
| TASK-087 | Add adjustment_type values: bad_debt_relief, error_correction, refund_claim, other | | |
| TASK-088 | Add tax_code_id, adjustment_amount, description fields | | |
| TASK-089 | Add approved_by, approved_at fields | | |
| TASK-090 | Create RecordTaxAdjustment Action | | |
| TASK-091 | Create TaxAdjustment record | | |
| TASK-092 | Update TaxReturn totals | | |
| TASK-093 | Create journal entry for adjustment if approved | | |

### Implementation Phase 11: Tax Payment Tracking

- **GOAL-011**: Track tax payments to authorities

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-094 | Create TaxPayment model for tracking settlements | | |
| TASK-095 | Add tax_return_id, payment_date, payment_amount fields | | |
| TASK-096 | Add payment_method enum (bank_transfer, cheque, online) | | |
| TASK-097 | Add payment_reference, journal_entry_id fields | | |
| TASK-098 | Create RecordTaxPayment Action | | |
| TASK-099 | Create journal entry: Debit Tax Payable, Credit Bank | | |
| TASK-100 | Link to TaxPayment record | | |
| TASK-101 | Update TaxReturn status to paid | | |

### Implementation Phase 12: Tax Reporting Actions

- **GOAL-012**: Generate tax compliance reports

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-102 | Create GenerateTaxSummaryReport Action | | |
| TASK-103 | Accept jurisdiction_id, from_date, to_date | | |
| TASK-104 | Group transactions by tax code | | |
| TASK-105 | Show output tax, input tax, net position | | |
| TASK-106 | Create GenerateTaxDetailReport Action | | |
| TASK-107 | List all transactions with tax details | | |
| TASK-108 | Include invoice numbers, amounts, tax codes | | |
| TASK-109 | Create GenerateTaxExceptionReport Action | | |
| TASK-110 | Identify transactions with exemptions or reverse charge | | |
| TASK-111 | Flag missing tax codes or incomplete data | | |

### Implementation Phase 13: Tax Return Export

- **GOAL-013**: Export tax returns in required formats

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-112 | Create ExportTaxReturn Action | | |
| TASK-113 | Accept tax_return_id, export_format enum | | |
| TASK-114 | Support CSV format for generic exports | | |
| TASK-115 | Support PDF format for printable returns | | |
| TASK-116 | Support jurisdiction-specific formats (e.g., UK MTD, Australia BAS) | | |
| TASK-117 | Include all required fields per jurisdiction | | |
| TASK-118 | Validate data completeness before export | | |
| TASK-119 | Generate export file | | |
| TASK-120 | Log export activity for audit trail | | |

### Implementation Phase 14: Filament Resources

- **GOAL-014**: Create Filament resources for tax management

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-121 | Create TaxJurisdictionResource | | |
| TASK-122 | Add form fields: name, code, type, reporting_frequency | | |
| TASK-123 | Create TaxCodeResource | | |
| TASK-124 | Add form fields: jurisdiction, code, name, type, default_rate | | |
| TASK-125 | Add is_compound, is_inclusive, is_recoverable flags | | |
| TASK-126 | Add GL account selectors | | |
| TASK-127 | Create TaxRateResource for managing rate history | | |
| TASK-128 | Add effective date range fields | | |
| TASK-129 | Create TaxReturnResource | | |
| TASK-130 | Add form: jurisdiction, period selection | | |
| TASK-131 | Add table columns: serial_number, period, net_tax, status | | |
| TASK-132 | Add filters: jurisdiction, status, fiscal_year | | |
| TASK-133 | Add actions: Generate Return, Submit, File, Record Payment | | |
| TASK-134 | Create ViewTaxReturn page showing return details and lines | | |

### Implementation Phase 15: Tax Compliance Dashboard

- **GOAL-015**: Build tax compliance monitoring dashboard

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-135 | Create TaxComplianceDashboardPage | | |
| TASK-136 | Create UpcomingTaxFilingsWidget | | |
| TASK-137 | Show tax returns due in next 30 days | | |
| TASK-138 | Create TaxPositionWidget | | |
| TASK-139 | Show current tax payable/receivable position | | |
| TASK-140 | Create TaxTrendWidget | | |
| TASK-141 | Show monthly output vs input tax trends | | |
| TASK-142 | Create TaxExceptionsWidget | | |
| TASK-143 | Show count of exempt/reverse charge transactions | | |

### Implementation Phase 16: Testing and Documentation

- **GOAL-016**: Create comprehensive tests and documentation

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-144 | Create unit tests for tax calculation (inclusive and exclusive) | | |
| TASK-145 | Test BCMath precision with various rates | | |
| TASK-146 | Create unit tests for compound tax calculation | | |
| TASK-147 | Create unit tests for tax rate lookup by date | | |
| TASK-148 | Create unit tests for tax exemption validation | | |
| TASK-149 | Create unit tests for reverse charge application | | |
| TASK-150 | Create feature test for tax return generation | | |
| TASK-151 | Test output tax calculation from sales invoices | | |
| TASK-152 | Test input tax calculation from purchase invoices | | |
| TASK-153 | Create feature test for tax payment recording | | |
| TASK-154 | Create feature test for tax return export | | |
| TASK-155 | Test tax calculation performance (PERF-001) | | |
| TASK-156 | Update ARCHITECTURAL_DECISIONS.md with tax module design | | |
| TASK-157 | Update PROGRESS_CHECKLIST.md with completion status | | |
| TASK-158 | Create tax management user guide | | |
| TASK-159 | Document jurisdiction-specific requirements | | |

## 3. Alternatives

- **ALT-001**: Third-party tax calculation service vs built-in - Chose built-in for control and cost
- **ALT-002**: Single tax per line vs multiple taxes - Supporting multiple for compound tax scenarios
- **ALT-003**: Automatic GL posting vs manual - Chose automatic with manual override option
- **ALT-004**: Real-time tax return generation vs periodic - Supporting both for flexibility

## 4. Dependencies

- **DEP-001**: SalesInvoice and PurchaseInvoice models for tax calculation
- **DEP-002**: JournalEntry for tax GL posting
- **DEP-003**: Account model with tax payable/receivable account types
- **DEP-004**: PaymentReceipt and PaymentVoucher for tax settlement
- **DEP-005**: FiscalYear and AccountingPeriod for tax period definition
- **DEP-006**: Customer and Supplier models for exemption tracking
- **DEP-007**: Laravel Queue for large tax return generation

## 5. Files

- **FILE-001**: `database/migrations/YYYY_MM_DD_create_tax_jurisdictions_table.php`
- **FILE-002**: `database/migrations/YYYY_MM_DD_create_tax_codes_table.php`
- **FILE-003**: `database/migrations/YYYY_MM_DD_create_tax_rates_table.php`
- **FILE-004**: `database/migrations/YYYY_MM_DD_create_transaction_tax_lines_table.php`
- **FILE-005**: `database/migrations/YYYY_MM_DD_create_tax_exemptions_table.php`
- **FILE-006**: `database/migrations/YYYY_MM_DD_create_tax_returns_table.php`
- **FILE-007**: `database/migrations/YYYY_MM_DD_create_tax_return_lines_table.php`
- **FILE-008**: `database/migrations/YYYY_MM_DD_create_tax_adjustments_table.php`
- **FILE-009**: `database/migrations/YYYY_MM_DD_create_tax_payments_table.php`
- **FILE-010**: `app/Models/TaxJurisdiction.php`
- **FILE-011**: `app/Models/TaxCode.php`
- **FILE-012**: `app/Models/TaxRate.php`
- **FILE-013**: `app/Models/TransactionTaxLine.php`
- **FILE-014**: `app/Models/TaxExemption.php`
- **FILE-015**: `app/Models/TaxReturn.php`
- **FILE-016**: `app/Models/TaxAdjustment.php`
- **FILE-017**: `app/Models/TaxPayment.php`
- **FILE-018**: `app/Actions/CalculateTransactionTax.php`
- **FILE-019**: `app/Actions/GetApplicableTaxRate.php`
- **FILE-020**: `app/Actions/ApplyReverseCharge.php`
- **FILE-021**: `app/Actions/GenerateTaxReturn.php`
- **FILE-022**: `app/Actions/RecordTaxPayment.php`
- **FILE-023**: `app/Actions/ExportTaxReturn.php`
- **FILE-024**: `app/Filament/Resources/TaxCodeResource.php`
- **FILE-025**: `app/Filament/Resources/TaxReturnResource.php`
- **FILE-026**: `app/Filament/Pages/TaxComplianceDashboardPage.php`

## 6. Testing

- **TEST-001**: Test tax calculation with exclusive tax (added on top)
- **TEST-002**: Test tax calculation with inclusive tax (included in price)
- **TEST-003**: Test compound tax calculation (tax on tax)
- **TEST-004**: Test tax rate lookup for historical dates
- **TEST-005**: Test tax exemption validation for customer
- **TEST-006**: Test reverse charge application on B2B transaction
- **TEST-007**: Test tax return generation with mixed transactions
- **TEST-008**: Test output tax calculation from sales
- **TEST-009**: Test input tax calculation from purchases (recoverable only)
- **TEST-010**: Test net tax payable calculation (output - input)
- **TEST-011**: Test tax adjustment recording and GL impact
- **TEST-012**: Test tax payment tracking and status updates
- **TEST-013**: Test tax return export to CSV format
- **TEST-014**: Test tax calculation performance (PERF-001)
- **TEST-015**: Test tax return generation with 50,000+ transactions (PERF-003)

## 7. Risks & Assumptions

### Risks
- **RISK-001**: Tax calculation errors can lead to compliance violations - Mitigation: Thorough testing, validation, approval workflow
- **RISK-002**: Tax rate changes may not be updated timely - Mitigation: Alerts for upcoming rate changes, automated updates
- **RISK-003**: Missing exemption documentation may cause issues during audits - Mitigation: Mandatory certificate upload, expiry tracking
- **RISK-004**: Jurisdiction-specific requirements may vary significantly - Mitigation: Configurable export formats, consultation with tax experts

### Assumptions
- **ASSUMPTION-001**: Tax codes are configured correctly before go-live
- **ASSUMPTION-002**: Tax rates change infrequently (quarterly or annually)
- **ASSUMPTION-003**: Tax returns are filed on time per reporting frequency
- **ASSUMPTION-004**: Input tax recovery rules are consistent within jurisdiction
- **ASSUMPTION-005**: Exemption certificates are obtained before creating exempt transactions

## 8. Related Specifications / Further Reading

- [System Architecture Specification](../spec/architecture-nexus-erp.md)
- [Accounting Module Planning](../ACCOUNTING_MODULE_PLANNING.md)
- [General Ledger Implementation Plan](./feature-general-ledger-1.md)
- [AR Implementation Plan](./feature-ar-filament-resources-1.md)
- [AP Implementation Plan](./feature-ap-foundation-1.md)
- [VAT Registration and Compliance](https://ec.europa.eu/taxation_customs/business/vat_en)
- [GST Implementation Guide](https://www.ato.gov.au/business/gst/)
