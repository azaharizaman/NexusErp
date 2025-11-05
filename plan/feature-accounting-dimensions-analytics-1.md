---
goal: Implement Accounting Dimensions & Analytics
version: 1.0
date_created: 2025-11-05
last_updated: 2025-11-05
owner: Development Team
status: 'Planned'
tags: ["feature", "accounting", "dimensions", "analytics", "tags"]
---

# Implement Accounting Dimensions & Analytics

![Status: Planned](https://img.shields.io/badge/status-Planned-blue)

This implementation plan covers the Accounting Dimensions & Analytics module including flexible dimension configuration (cost center, project, department, product line, region), dimension tagging on transactions, multi-dimensional reporting and analysis, dimension-based budgeting, and analytics dashboards with drill-down capabilities.

## 1. Requirements & Constraints

### Functional Requirements
- **REQ-001**: Support configurable dimensions beyond standard cost centers
- **REQ-002**: Allow up to 10 active dimensions per company
- **REQ-003**: Tag journal entry lines with multiple dimension values
- **REQ-004**: Support mandatory vs optional dimensions per account
- **REQ-005**: Provide dimension hierarchies (e.g., region → country → branch)
- **REQ-006**: Generate reports sliced by any dimension combination
- **REQ-007**: Support dimension-based budget allocation and tracking
- **REQ-008**: Create analytics dashboards with dimension drill-down
- **REQ-009**: Enable dimension value consolidation and mapping
- **REQ-010**: Track dimension changes over time for historical accuracy

### Technical Constraints
- **CON-001**: Dimension tagging must not significantly impact transaction posting performance
- **CON-002**: Dimension data must be stored in a way that supports flexible querying
- **CON-003**: Dimension validation must occur before transaction posting
- **CON-004**: Dimension reports must use indexed queries for performance

### Performance Requirements
- **PERF-001**: Dimension-filtered reports must load within 5 seconds for 100K+ transactions
- **PERF-002**: Dimension rollup calculations must complete within 10 seconds
- **PERF-003**: Dimension analytics dashboard must use cached aggregations

### Integration Requirements
- **INT-001**: Integrate with JournalEntryLine for transaction tagging
- **INT-002**: Link to Budget module for dimension-based budgeting
- **INT-003**: Extend all financial reports to filter by dimensions
- **INT-004**: Link to Account for dimension requirement rules
- **INT-005**: Support AR/AP modules with dimension tagging

## 2. Implementation Steps

### Implementation Phase 1: Dimension Definition Model

- **GOAL-001**: Create Dimension model for defining dimension types

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-001 | Create dimensions migration | | |
| TASK-002 | Add company_id, dimension_name, dimension_code fields | | |
| TASK-003 | Add description, display_order fields | | |
| TASK-004 | Add is_hierarchical boolean (supports parent-child structure) | | |
| TASK-005 | Add is_mandatory boolean (required on all transactions) | | |
| TASK-006 | Add applies_to_account_types JSON (asset, liability, revenue, expense) | | |
| TASK-007 | Add is_active boolean flag | | |
| TASK-008 | Create Dimension model | | |
| TASK-009 | Add validation: max 10 active dimensions per company | | |
| TASK-010 | Seed common dimensions (CostCenter, Project, Department, Region, Product) | | |

### Implementation Phase 2: Dimension Value Model

- **GOAL-002**: Create DimensionValue model for specific dimension instances

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-011 | Create dimension_values migration | | |
| TASK-012 | Add dimension_id, value_code, value_name fields | | |
| TASK-013 | Add parent_value_id for hierarchical dimensions | | |
| TASK-014 | Add description, is_active fields | | |
| TASK-015 | Add effective_from_date, effective_to_date fields | | |
| TASK-016 | Add budget_allocation_percentage (for splitting budgets) | | |
| TASK-017 | Create DimensionValue model | | |
| TASK-018 | Add unique constraint (dimension_id, value_code) | | |
| TASK-019 | Add validation for hierarchical parent-child relationships | | |
| TASK-020 | Implement Sortable trait for custom ordering | | |

### Implementation Phase 3: Transaction Dimension Tagging

- **GOAL-003**: Extend JournalEntryLine to support dimension tagging

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-021 | Create transaction_dimensions migration (pivot table) | | |
| TASK-022 | Add taggable_type, taggable_id (polymorphic to JournalEntryLine, InvoiceLine, etc.) | | |
| TASK-023 | Add dimension_id, dimension_value_id fields | | |
| TASK-024 | Create TransactionDimension model | | |
| TASK-025 | Add unique constraint (taggable_type, taggable_id, dimension_id) | | |
| TASK-026 | Add morphMany relationship to JournalEntryLine | | |
| TASK-027 | Create TagTransactionWithDimensions Action | | |
| TASK-028 | Accept transaction, dimensions array (dimension_id => value_id) | | |
| TASK-029 | Validate mandatory dimensions are present | | |
| TASK-030 | Create TransactionDimension records | | |

### Implementation Phase 4: Dimension Validation Rules

- **GOAL-004**: Implement validation rules for dimension requirements

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-031 | Create account_dimension_rules migration | | |
| TASK-032 | Add account_id, dimension_id fields | | |
| TASK-033 | Add is_required boolean | | |
| TASK-034 | Add allowed_dimension_values JSON (restrict to specific values) | | |
| TASK-035 | Create AccountDimensionRule model | | |
| TASK-036 | Add unique constraint (account_id, dimension_id) | | |
| TASK-037 | Create ValidateTransactionDimensions Action | | |
| TASK-038 | Check mandatory dimensions are present | | |
| TASK-039 | Check account-specific dimension requirements | | |
| TASK-040 | Check dimension values are in allowed list (if restricted) | | |
| TASK-041 | Return validation errors array | | |

### Implementation Phase 5: Dimension Hierarchies

- **GOAL-005**: Support hierarchical dimension structures

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-042 | Create GetDimensionHierarchy Action | | |
| TASK-043 | Query dimension values with parent-child relationships | | |
| TASK-044 | Build tree structure recursively | | |
| TASK-045 | Return nested array or collection | | |
| TASK-046 | Create RollupDimensionHierarchy Action | | |
| TASK-047 | Accept dimension_id, from_level, to_level | | |
| TASK-048 | Aggregate transaction amounts from child to parent values | | |
| TASK-049 | Support multi-level rollup (e.g., branch → region → country) | | |

### Implementation Phase 6: Dimension Reporting Actions

- **GOAL-006**: Create Actions for dimension-based reporting

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-050 | Create GenerateDimensionAnalysisReport Action | | |
| TASK-051 | Accept dimensions array (which dimensions to analyze) | | |
| TASK-052 | Accept filters: account, date_range, fiscal_year | | |
| TASK-053 | Query journal entry lines with dimension tags | | |
| TASK-054 | Group by selected dimension values | | |
| TASK-055 | Calculate totals (debit, credit, net) per dimension combination | | |
| TASK-056 | Support cross-tabulation (rows = dimension1, columns = dimension2) | | |
| TASK-057 | Return structured data for rendering | | |
| TASK-058 | Create GenerateDimensionBudgetVariance Action | | |
| TASK-059 | Compare dimension-tagged actual vs budget by dimension value | | |

### Implementation Phase 7: Dimension-Based Budget Extension

- **GOAL-007**: Extend Budget module to support dimension allocation

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-060 | Add dimensions JSON field to budget_lines table | | |
| TASK-061 | Store dimension_id => value_id mappings | | |
| TASK-062 | Update BudgetLine model to handle dimension tagging | | |
| TASK-063 | Create AllocateBudgetByDimension Action | | |
| TASK-064 | Accept budget_line_id, dimension_id, allocation_percentages array | | |
| TASK-065 | Split budget amount across dimension values | | |
| TASK-066 | Create sub-budget-lines for each dimension value | | |
| TASK-067 | Update CalculateBudgetUtilization to filter by dimensions | | |
| TASK-068 | Match actual transaction dimensions to budget dimensions | | |

### Implementation Phase 8: Dimension Analytics Views

- **GOAL-008**: Create database views for dimension analytics performance

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-069 | Create dimension_transaction_summary view | | |
| TASK-070 | Join journal_entry_lines with transaction_dimensions | | |
| TASK-071 | Include account, dimension, dimension_value, amount columns | | |
| TASK-072 | Add period_id, fiscal_year_id for time filtering | | |
| TASK-073 | Create indexes on dimension_id, dimension_value_id, period_id | | |
| TASK-074 | Create dimension_rollup_balances view for aggregated balances | | |
| TASK-075 | Test view performance with 100K+ transactions | | |

### Implementation Phase 9: Filament Resources

- **GOAL-009**: Create Filament resources for dimension management

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-076 | Create DimensionResource | | |
| TASK-077 | Add form fields: name, code, is_hierarchical, is_mandatory | | |
| TASK-078 | Add applies_to_account_types multi-select | | |
| TASK-079 | Add table columns: name, code, is_hierarchical, is_mandatory, is_active | | |
| TASK-080 | Add filters: is_active, is_mandatory | | |
| TASK-081 | Create DimensionValueResource | | |
| TASK-082 | Add hierarchical tree view for dimension values | | |
| TASK-083 | Add form fields: dimension, code, name, parent_value | | |
| TASK-084 | Add drag-and-drop reordering for values | | |
| TASK-085 | Create AccountDimensionRuleResource | | |
| TASK-086 | Add form for defining account-specific dimension rules | | |
| TASK-087 | Add bulk rule creation interface | | |

### Implementation Phase 10: Dimension Analysis Reports

- **GOAL-010**: Create reporting pages for dimension analytics

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-088 | Create DimensionAnalysisReportPage in Filament | | |
| TASK-089 | Add dimension selector (which dimension to analyze) | | |
| TASK-090 | Add secondary dimension for cross-tabulation (optional) | | |
| TASK-091 | Add account, date range, cost center filters | | |
| TASK-092 | Render pivot table or matrix view | | |
| TASK-093 | Add drill-down to transaction detail | | |
| TASK-094 | Support export to Excel with dimension breakdowns | | |
| TASK-095 | Create DimensionBudgetVarianceReportPage | | |
| TASK-096 | Show budget vs actual by dimension value | | |
| TASK-097 | Color-code favorable vs unfavorable variances | | |

### Implementation Phase 11: Dimension Analytics Dashboard

- **GOAL-011**: Build analytics dashboard with dimension widgets

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-098 | Create DimensionAnalyticsDashboardPage | | |
| TASK-099 | Create TopDimensionValuesByRevenue widget | | |
| TASK-100 | Show top 10 dimension values by revenue | | |
| TASK-101 | Create DimensionTrendWidget showing monthly trends | | |
| TASK-102 | Create DimensionProfitabilityWidget (revenue - expense by dimension) | | |
| TASK-103 | Create DimensionBudgetUtilizationWidget | | |
| TASK-104 | Show progress bars for budget utilization by dimension value | | |
| TASK-105 | Implement caching for dashboard widgets (5-minute TTL) | | |
| TASK-106 | Add dimension selector to change dashboard focus | | |

### Implementation Phase 12: Dimension Integration with Financial Reports

- **GOAL-012**: Extend existing reports to support dimension filtering

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-107 | Update GenerateBalanceSheet to accept dimension filters | | |
| TASK-108 | Filter journal entry lines by selected dimension values | | |
| TASK-109 | Update GenerateProfitAndLoss for dimension filtering | | |
| TASK-110 | Update GenerateTrialBalance for dimension filtering | | |
| TASK-111 | Update GeneralLedgerReport for dimension filtering | | |
| TASK-112 | Add dimension filter UI to all report pages | | |
| TASK-113 | Show dimension context in report headers | | |

### Implementation Phase 13: Dimension Value Mapping

- **GOAL-013**: Support mapping/consolidation of dimension values

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-114 | Create dimension_value_mappings migration | | |
| TASK-115 | Add from_dimension_value_id, to_dimension_value_id fields | | |
| TASK-116 | Add mapping_type enum (consolidation, reclassification) | | |
| TASK-117 | Add effective_from_date, effective_to_date fields | | |
| TASK-118 | Create DimensionValueMapping model | | |
| TASK-119 | Create ApplyDimensionValueMapping Action | | |
| TASK-120 | Remap historical transactions to new dimension value | | |
| TASK-121 | Use mappings in rollup calculations | | |

### Implementation Phase 14: Testing and Documentation

- **GOAL-014**: Create comprehensive tests and documentation

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-122 | Create unit tests for dimension validation rules | | |
| TASK-123 | Test mandatory dimension enforcement | | |
| TASK-124 | Create unit tests for dimension hierarchy rollup | | |
| TASK-125 | Test multi-level hierarchy aggregation | | |
| TASK-126 | Create unit tests for dimension analysis report generation | | |
| TASK-127 | Test cross-tabulation with two dimensions | | |
| TASK-128 | Create feature test for dimension tagging workflow | | |
| TASK-129 | Create feature test for dimension-based budget allocation | | |
| TASK-130 | Test dimension filtering on financial reports | | |
| TASK-131 | Test performance with 100K+ dimension-tagged transactions (PERF-001) | | |
| TASK-132 | Update ARCHITECTURAL_DECISIONS.md with dimension design | | |
| TASK-133 | Update PROGRESS_CHECKLIST.md with completion status | | |
| TASK-134 | Create accounting dimensions module user guide | | |
| TASK-135 | Document best practices for dimension structure | | |

## 3. Alternatives

- **ALT-001**: Fixed dimension fields vs flexible dimension framework - Chose flexible for extensibility
- **ALT-002**: JSON storage vs pivot table for dimensions - Chose pivot table for query performance
- **ALT-003**: Mandatory dimension tagging for all transactions vs optional - Supporting both with configuration
- **ALT-004**: Single dimension per transaction vs multiple - Supporting multiple for rich analytics

## 4. Dependencies

- **DEP-001**: JournalEntry and JournalEntryLine models
- **DEP-002**: Account model for dimension requirement rules
- **DEP-003**: Budget module for dimension-based budgeting
- **DEP-004**: FiscalYear and AccountingPeriod for time-based analysis
- **DEP-005**: All financial report Actions for dimension filtering
- **DEP-006**: Laravel Cache for analytics dashboard performance

## 5. Files

- **FILE-001**: `database/migrations/YYYY_MM_DD_create_dimensions_table.php`
- **FILE-002**: `database/migrations/YYYY_MM_DD_create_dimension_values_table.php`
- **FILE-003**: `database/migrations/YYYY_MM_DD_create_transaction_dimensions_table.php`
- **FILE-004**: `database/migrations/YYYY_MM_DD_create_account_dimension_rules_table.php`
- **FILE-005**: `database/migrations/YYYY_MM_DD_create_dimension_value_mappings_table.php`
- **FILE-006**: `database/migrations/YYYY_MM_DD_add_dimensions_to_budget_lines.php`
- **FILE-007**: `database/migrations/YYYY_MM_DD_create_dimension_transaction_summary_view.php`
- **FILE-008**: `database/seeders/DimensionSeeder.php`
- **FILE-009**: `app/Models/Dimension.php`
- **FILE-010**: `app/Models/DimensionValue.php`
- **FILE-011**: `app/Models/TransactionDimension.php`
- **FILE-012**: `app/Models/AccountDimensionRule.php`
- **FILE-013**: `app/Models/DimensionValueMapping.php`
- **FILE-014**: `app/Actions/TagTransactionWithDimensions.php`
- **FILE-015**: `app/Actions/ValidateTransactionDimensions.php`
- **FILE-016**: `app/Actions/GetDimensionHierarchy.php`
- **FILE-017**: `app/Actions/RollupDimensionHierarchy.php`
- **FILE-018**: `app/Actions/GenerateDimensionAnalysisReport.php`
- **FILE-019**: `app/Actions/AllocateBudgetByDimension.php`
- **FILE-020**: `app/Filament/Resources/DimensionResource.php`
- **FILE-021**: `app/Filament/Resources/DimensionValueResource.php`
- **FILE-022**: `app/Filament/Pages/DimensionAnalysisReportPage.php`
- **FILE-023**: `app/Filament/Pages/DimensionAnalyticsDashboardPage.php`
- **FILE-024**: `app/Filament/Widgets/TopDimensionValuesByRevenue.php`

## 6. Testing

- **TEST-001**: Test dimension creation and configuration
- **TEST-002**: Test dimension value hierarchy structure
- **TEST-003**: Test mandatory dimension validation on transaction posting
- **TEST-004**: Test account-specific dimension requirement enforcement
- **TEST-005**: Test transaction dimension tagging with multiple dimensions
- **TEST-006**: Test dimension hierarchy rollup aggregation
- **TEST-007**: Test multi-level hierarchy (3+ levels) rollup
- **TEST-008**: Test dimension analysis report with single dimension
- **TEST-009**: Test cross-tabulation with two dimensions
- **TEST-010**: Test dimension-based budget allocation
- **TEST-011**: Test budget variance by dimension value
- **TEST-012**: Test financial report filtering by dimension
- **TEST-013**: Test dimension value mapping/consolidation
- **TEST-014**: Test dimension analytics dashboard widgets
- **TEST-015**: Test performance with 100K+ dimension-tagged transactions (PERF-001)

## 7. Risks & Assumptions

### Risks
- **RISK-001**: Too many dimensions may confuse users - Mitigation: Training, clear naming conventions, optional dimensions
- **RISK-002**: Dimension tagging may be forgotten or inconsistent - Mitigation: Mandatory dimension validation, UI defaults
- **RISK-003**: Complex hierarchies may cause performance issues - Mitigation: Database views, indexes, caching
- **RISK-004**: Dimension structure changes may break historical reports - Mitigation: Effective dating, dimension value mapping

### Assumptions
- **ASSUMPTION-001**: Maximum 10 active dimensions per company is sufficient
- **ASSUMPTION-002**: Dimension values are relatively stable (not frequently changed)
- **ASSUMPTION-003**: Users understand dimension concept and usage
- **ASSUMPTION-004**: Dimension hierarchies are typically 2-3 levels deep
- **ASSUMPTION-005**: Dimension tagging is applied consistently across transactions

## 8. Related Specifications / Further Reading

- [System Architecture Specification](../spec/architecture-nexus-erp.md)
- [Accounting Module Planning](../ACCOUNTING_MODULE_PLANNING.md)
- [General Ledger Implementation Plan](./feature-general-ledger-1.md)
- [Budgeting & Planning Implementation Plan](./feature-budgeting-planning-1.md)
- [Cost Accounting Best Practices](https://www.accountingtools.com/articles/cost-accounting)
