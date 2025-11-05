---
goal: Implement Budgeting & Planning Module
version: 1.0
date_created: 2025-11-05
last_updated: 2025-11-05
owner: Development Team
status: 'Planned'
tags: ["feature", "accounting", "budgeting", "planning", "forecasting"]
---

# Implement Budgeting & Planning Module

![Status: Planned](https://img.shields.io/badge/status-Planned-blue)

This implementation plan covers the Budgeting & Planning module including budget creation, allocation by accounts and cost centers, approval workflows, budget monitoring, variance analysis, and basic forecasting capabilities.

## 1. Requirements & Constraints

### Functional Requirements
- REQ-001: Create annual budgets linked to fiscal years, accounts, and cost centers
- REQ-002: Support budget allocation by period (monthly, quarterly)
- REQ-003: Implement budget versioning and revision tracking
- REQ-004: Create budget approval workflow (draft → submitted → approved → active)
- REQ-005: Track budget utilization in real-time against actual transactions
- REQ-006: Generate budget vs actual variance reports with alerts
- REQ-007: Support budget copying from previous periods
- REQ-008: Implement basic forecasting based on historical data
- REQ-009: Create budget monitoring dashboard with progress indicators

### Technical Constraints
- CON-001: Budget amounts must use BCMath for precision
- CON-002: Budget approval must use spatie model-status package
- CON-003: Budget lines must support multi-dimensional allocation (account + cost center)
- CON-004: Variance calculations must compare budget vs actual from posted transactions only

### Performance Requirements
- PERF-001: Budget utilization calculations must complete within 5 seconds for 1000+ budget lines
- PERF-002: Variance reports must use cached data for dashboard views
- PERF-003: Budget copy operation must handle 500+ lines efficiently

### Integration Requirements
- INT-001: Link to Account model for budget allocation
- INT-002: Link to CostCenter model for departmental budgeting
- INT-003: Pull actual data from JournalEntryLine for variance analysis
- INT-004: Integrate with FiscalYear and AccountingPeriod for period allocation

## 2. Implementation Steps

### Implementation Phase 1: Budget Model Foundation
- GOAL-001: Create Budget model with versioning and approval workflow
- TASK-001 Create budgets migration
- TASK-002 Add company_id, fiscal_year_id fields
- TASK-003 Add budget_name, description fields
- TASK-004 Add version_number for revision tracking
- TASK-005 Add budget_type enum (operating, capital, project-based)
- TASK-006 Add status enum using HasStatuses trait (draft, submitted, approved, active, archived)
- TASK-007 Add approved_by, approved_at fields
- TASK-008 Add parent_budget_id for version lineage
- TASK-009 Add is_active boolean flag
- TASK-010 Create Budget model with relationships
- TASK-011 Implement HasStatuses trait for workflow

### Implementation Phase 2: Budget Line Items
- GOAL-002: Create BudgetLine model for detailed account allocations
- TASK-012 Create budget_lines migration
- TASK-013 Add budget_id, account_id fields
- TASK-014 Add cost_center_id (nullable) for departmental budgets
- TASK-015 Add annual_amount field
- TASK-016 Add allocation_method enum (equal, custom, weighted)
- TASK-017 Add notes field for line-specific comments
- TASK-018 Create BudgetLine model with relationships
- TASK-019 Add validation: account must be detail-level (no parent accounts)
- TASK-020 Add validation: amount must be positive for expense/asset accounts

### Implementation Phase 3: Period Allocations
- GOAL-003: Create BudgetPeriodAllocation for monthly/quarterly breakdown
- TASK-021 Create budget_period_allocations migration
- TASK-022 Add budget_line_id, accounting_period_id fields
- TASK-023 Add allocated_amount field
- TASK-024 Add actual_amount field (calculated from transactions)
- TASK-025 Add variance_amount field (actual - allocated)
- TASK-026 Add variance_percentage field
- TASK-027 Add last_calculated_at timestamp
- TASK-028 Create BudgetPeriodAllocation model
- TASK-029 Add unique constraint (budget_line_id, accounting_period_id)
- TASK-030 Implement auto-calculation of actual amounts on demand

### Implementation Phase 4: Budget Allocation Actions
- GOAL-004: Create Actions for budget allocation and copying
- TASK-031 Create AllocateBudgetToPeriods Action
- TASK-032 Implement equal allocation (annual_amount / 12)
- TASK-033 Implement custom allocation (user-specified per period)
- TASK-034 Implement weighted allocation (by historical patterns)
- TASK-035 Validate total period allocations = annual amount
- TASK-036 Create CopyBudgetFromPrevious Action
- TASK-037 Copy budget structure from previous fiscal year
- TASK-038 Apply optional adjustment percentage (e.g., +5% inflation)
- TASK-039 Create new version with incremented version_number
- TASK-040 Link to parent_budget_id for version tracking

### Implementation Phase 5: Budget Utilization Tracking
- GOAL-005: Implement real-time budget utilization calculations
- TASK-041 Create CalculateBudgetUtilization Action
- TASK-042 Query journal entry lines by account and period
- TASK-043 Filter by cost center if specified in budget line
- TASK-044 Sum actual amounts from posted transactions only
- TASK-045 Calculate variance (actual - budget)
- TASK-046 Calculate variance percentage ((actual - budget) / budget * 100)
- TASK-047 Update BudgetPeriodAllocation with calculated values
- TASK-048 Cache utilization calculations for performance
- TASK-049 Create scheduled job to refresh calculations daily
- TASK-050 Add manual refresh option in UI

### Implementation Phase 6: Budget Approval Workflow
- GOAL-006: Implement budget approval workflow with status transitions
- TASK-051 Create SubmitBudgetForApproval Action
- TASK-052 Validate budget completeness (all periods allocated)
- TASK-053 Transition status from draft to submitted
- TASK-054 Notify approvers via email/notification
- TASK-055 Create ApproveBudget Action
- TASK-056 Check user has approval permission
- TASK-057 Transition status to approved
- TASK-058 Set approved_by and approved_at fields
- TASK-059 Create ActivateBudget Action
- TASK-060 Deactivate any existing active budget for same fiscal year
- TASK-061 Set is_active flag and transition to active status
- TASK-062 Create RejectBudget Action for sending back to draft

### Implementation Phase 7: Budget Variance Reports
- GOAL-007: Create budget monitoring reports and alerts
- TASK-063 Create GenerateBudgetUtilizationReport Action
- TASK-064 Accept parameters: budget_id, period_id, cost_center_id
- TASK-065 Group by account hierarchy
- TASK-066 Show columns: Budget, Actual, Variance, Variance %, Utilization %
- TASK-067 Color-code over-budget items (red) vs under-budget (green)
- TASK-068 Create CheckBudgetThresholdAlerts Action
- TASK-069 Identify budget lines exceeding threshold (e.g., >90% utilized)
- TASK-070 Generate notifications to budget owners
- TASK-071 Create scheduled job to run daily threshold checks
- TASK-072 Create BudgetComparisonReport for YoY budget analysis

### Implementation Phase 8: Forecasting Features
- GOAL-008: Implement basic forecasting based on historical data
- TASK-073 Create Forecast model for storing predictions
- TASK-074 Add forecast_name, fiscal_year_id, methodology fields
- TASK-075 Create ForecastLine model linked to accounts
- TASK-076 Add forecasted_amount, confidence_level fields
- TASK-077 Create GenerateSimpleForecast Action
- TASK-078 Use historical average method (last 12 months average)
- TASK-079 Use trend-based method (linear regression)
- TASK-080 Apply seasonal adjustments if detected
- TASK-081 Create CompareForecastToActual Action
- TASK-082 Calculate forecast accuracy metrics
- TASK-083 Generate variance reports (forecasted vs actual)

### Implementation Phase 9: Filament Resources
- GOAL-009: Create Filament resources for budget management
- TASK-084 Create BudgetResource in Filament
- TASK-085 Add form fields: budget_name, fiscal_year, type, description
- TASK-086 Add Repeater for BudgetLine items with account selector
- TASK-087 Add cost center selector (nullable)
- TASK-088 Add annual_amount with currency formatting
- TASK-089 Add allocation_method selector
- TASK-090 Create Period Allocation sub-form with monthly breakdown
- TASK-091 Add validation for total allocations = annual amount
- TASK-092 Add table with columns: name, fiscal_year, type, status, version
- TASK-093 Add filters: fiscal_year, status, type
- TASK-094 Add actions: Submit, Approve, Activate, Copy from Previous
- TASK-095 Add ViewBudget page showing utilization dashboard
- TASK-096 Create BudgetUtilizationWidget showing progress bars
- TASK-097 Create BudgetVarianceWidget showing top over/under budget items

### Implementation Phase 10: Testing and Documentation
- GOAL-010: Create comprehensive tests and documentation
- TASK-098 Create unit tests for AllocateBudgetToPeriods with all methods
- TASK-099 Create unit tests for CalculateBudgetUtilization
- TASK-100 Create unit tests for variance calculations
- TASK-101 Create feature test for budget approval workflow
- TASK-102 Create feature test for budget copying
- TASK-103 Create feature test for utilization tracking
- TASK-104 Test forecast generation with historical data
- TASK-105 Update ARCHITECTURAL_DECISIONS.md with budgeting module
- TASK-106 Update PROGRESS_CHECKLIST.md with completion status
- TASK-107 Create budgeting module user guide

## 3. Alternatives
- ALT-001: Rolling forecasts vs fixed annual budgets - Started with fixed, rolling deferred to future
- ALT-002: Bottom-up vs top-down budgeting - Supporting both approaches via flexible allocation
- ALT-003: Simple approval vs multi-level approval - Started with simple, multi-level deferred
- ALT-004: Advanced forecasting (ML-based) vs simple historical - Started with simple

## 4. Dependencies
- DEP-001: Account model with hierarchical structure
- DEP-002: CostCenter model for departmental budgeting
- DEP-003: FiscalYear and AccountingPeriod models
- DEP-004: JournalEntry and JournalEntryLine for actual data
- DEP-005: Spatie Model Status for approval workflow
- DEP-006: Laravel Notifications for approval alerts

## 5. Files
- FILE-001: database/migrations/YYYY_MM_DD_create_budgets_table.php
- FILE-002: database/migrations/YYYY_MM_DD_create_budget_lines_table.php
- FILE-003: database/migrations/YYYY_MM_DD_create_budget_period_allocations_table.php
- FILE-004: database/migrations/YYYY_MM_DD_create_forecasts_table.php
- FILE-005: database/migrations/YYYY_MM_DD_create_forecast_lines_table.php
- FILE-006: app/Models/Budget.php
- FILE-007: app/Models/BudgetLine.php
- FILE-008: app/Models/BudgetPeriodAllocation.php
- FILE-009: app/Models/Forecast.php
- FILE-010: app/Models/ForecastLine.php
- FILE-011: app/Actions/AllocateBudgetToPeriods.php
- FILE-012: app/Actions/CopyBudgetFromPrevious.php
- FILE-013: app/Actions/CalculateBudgetUtilization.php
- FILE-014: app/Actions/SubmitBudgetForApproval.php
- FILE-015: app/Actions/ApproveBudget.php
- FILE-016: app/Actions/ActivateBudget.php
- FILE-017: app/Actions/GenerateSimpleForecast.php
- FILE-018: app/Filament/Resources/BudgetResource.php
- FILE-019: app/Filament/Widgets/BudgetUtilizationWidget.php

## 6. Testing
- TEST-001 Test equal allocation distributes amount evenly across periods
- TEST-002 Test custom allocation validates total = annual amount
- TEST-003 Test weighted allocation uses historical patterns correctly
- TEST-004 Test budget utilization calculates variance accurately
- TEST-005 Test variance percentage calculation with edge cases (zero budget)
- TEST-006 Test budget approval workflow transitions correctly
- TEST-007 Test budget copying creates new version with linkage
- TEST-008 Test threshold alerts trigger at correct utilization levels
- TEST-009 Test forecast generation uses historical data correctly
- TEST-010 Test budget validation prevents overlapping active budgets

## 7. Risks & Assumptions
### Risks
- RISK-001 Budget revisions may become complex with many versions - Mitigation: Clear version lineage and UI
- RISK-002 Real-time utilization may impact performance - Mitigation: Caching and scheduled updates
- RISK-003 Users may forget to allocate to periods - Mitigation: Validation and auto-allocation defaults
- RISK-004 Forecast accuracy may be poor initially - Mitigation: Show confidence levels and allow manual adjustments

### Assumptions
- ASSUMPTION-001 Budget period aligns with accounting periods
- ASSUMPTION-002 Budget owners review utilization regularly
- ASSUMPTION-003 Historical data is available for forecasting
- ASSUMPTION-004 Only one active budget per fiscal year per company

## 8. Related Specifications / Further Reading
- System Architecture Specification: ../spec/architecture-nexus-erp.md
- Accounting Module Planning: ../ACCOUNTING_MODULE_PLANNING.md
- Financial Reporting Implementation Plan: ./feature-financial-reporting-1.md
- General Ledger Implementation Plan: ./feature-general-ledger-1.md
- Spatie Model Status Documentation: https://github.com/spatie/laravel-model-status
