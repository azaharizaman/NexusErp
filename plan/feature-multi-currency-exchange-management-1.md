---
goal: Implement Multi-Currency & Exchange Management
version: 1.0
date_created: 2025-11-05
last_updated: 2025-11-05
owner: Development Team
status: 'Planned'
tags: ["feature", "accounting", "multi-currency", "fx", "exchange-rates"]
---

# Implement Multi-Currency & Exchange Management

![Status: Planned](https://img.shields.io/badge/status-Planned-blue)

This implementation plan covers the Multi-Currency & Exchange Management module including currency master data, exchange rate management, multi-currency transactions, foreign exchange gain/loss calculations (realized and unrealized), currency revaluation, and multi-currency reporting.

## 1. Requirements & Constraints

### Functional Requirements
- **REQ-001**: Maintain currency master with ISO codes, symbols, and decimal precision
- **REQ-002**: Store exchange rates with effective dates and sources
- **REQ-003**: Support base currency per company with conversion to reporting currency
- **REQ-004**: Record transactions in foreign currency with automatic base currency conversion
- **REQ-005**: Calculate realized FX gains/losses on settlement (payment/receipt)
- **REQ-006**: Calculate unrealized FX gains/losses on open balances at period-end
- **REQ-007**: Support manual exchange rate entry and automated import from providers
- **REQ-008**: Generate currency revaluation journal entries
- **REQ-009**: Provide multi-currency financial reports with conversion

### Technical Constraints
- **CON-001**: All currency calculations must use BCMath with configurable decimal precision
- **CON-002**: Exchange rates must support both direct and indirect quotation
- **CON-003**: JournalEntry must store both foreign and base currency amounts
- **CON-004**: Revaluation must preserve historical exchange rates for audit trail

### Performance Requirements
- **PERF-001**: Exchange rate lookup must be cached for performance
- **PERF-002**: Currency revaluation must complete within 5 minutes for 10,000+ transactions
- **PERF-003**: Multi-currency reports must use pre-calculated conversion rates

### Integration Requirements
- **INT-001**: Integrate with JournalEntry and JournalEntryLine for multi-currency support
- **INT-002**: Link to Account for tracking FX gain/loss accounts
- **INT-003**: Integrate with AR/AP modules for realized FX calculations
- **INT-004**: Support external FX rate providers (API integration)
- **INT-005**: Link to FiscalYear and AccountingPeriod for revaluation

## 2. Implementation Steps

### Implementation Phase 1: Currency Model

- **GOAL-001**: Create Currency model with ISO standards support

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-001 | Create currencies migration | | |
| TASK-002 | Add currency_code (ISO 4217, e.g., USD, EUR, GBP) | | |
| TASK-003 | Add currency_name, currency_symbol fields | | |
| TASK-004 | Add decimal_places field (typically 2, some currencies use 0 or 3) | | |
| TASK-005 | Add subunit_name (e.g., cents, pence) and subunit_to_unit (e.g., 100) | | |
| TASK-006 | Add is_active boolean flag | | |
| TASK-007 | Add unique constraint on currency_code | | |
| TASK-008 | Create Currency model | | |
| TASK-009 | Seed common currencies (USD, EUR, GBP, JPY, etc.) | | |

### Implementation Phase 2: Exchange Rate Model

- **GOAL-002**: Create ExchangeRate model for rate storage and history

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-010 | Create exchange_rates migration | | |
| TASK-011 | Add from_currency_id, to_currency_id fields | | |
| TASK-012 | Add exchange_rate field (decimal with high precision) | | |
| TASK-013 | Add effective_date, expiry_date fields | | |
| TASK-014 | Add rate_type enum (spot, average, custom) | | |
| TASK-015 | Add source enum (manual, api, bank, official) | | |
| TASK-016 | Add is_active boolean flag | | |
| TASK-017 | Add created_by_user_id for audit trail | | |
| TASK-018 | Create ExchangeRate model | | |
| TASK-019 | Add validation: rate must be positive | | |
| TASK-020 | Add unique constraint (from_currency, to_currency, effective_date, rate_type) | | |

### Implementation Phase 3: Company Base Currency Configuration

- **GOAL-003**: Extend Company model for base currency settings

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-021 | Add base_currency_id to companies table | | |
| TASK-022 | Add reporting_currency_id (for consolidated reports) | | |
| TASK-023 | Add fx_gain_account_id, fx_loss_account_id fields | | |
| TASK-024 | Add unrealized_fx_gain_account_id, unrealized_fx_loss_account_id fields | | |
| TASK-025 | Add currency_translation_method enum (current_rate, temporal) | | |
| TASK-026 | Update Company model with currency relationships | | |
| TASK-027 | Add validation: base_currency must be active | | |

### Implementation Phase 4: Multi-Currency Journal Entry Support

- **GOAL-004**: Extend JournalEntry and JournalEntryLine for multi-currency

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-028 | Add currency_id to journal_entries table | | |
| TASK-029 | Add exchange_rate field to journal_entries | | |
| TASK-030 | Add is_foreign_currency boolean flag | | |
| TASK-031 | Add foreign_currency_amount to journal_entry_lines table | | |
| TASK-032 | Add exchange_rate_at_transaction to journal_entry_lines | | |
| TASK-033 | Ensure base_currency_amount (debit/credit) always populated | | |
| TASK-034 | Update JournalEntry model with currency relationships | | |
| TASK-035 | Update JournalEntryLine model with currency fields | | |
| TASK-036 | Add validation: if foreign currency, exchange rate required | | |

### Implementation Phase 5: Exchange Rate Actions

- **GOAL-005**: Create Actions for exchange rate management

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-037 | Create RecordExchangeRate Action | | |
| TASK-038 | Accept from_currency, to_currency, rate, effective_date | | |
| TASK-039 | Validate rate is positive and currencies are different | | |
| TASK-040 | Create ExchangeRate record with source = manual | | |
| TASK-041 | Create inverse rate automatically (1 / rate) | | |
| TASK-042 | Create GetExchangeRate Action | | |
| TASK-043 | Accept from_currency, to_currency, transaction_date | | |
| TASK-044 | Query effective exchange rate for date (effective_date <= transaction_date) | | |
| TASK-045 | Return most recent rate before or on transaction date | | |
| TASK-046 | Cache rate lookups for performance | | |
| TASK-047 | Handle missing rate with exception or return null | | |

### Implementation Phase 6: Currency Conversion Action

- **GOAL-006**: Create Action for converting amounts between currencies

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-048 | Create ConvertCurrency Action | | |
| TASK-049 | Accept amount, from_currency, to_currency, transaction_date | | |
| TASK-050 | Get exchange rate for date | | |
| TASK-051 | Calculate converted amount using BCMath | | |
| TASK-052 | Round to target currency decimal precision | | |
| TASK-053 | Return converted amount and exchange rate used | | |
| TASK-054 | Handle same currency conversion (return original amount) | | |
| TASK-055 | Handle base currency conversion efficiently | | |

### Implementation Phase 7: Foreign Transaction Posting

- **GOAL-007**: Extend CreateJournalEntry Action for multi-currency

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-056 | Update CreateJournalEntry to accept currency_id | | |
| TASK-057 | If foreign currency, get exchange rate for transaction date | | |
| TASK-058 | Convert each line amount to base currency | | |
| TASK-059 | Store foreign_currency_amount and exchange_rate_at_transaction | | |
| TASK-060 | Ensure debit = credit in base currency | | |
| TASK-061 | Set is_foreign_currency flag on journal entry | | |

### Implementation Phase 8: Realized FX Gain/Loss Calculation

- **GOAL-008**: Calculate realized FX gains/losses on payment settlement

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-062 | Create CalculateRealizedFXGainLoss Action | | |
| TASK-063 | Accept invoice transaction and payment transaction | | |
| TASK-064 | Get exchange rate at invoice date (invoice_rate) | | |
| TASK-065 | Get exchange rate at payment date (payment_rate) | | |
| TASK-066 | Calculate base currency difference (payment_amount * payment_rate - invoice_amount * invoice_rate) | | |
| TASK-067 | Determine if gain (positive) or loss (negative) | | |
| TASK-068 | Return FX gain/loss amount | | |
| TASK-069 | Integrate into PostPaymentReceipt Action | | |
| TASK-070 | Create journal entry line for FX gain/loss | | |
| TASK-071 | Integrate into PostPaymentVoucher Action | | |

### Implementation Phase 9: Unrealized FX Gain/Loss Revaluation

- **GOAL-009**: Implement period-end currency revaluation for open balances

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-072 | Create CurrencyRevaluation model for tracking runs | | |
| TASK-073 | Add revaluation_date, accounting_period_id fields | | |
| TASK-074 | Add status enum (draft, posted, reversed) | | |
| TASK-075 | Add journal_entry_id linking to revaluation entry | | |
| TASK-076 | Create RevalueCurrencyBalances Action | | |
| TASK-077 | Accept revaluation_date, account_ids (AR/AP/Bank accounts) | | |
| TASK-078 | Query open foreign currency balances by account | | |
| TASK-079 | Get historical exchange rate at transaction date | | |
| TASK-080 | Get current exchange rate at revaluation date | | |
| TASK-081 | Calculate unrealized gain/loss (balance * current_rate - balance * historical_rate) | | |
| TASK-082 | Group gains and losses by currency and account | | |
| TASK-083 | Create journal entry for unrealized FX adjustments | | |
| TASK-084 | Debit/Credit Unrealized FX Gain/Loss accounts | | |
| TASK-085 | Link to CurrencyRevaluation record | | |

### Implementation Phase 10: Exchange Rate Import

- **GOAL-010**: Implement automated exchange rate import from external sources

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-086 | Create ImportExchangeRates Action | | |
| TASK-087 | Support CSV file upload with columns: from_currency, to_currency, rate, effective_date | | |
| TASK-088 | Validate CSV structure and data types | | |
| TASK-089 | Create ExchangeRate records with source = api or file | | |
| TASK-090 | Handle duplicate rates (update if exists) | | |
| TASK-091 | Create FetchExchangeRatesFromAPI Action (optional) | | |
| TASK-092 | Integrate with external FX provider (e.g., exchangerate-api.com) | | |
| TASK-093 | Fetch rates for configured currency pairs | | |
| TASK-094 | Store with source = api and effective_date = today | | |
| TASK-095 | Create scheduled job to fetch rates daily | | |

### Implementation Phase 11: Filament Resources

- **GOAL-011**: Create Filament resources for currency management

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-096 | Create CurrencyResource | | |
| TASK-097 | Add form fields: currency_code, name, symbol, decimal_places | | |
| TASK-098 | Add table columns: code, name, symbol, is_active | | |
| TASK-099 | Add filters: is_active | | |
| TASK-100 | Create ExchangeRateResource | | |
| TASK-101 | Add form fields: from_currency, to_currency, rate, effective_date, rate_type, source | | |
| TASK-102 | Add table columns: currencies, rate, effective_date, source | | |
| TASK-103 | Add filters: from_currency, to_currency, source, effective_date range | | |
| TASK-104 | Add actions: Import from CSV, Fetch from API | | |
| TASK-105 | Create CurrencyRevaluationResource | | |
| TASK-106 | Add form: revaluation_date, account selection | | |
| TASK-107 | Add table showing revaluation runs with totals | | |
| TASK-108 | Add action: Run Revaluation, View Journal Entry | | |

### Implementation Phase 12: Multi-Currency Reporting

- **GOAL-012**: Extend financial reports for multi-currency display

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-109 | Update BalanceSheet report to accept display_currency parameter | | |
| TASK-110 | Convert all balances to display currency using period-end rates | | |
| TASK-111 | Show original currency amounts in drill-down | | |
| TASK-112 | Update ProfitAndLoss report for multi-currency | | |
| TASK-113 | Use average rates for revenue/expense conversion | | |
| TASK-114 | Update TrialBalance report to show foreign currency balances | | |
| TASK-115 | Create FXGainLossReport showing realized and unrealized FX | | |
| TASK-116 | Group by currency and time period | | |
| TASK-117 | Create CurrencyExposureReport showing open foreign currency positions | | |

### Implementation Phase 13: Testing and Documentation

- **GOAL-013**: Create comprehensive tests and documentation

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-118 | Create unit tests for ConvertCurrency with various scenarios | | |
| TASK-119 | Test rounding to different decimal precisions | | |
| TASK-120 | Create unit tests for GetExchangeRate with date ranges | | |
| TASK-121 | Test missing exchange rate handling | | |
| TASK-122 | Create unit tests for realized FX gain/loss calculations | | |
| TASK-123 | Test FX gain and FX loss scenarios | | |
| TASK-124 | Create unit tests for unrealized FX revaluation | | |
| TASK-125 | Test revaluation with appreciation and depreciation | | |
| TASK-126 | Create feature test for multi-currency transaction posting | | |
| TASK-127 | Create feature test for period-end revaluation workflow | | |
| TASK-128 | Create feature test for exchange rate import | | |
| TASK-129 | Test BCMath precision with large amounts | | |
| TASK-130 | Update ARCHITECTURAL_DECISIONS.md with multi-currency design | | |
| TASK-131 | Update PROGRESS_CHECKLIST.md with completion status | | |
| TASK-132 | Create multi-currency module user guide | | |
| TASK-133 | Document exchange rate source configuration | | |

## 3. Alternatives

- **ALT-001**: Real-time FX rate API vs periodic import - Chose periodic with manual override option for control
- **ALT-002**: Single exchange rate per day vs multiple rates - Supporting multiple rates per day with rate_type differentiation
- **ALT-003**: Automatic revaluation vs manual trigger - Chose manual trigger for control, can automate later
- **ALT-004**: Triangular conversion (via USD) vs direct rates - Supporting both for flexibility

## 4. Dependencies

- **DEP-001**: Company model for base currency configuration
- **DEP-002**: JournalEntry and JournalEntryLine models
- **DEP-003**: Account model for FX gain/loss accounts
- **DEP-004**: FiscalYear and AccountingPeriod for revaluation scheduling
- **DEP-005**: AR and AP modules for realized FX integration
- **DEP-006**: Laravel Cache for exchange rate lookup performance
- **DEP-007**: External FX rate provider API (optional)

## 5. Files

- **FILE-001**: `database/migrations/YYYY_MM_DD_create_currencies_table.php`
- **FILE-002**: `database/migrations/YYYY_MM_DD_create_exchange_rates_table.php`
- **FILE-003**: `database/migrations/YYYY_MM_DD_add_currency_fields_to_companies.php`
- **FILE-004**: `database/migrations/YYYY_MM_DD_add_currency_fields_to_journal_entries.php`
- **FILE-005**: `database/migrations/YYYY_MM_DD_add_currency_fields_to_journal_entry_lines.php`
- **FILE-006**: `database/migrations/YYYY_MM_DD_create_currency_revaluations_table.php`
- **FILE-007**: `database/seeders/CurrencySeeder.php`
- **FILE-008**: `app/Models/Currency.php`
- **FILE-009**: `app/Models/ExchangeRate.php`
- **FILE-010**: `app/Models/CurrencyRevaluation.php`
- **FILE-011**: `app/Actions/RecordExchangeRate.php`
- **FILE-012**: `app/Actions/GetExchangeRate.php`
- **FILE-013**: `app/Actions/ConvertCurrency.php`
- **FILE-014**: `app/Actions/CalculateRealizedFXGainLoss.php`
- **FILE-015**: `app/Actions/RevalueCurrencyBalances.php`
- **FILE-016**: `app/Actions/ImportExchangeRates.php`
- **FILE-017**: `app/Actions/FetchExchangeRatesFromAPI.php`
- **FILE-018**: `app/Filament/Resources/CurrencyResource.php`
- **FILE-019**: `app/Filament/Resources/ExchangeRateResource.php`
- **FILE-020**: `app/Filament/Resources/CurrencyRevaluationResource.php`
- **FILE-021**: `app/Console/Commands/FetchDailyExchangeRates.php`

## 6. Testing

- **TEST-001**: Test currency conversion with various exchange rates
- **TEST-002**: Test rounding to 0, 2, and 3 decimal places
- **TEST-003**: Test same currency conversion returns original amount
- **TEST-004**: Test exchange rate lookup for specific dates
- **TEST-005**: Test missing exchange rate throws exception or returns null
- **TEST-006**: Test realized FX gain calculation when currency appreciates
- **TEST-007**: Test realized FX loss calculation when currency depreciates
- **TEST-008**: Test unrealized FX revaluation for AR balances
- **TEST-009**: Test unrealized FX revaluation for AP balances
- **TEST-010**: Test revaluation journal entry debit/credit correctness
- **TEST-011**: Test multi-currency transaction posting to GL
- **TEST-012**: Test exchange rate import from CSV
- **TEST-013**: Test exchange rate API integration
- **TEST-014**: Test BCMath precision with large currency amounts
- **TEST-015**: Test multi-currency financial report conversions

## 7. Risks & Assumptions

### Risks
- **RISK-001**: Exchange rate errors can cause significant financial misstatements - Mitigation: Validation, approval workflow, audit trail
- **RISK-002**: Missing exchange rates block transactions - Mitigation: Alerts, manual entry option, comprehensive rate coverage
- **RISK-003**: Rounding differences accumulate - Mitigation: BCMath high precision, document rounding rules
- **RISK-004**: External API failures disrupt operations - Mitigation: Fallback to manual entry, rate caching

### Assumptions
- **ASSUMPTION-001**: Base currency is set at company creation and rarely changes
- **ASSUMPTION-002**: Exchange rates are updated at least daily for active currencies
- **ASSUMPTION-003**: Currency revaluation is performed at least monthly or at period-end
- **ASSUMPTION-004**: Realized FX is calculated on payment settlement, not on invoice creation
- **ASSUMPTION-005**: Historical exchange rates are preserved for audit purposes

## 8. Related Specifications / Further Reading

- [System Architecture Specification](../spec/architecture-nexus-erp.md)
- [Accounting Module Planning](../ACCOUNTING_MODULE_PLANNING.md)
- [General Ledger Implementation Plan](./feature-general-ledger-1.md)
- [IAS 21 The Effects of Changes in Foreign Exchange Rates](https://www.ifrs.org/issued-standards/list-of-standards/ias-21-the-effects-of-changes-in-foreign-exchange-rates/)
- [ISO 4217 Currency Codes](https://www.iso.org/iso-4217-currency-codes.html)
- [Exchange Rate API Documentation](https://www.exchangerate-api.com/docs)
