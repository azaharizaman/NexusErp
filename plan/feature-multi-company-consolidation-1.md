---
goal: Implement Multi-Company & Consolidation Features
version: 1.0
date_created: 2025-11-05
last_updated: 2025-11-05
owner: Development Team
status: 'Planned'
tags: ["feature", "accounting", "multi-company", "consolidation", "intercompany"]
---

# Implement Multi-Company & Consolidation Features

![Status: Planned](https://img.shields.io/badge/status-Planned-blue)

This implementation plan covers the Multi-Company & Consolidation module including company hierarchy management, intercompany transaction tracking, automated elimination entries, account mapping for consolidation, currency translation for foreign subsidiaries, and consolidated financial statement generation.

## 1. Requirements & Constraints

### Functional Requirements
- **REQ-001**: Support multiple legal entities (companies) within same installation
- **REQ-002**: Define company hierarchies (parent-subsidiary relationships)
- **REQ-003**: Track intercompany transactions with automatic matching
- **REQ-004**: Generate elimination entries for intercompany balances and transactions
- **REQ-005**: Support account mapping between different companies' charts of accounts
- **REQ-006**: Translate foreign subsidiary financials to parent currency
- **REQ-007**: Generate consolidated financial statements (Balance Sheet, P&L, Cash Flow)
- **REQ-008**: Support minority interests and non-controlling interests
- **REQ-009**: Provide consolidation audit trail and adjustment tracking
- **REQ-010**: Enable partial consolidation (exclude specific entities)

### Technical Constraints
- **CON-001**: All company data must be properly scoped to prevent cross-company data leakage
- **CON-002**: Consolidation calculations must use BCMath for precision
- **CON-003**: Elimination entries must be reversible and traceable
- **CON-004**: Consolidation runs must use database transactions for atomicity

### Performance Requirements
- **PERF-001**: Consolidation run must complete within 10 minutes for 20+ entities
- **PERF-002**: Intercompany matching must handle 10,000+ transactions efficiently
- **PERF-003**: Consolidated reports must use cached consolidation data

### Integration Requirements
- **INT-001**: Integrate with JournalEntry for consolidation adjustments
- **INT-002**: Link to Currency and ExchangeRate for translation
- **INT-003**: Link to Account for mapping and elimination accounts
- **INT-004**: Pull data from all companies' GL for consolidation
- **INT-005**: Link to FiscalYear and AccountingPeriod for consolidation scheduling

## 2. Implementation Steps

### Implementation Phase 1: Company Hierarchy Model

- **GOAL-001**: Extend Company model with consolidation hierarchy

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-001 | Add parent_company_id to companies table | | |
| TASK-002 | Add company_type enum (standalone, parent, subsidiary) | | |
| TASK-003 | Add consolidation_method enum (full, proportional, equity) | | |
| TASK-004 | Add ownership_percentage field (for non-100% subsidiaries) | | |
| TASK-005 | Add consolidation_currency_id field | | |
| TASK-006 | Add is_consolidated boolean flag | | |
| TASK-007 | Add consolidation_start_date field | | |
| TASK-008 | Update Company model with parent/children relationships | | |
| TASK-009 | Add validation: no circular hierarchy references | | |
| TASK-010 | Create CompanyHierarchy recursive query for tree structure | | |

### Implementation Phase 2: Intercompany Relationship Model

- **GOAL-002**: Create IntercompanyRelationship for tracking trading partners

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-011 | Create intercompany_relationships migration | | |
| TASK-012 | Add from_company_id, to_company_id fields | | |
| TASK-013 | Add relationship_type enum (parent_subsidiary, sister_company, related_party) | | |
| TASK-014 | Add is_active boolean flag | | |
| TASK-015 | Add notes field for relationship description | | |
| TASK-016 | Create IntercompanyRelationship model | | |
| TASK-017 | Add unique constraint (from_company, to_company) | | |
| TASK-018 | Add validation: companies must be different | | |

### Implementation Phase 3: Intercompany Transaction Tagging

- **GOAL-003**: Track intercompany transactions for elimination

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-019 | Add is_intercompany to journal_entries table | | |
| TASK-020 | Add intercompany_company_id (trading partner company) | | |
| TASK-021 | Add intercompany_reference (for matching) | | |
| TASK-022 | Add is_eliminated, eliminated_at fields | | |
| TASK-023 | Update JournalEntry model with intercompany fields | | |
| TASK-024 | Create IntercompanyTransactionTag model (optional bridge table) | | |
| TASK-025 | Add from_company_id, to_company_id, transaction_type enum | | |
| TASK-026 | Add amount_in_from_currency, amount_in_to_currency fields | | |
| TASK-027 | Add matching_reference for linking receivable/payable pairs | | |

### Implementation Phase 4: Account Mapping for Consolidation

- **GOAL-004**: Create account mapping between companies

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-028 | Create consolidation_account_mappings migration | | |
| TASK-029 | Add subsidiary_company_id, subsidiary_account_id fields | | |
| TASK-030 | Add parent_company_id, consolidated_account_id fields | | |
| TASK-031 | Add mapping_type enum (direct, group, elimination) | | |
| TASK-032 | Add adjustment_factor (for proportional consolidation) | | |
| TASK-033 | Create ConsolidationAccountMapping model | | |
| TASK-034 | Add validation: accounts must belong to respective companies | | |
| TASK-035 | Add unique constraint (subsidiary_company, subsidiary_account, parent_company) | | |

### Implementation Phase 5: Consolidation Run Model

- **GOAL-005**: Create ConsolidationRun for tracking consolidation executions

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-036 | Create consolidation_runs migration | | |
| TASK-037 | Add serial_number with CONS-YYYY-XXXX format | | |
| TASK-038 | Add parent_company_id, consolidation_date fields | | |
| TASK-039 | Add fiscal_year_id, accounting_period_id fields | | |
| TASK-040 | Add status enum (draft, in_progress, completed, failed, reversed) | | |
| TASK-041 | Add total_elimination_amount, total_adjustment_amount fields | | |
| TASK-042 | Add elimination_journal_entry_id linking to elimination JE | | |
| TASK-043 | Add completed_at, completed_by fields | | |
| TASK-044 | Create ConsolidationRun model with HasStatuses trait | | |
| TASK-045 | Add relationships to Company, JournalEntry | | |

### Implementation Phase 6: Consolidation Company Selection

- **GOAL-006**: Define which companies to include in consolidation

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-046 | Create consolidation_companies migration (pivot table) | | |
| TASK-047 | Add consolidation_run_id, company_id fields | | |
| TASK-048 | Add is_included boolean flag | | |
| TASK-049 | Add exclusion_reason field (nullable) | | |
| TASK-050 | Create ConsolidationCompany model | | |
| TASK-051 | Add unique constraint (consolidation_run_id, company_id) | | |

### Implementation Phase 7: Intercompany Matching Action

- **GOAL-007**: Automatically match intercompany transactions

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-052 | Create MatchIntercompanyTransactions Action | | |
| TASK-053 | Query intercompany journal entries for consolidation period | | |
| TASK-054 | Match by intercompany_reference and companies | | |
| TASK-055 | Match by amount and date proximity (if reference missing) | | |
| TASK-056 | Create matched pairs for elimination | | |
| TASK-057 | Flag unmatched transactions for review | | |
| TASK-058 | Handle currency differences in matching | | |

### Implementation Phase 8: Elimination Entry Generation

- **GOAL-008**: Generate elimination journal entries for consolidation

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-059 | Create GenerateEliminationEntries Action | | |
| TASK-060 | Query all intercompany receivables and payables | | |
| TASK-061 | Generate elimination for AR/AP balances | | |
| TASK-062 | Debit: Intercompany Payable, Credit: Intercompany Receivable | | |
| TASK-063 | Query intercompany revenue and expense transactions | | |
| TASK-064 | Generate elimination for intercompany sales/COGS | | |
| TASK-065 | Debit: Intercompany Revenue, Credit: Intercompany COGS/Expense | | |
| TASK-066 | Handle unrealized profit on inventory (if applicable) | | |
| TASK-067 | Create consolidation journal entry with all eliminations | | |
| TASK-068 | Link to ConsolidationRun via elimination_journal_entry_id | | |

### Implementation Phase 9: Currency Translation for Subsidiaries

- **GOAL-009**: Translate foreign subsidiary financials to parent currency

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-069 | Create TranslateSubsidiaryFinancials Action | | |
| TASK-070 | Accept subsidiary_company_id, consolidation_date | | |
| TASK-071 | Get subsidiary functional currency and parent reporting currency | | |
| TASK-072 | Translate assets and liabilities using closing rate (period-end) | | |
| TASK-073 | Translate revenue and expenses using average rate | | |
| TASK-074 | Translate equity using historical rates | | |
| TASK-075 | Calculate currency translation adjustment (CTA) | | |
| TASK-076 | Store translated balances in consolidation working tables | | |
| TASK-077 | Create CTA journal entry in equity | | |

### Implementation Phase 10: Consolidation Calculation Action

- **GOAL-010**: Perform consolidation calculations and generate consolidated balances

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-078 | Create RunConsolidation Action | | |
| TASK-079 | Accept parent_company_id, consolidation_date, period_id | | |
| TASK-080 | Select companies to consolidate based on hierarchy | | |
| TASK-081 | Translate foreign subsidiaries to parent currency | | |
| TASK-082 | Map subsidiary accounts to parent consolidated accounts | | |
| TASK-083 | Aggregate balances by consolidated account | | |
| TASK-084 | Generate elimination entries | | |
| TASK-085 | Apply eliminations to aggregated balances | | |
| TASK-086 | Calculate minority interest (non-controlling interest) | | |
| TASK-087 | Store consolidated balances in ConsolidatedBalance model | | |
| TASK-088 | Create ConsolidationRun record with status = completed | | |
| TASK-089 | Use database transaction for atomicity | | |

### Implementation Phase 11: Consolidated Balance Storage

- **GOAL-011**: Store consolidated balances for reporting

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-090 | Create consolidated_balances migration | | |
| TASK-091 | Add consolidation_run_id, consolidated_account_id fields | | |
| TASK-092 | Add balance_before_elimination, elimination_amount, consolidated_balance fields | | |
| TASK-093 | Add minority_interest_amount field | | |
| TASK-094 | Add contributing_companies JSON (list of source companies) | | |
| TASK-095 | Create ConsolidatedBalance model | | |
| TASK-096 | Add unique constraint (consolidation_run_id, consolidated_account_id) | | |

### Implementation Phase 12: Minority Interest Calculation

- **GOAL-012**: Calculate and track minority (non-controlling) interests

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-097 | Create CalculateMinorityInterest Action | | |
| TASK-098 | For each subsidiary, get ownership_percentage | | |
| TASK-099 | Calculate minority percentage (100% - ownership_percentage) | | |
| TASK-100 | Apply minority percentage to subsidiary net assets | | |
| TASK-101 | Apply minority percentage to subsidiary net income | | |
| TASK-102 | Create minority interest balance in consolidated equity | | |
| TASK-103 | Create minority interest line in consolidated P&L | | |

### Implementation Phase 13: Consolidation Adjustments

- **GOAL-013**: Support manual consolidation adjustments

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-104 | Create consolidation_adjustments migration | | |
| TASK-105 | Add consolidation_run_id, adjustment_type enum fields | | |
| TASK-106 | Add adjustment_type values: reclassification, fair_value, other | | |
| TASK-107 | Add description, amount fields | | |
| TASK-108 | Add journal_entry_id linking to adjustment JE | | |
| TASK-109 | Add created_by, approved_by fields | | |
| TASK-110 | Create ConsolidationAdjustment model | | |
| TASK-111 | Create RecordConsolidationAdjustment Action | | |
| TASK-112 | Create journal entry for adjustment | | |
| TASK-113 | Apply adjustment to consolidated balances | | |

### Implementation Phase 14: Consolidated Reports

- **GOAL-014**: Generate consolidated financial statements

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-114 | Create GenerateConsolidatedBalanceSheet Action | | |
| TASK-115 | Query consolidated balances from latest consolidation run | | |
| TASK-116 | Group by account hierarchy | | |
| TASK-117 | Show minority interest separately in equity section | | |
| TASK-118 | Create GenerateConsolidatedProfitAndLoss Action | | |
| TASK-119 | Query consolidated revenue and expense balances | | |
| TASK-120 | Deduct minority interest in net income | | |
| TASK-121 | Show net income attributable to parent | | |
| TASK-122 | Create ConsolidatedFinancialStatementsPage in Filament | | |
| TASK-123 | Add consolidation run selector | | |
| TASK-124 | Display Balance Sheet, P&L, and elimination details | | |
| TASK-125 | Add drill-down to source company details | | |

### Implementation Phase 15: Filament Resources

- **GOAL-015**: Create Filament resources for consolidation management

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-126 | Create ConsolidationRunResource | | |
| TASK-127 | Add form: parent_company, consolidation_date, period selection | | |
| TASK-128 | Add company selection interface (checkboxes for subsidiaries) | | |
| TASK-129 | Add table columns: serial_number, date, status, total_eliminations | | |
| TASK-130 | Add filters: parent_company, status, fiscal_year | | |
| TASK-131 | Add actions: Run Consolidation, View Eliminations, Reverse | | |
| TASK-132 | Create ViewConsolidationRun page showing detailed results | | |
| TASK-133 | Create IntercompanyRelationshipResource | | |
| TASK-134 | Add form for defining relationships between companies | | |
| TASK-135 | Create ConsolidationAccountMappingResource | | |
| TASK-136 | Add interface for mapping subsidiary accounts to parent | | |
| TASK-137 | Support bulk mapping and import from CSV | | |

### Implementation Phase 16: Consolidation Reversal

- **GOAL-016**: Support reversal of consolidation runs

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-138 | Create ReverseConsolidation Action | | |
| TASK-139 | Validate consolidation run can be reversed (not already reversed) | | |
| TASK-140 | Reverse elimination journal entry | | |
| TASK-141 | Delete consolidated balances records | | |
| TASK-142 | Update ConsolidationRun status to reversed | | |
| TASK-143 | Mark intercompany transactions as not eliminated | | |

### Implementation Phase 17: Testing and Documentation

- **GOAL-017**: Create comprehensive tests and documentation

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-144 | Create unit tests for intercompany transaction matching | | |
| TASK-145 | Create unit tests for elimination entry generation | | |
| TASK-146 | Create unit tests for currency translation (current rate, average rate) | | |
| TASK-147 | Create unit tests for minority interest calculations | | |
| TASK-148 | Create feature test for full consolidation workflow | | |
| TASK-149 | Test consolidation with 100% subsidiary | | |
| TASK-150 | Test consolidation with partial ownership (70%) | | |
| TASK-151 | Test consolidation with multiple levels (grandchild subsidiaries) | | |
| TASK-152 | Test consolidation reversal | | |
| TASK-153 | Test performance with 20+ entities (PERF-001) | | |
| TASK-154 | Update ARCHITECTURAL_DECISIONS.md with consolidation design | | |
| TASK-155 | Update PROGRESS_CHECKLIST.md with completion status | | |
| TASK-156 | Create multi-company consolidation user guide | | |
| TASK-157 | Document intercompany transaction best practices | | |

## 3. Alternatives

- **ALT-001**: Full consolidation only vs supporting proportional/equity methods - Supporting multiple methods for flexibility
- **ALT-002**: Real-time consolidation vs periodic runs - Chose periodic runs for control and performance
- **ALT-003**: Automatic account mapping vs manual - Starting with manual, can add auto-mapping later
- **ALT-004**: Single-level consolidation vs multi-level - Supporting multi-level for complex groups

## 4. Dependencies

- **DEP-001**: Company model with base currency and fiscal year setup
- **DEP-002**: JournalEntry and JournalEntryLine models
- **DEP-003**: Account model with consolidation account types
- **DEP-004**: Currency and ExchangeRate models for translation
- **DEP-005**: FiscalYear and AccountingPeriod for scheduling
- **DEP-006**: Multi-currency module for translation calculations
- **DEP-007**: Laravel Queue for long-running consolidation jobs

## 5. Files

- **FILE-001**: `database/migrations/YYYY_MM_DD_add_consolidation_fields_to_companies.php`
- **FILE-002**: `database/migrations/YYYY_MM_DD_create_intercompany_relationships_table.php`
- **FILE-003**: `database/migrations/YYYY_MM_DD_add_intercompany_fields_to_journal_entries.php`
- **FILE-004**: `database/migrations/YYYY_MM_DD_create_consolidation_account_mappings_table.php`
- **FILE-005**: `database/migrations/YYYY_MM_DD_create_consolidation_runs_table.php`
- **FILE-006**: `database/migrations/YYYY_MM_DD_create_consolidation_companies_table.php`
- **FILE-007**: `database/migrations/YYYY_MM_DD_create_consolidated_balances_table.php`
- **FILE-008**: `database/migrations/YYYY_MM_DD_create_consolidation_adjustments_table.php`
- **FILE-009**: `app/Models/IntercompanyRelationship.php`
- **FILE-010**: `app/Models/ConsolidationAccountMapping.php`
- **FILE-011**: `app/Models/ConsolidationRun.php`
- **FILE-012**: `app/Models/ConsolidatedBalance.php`
- **FILE-013**: `app/Models/ConsolidationAdjustment.php`
- **FILE-014**: `app/Actions/MatchIntercompanyTransactions.php`
- **FILE-015**: `app/Actions/GenerateEliminationEntries.php`
- **FILE-016**: `app/Actions/TranslateSubsidiaryFinancials.php`
- **FILE-017**: `app/Actions/CalculateMinorityInterest.php`
- **FILE-018**: `app/Actions/RunConsolidation.php`
- **FILE-019**: `app/Actions/ReverseConsolidation.php`
- **FILE-020**: `app/Actions/GenerateConsolidatedBalanceSheet.php`
- **FILE-021**: `app/Actions/GenerateConsolidatedProfitAndLoss.php`
- **FILE-022**: `app/Filament/Resources/ConsolidationRunResource.php`
- **FILE-023**: `app/Filament/Resources/IntercompanyRelationshipResource.php`
- **FILE-024**: `app/Filament/Resources/ConsolidationAccountMappingResource.php`
- **FILE-025**: `app/Filament/Pages/ConsolidatedFinancialStatementsPage.php`

## 6. Testing

- **TEST-001**: Test company hierarchy creation and validation (no circular references)
- **TEST-002**: Test intercompany transaction matching by reference
- **TEST-003**: Test intercompany transaction matching by amount and date
- **TEST-004**: Test elimination entry generation for AR/AP
- **TEST-005**: Test elimination entry generation for intercompany revenue/expense
- **TEST-006**: Test currency translation using closing rate for balance sheet items
- **TEST-007**: Test currency translation using average rate for P&L items
- **TEST-008**: Test minority interest calculation for 70% ownership
- **TEST-009**: Test full consolidation with 100% subsidiary
- **TEST-010**: Test proportional consolidation with 50% ownership
- **TEST-011**: Test multi-level consolidation (parent → sub → grandsub)
- **TEST-012**: Test consolidation with manual adjustments
- **TEST-013**: Test consolidation reversal restores original state
- **TEST-014**: Test consolidated financial statements accuracy
- **TEST-015**: Test performance with 20+ entities (PERF-001)

## 7. Risks & Assumptions

### Risks
- **RISK-001**: Incorrect elimination entries can materially misstate consolidated financials - Mitigation: Thorough testing, audit trail, approval workflow
- **RISK-002**: Unmatched intercompany transactions cause consolidation errors - Mitigation: Automated matching, manual review interface, alerts
- **RISK-003**: Currency translation errors accumulate across multiple entities - Mitigation: BCMath precision, validation against source systems
- **RISK-004**: Complex multi-level consolidations may have performance issues - Mitigation: Query optimization, caching, queue jobs

### Assumptions
- **ASSUMPTION-001**: Intercompany transactions are properly tagged at source
- **ASSUMPTION-002**: Account mappings are maintained and up-to-date
- **ASSUMPTION-003**: Consolidation is performed at least quarterly
- **ASSUMPTION-004**: All subsidiaries use same fiscal year calendar as parent
- **ASSUMPTION-005**: Ownership percentages are stable within fiscal year

## 8. Related Specifications / Further Reading

- [System Architecture Specification](../spec/architecture-nexus-erp.md)
- [Accounting Module Planning](../ACCOUNTING_MODULE_PLANNING.md)
- [Multi-Currency Implementation Plan](./feature-multi-currency-exchange-management-1.md)
- [General Ledger Implementation Plan](./feature-general-ledger-1.md)
- [IFRS 10 Consolidated Financial Statements](https://www.ifrs.org/issued-standards/list-of-standards/ifrs-10-consolidated-financial-statements/)
- [IFRS 3 Business Combinations](https://www.ifrs.org/issued-standards/list-of-standards/ifrs-3-business-combinations/)
