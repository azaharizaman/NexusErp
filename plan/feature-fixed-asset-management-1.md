---
goal: Implement Fixed Asset Management Module
version: 1.0
date_created: 2025-11-05
last_updated: 2025-11-05
owner: Development Team
status: 'Planned'
tags: ["feature", "accounting", "fixed-assets", "depreciation", "capex"]
---

# Implement Fixed Asset Management Module

![Status: Planned](https://img.shields.io/badge/status-Planned-blue)

This implementation plan covers the Fixed Asset Management module including asset acquisition, capitalization, depreciation calculations, impairment, disposals, asset transfers, physical inventory reconciliation, and comprehensive asset reporting with GL integration.

## 1. Requirements & Constraints

### Functional Requirements
- **REQ-001**: Create asset register with categories, useful life, and depreciation methods
- **REQ-002**: Support asset acquisition from purchase orders or direct entry
- **REQ-003**: Capitalize assets and start depreciation tracking
- **REQ-004**: Calculate depreciation using multiple methods (straight-line, declining balance, units of production)
- **REQ-005**: Support asset revaluation and impairment with approval workflow
- **REQ-006**: Handle asset disposals with gain/loss calculation
- **REQ-007**: Track asset transfers between cost centers/locations/companies
- **REQ-008**: Maintain asset metadata (serial number, warranty, location, custodian)
- **REQ-009**: Support physical inventory reconciliation
- **REQ-010**: Generate asset reports (register, depreciation schedule, disposal summary)

### Technical Constraints
- **CON-001**: Depreciation calculations must use BCMath for precision
- **CON-002**: Asset transactions must use HasSerialNumbering with FA- prefix
- **CON-003**: Asset status must use spatie model-status package
- **CON-004**: All GL postings must be reversible for corrections

### Performance Requirements
- **PERF-001**: Monthly depreciation run must complete within 10 minutes for 10,000+ assets
- **PERF-002**: Asset register report must load within 5 seconds for 5,000+ assets
- **PERF-003**: Depreciation schedule generation must handle 100+ years of projections

### Integration Requirements
- **INT-001**: Link to JournalEntry for GL posting
- **INT-002**: Link to Account for asset, accumulated depreciation, and expense accounts
- **INT-003**: Link to PurchaseOrder for asset acquisition tracking
- **INT-004**: Link to CostCenter for departmental asset tracking
- **INT-005**: Integrate with FiscalYear and AccountingPeriod for depreciation scheduling

## 2. Implementation Steps

### Implementation Phase 1: Asset Category Model

- **GOAL-001**: Create AssetCategory model for grouping and defaults

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-001 | Create asset_categories migration | | |
| TASK-002 | Add company_id, category_name, description fields | | |
| TASK-003 | Add parent_id for hierarchical categories | | |
| TASK-004 | Add default_useful_life_years, default_residual_value_percent fields | | |
| TASK-005 | Add default_depreciation_method enum (straight_line, declining_balance, units_of_production) | | |
| TASK-006 | Add default_asset_account_id, default_accumulated_depreciation_account_id fields | | |
| TASK-007 | Add default_depreciation_expense_account_id field | | |
| TASK-008 | Add is_active boolean flag | | |
| TASK-009 | Create AssetCategory model with relationships | | |
| TASK-010 | Add validation for hierarchical structure (no circular references) | | |

### Implementation Phase 2: Asset Master Model

- **GOAL-002**: Create Asset model with comprehensive tracking fields

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-011 | Create assets migration | | |
| TASK-012 | Add serial_number with HasSerialNumbering (FA-YYYY-XXXX format) | | |
| TASK-013 | Add company_id, asset_category_id fields | | |
| TASK-014 | Add asset_name, description, asset_tag fields | | |
| TASK-015 | Add manufacturer, model_number, serial_number_manufacturer fields | | |
| TASK-016 | Add acquisition_date, capitalization_date, in_service_date fields | | |
| TASK-017 | Add acquisition_cost, residual_value, depreciable_amount fields | | |
| TASK-018 | Add useful_life_years, depreciation_method enum fields | | |
| TASK-019 | Add current_book_value, accumulated_depreciation fields | | |
| TASK-020 | Add location, custodian_user_id, cost_center_id fields | | |
| TASK-021 | Add warranty_expiry_date, maintenance_schedule fields | | |
| TASK-022 | Add status enum using HasStatuses trait (under_construction, active, fully_depreciated, disposed, impaired) | | |
| TASK-023 | Add disposal_date, disposal_amount, disposal_method fields (nullable) | | |
| TASK-024 | Create Asset model with relationships | | |
| TASK-025 | Implement HasStatuses and HasSerialNumbering traits | | |

### Implementation Phase 3: Asset Transaction Model

- **GOAL-003**: Create AssetTransaction for tracking all asset lifecycle events

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-026 | Create asset_transactions migration | | |
| TASK-027 | Add asset_id, transaction_date, transaction_type enum fields | | |
| TASK-028 | Add transaction_type values: acquisition, capitalization, depreciation, revaluation, impairment, transfer, disposal | | |
| TASK-029 | Add amount, quantity (for units of production) fields | | |
| TASK-030 | Add description, reference_number fields | | |
| TASK-031 | Add journal_entry_id, is_posted_to_gl, posted_to_gl_at fields | | |
| TASK-032 | Add from_cost_center_id, to_cost_center_id (for transfers) | | |
| TASK-033 | Add approved_by, approved_at fields | | |
| TASK-034 | Create AssetTransaction model | | |
| TASK-035 | Add relationships to Asset, JournalEntry, CostCenter | | |

### Implementation Phase 4: Depreciation Schedule Model

- **GOAL-004**: Create DepreciationSchedule for monthly depreciation tracking

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-036 | Create depreciation_schedules migration | | |
| TASK-037 | Add asset_id, accounting_period_id fields | | |
| TASK-038 | Add scheduled_amount, actual_amount fields | | |
| TASK-039 | Add opening_book_value, closing_book_value fields | | |
| TASK-040 | Add opening_accumulated_depreciation, closing_accumulated_depreciation fields | | |
| TASK-041 | Add is_posted, posted_at, journal_entry_id fields | | |
| TASK-042 | Add unique constraint (asset_id, accounting_period_id) | | |
| TASK-043 | Create DepreciationSchedule model | | |

### Implementation Phase 5: Asset Acquisition Actions

- **GOAL-005**: Create Actions for asset acquisition and capitalization

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-044 | Create RecordAssetAcquisition Action | | |
| TASK-045 | Accept asset details and acquisition cost | | |
| TASK-046 | Create Asset record with status = under_construction | | |
| TASK-047 | Create AssetTransaction for acquisition | | |
| TASK-048 | Link to PurchaseOrder if applicable | | |
| TASK-049 | Create CapitalizeAsset Action | | |
| TASK-050 | Validate asset has acquisition cost | | |
| TASK-051 | Set capitalization_date and in_service_date | | |
| TASK-052 | Calculate depreciable_amount (acquisition_cost - residual_value) | | |
| TASK-053 | Transition status to active | | |
| TASK-054 | Create AssetTransaction for capitalization | | |
| TASK-055 | Generate depreciation schedule for asset lifetime | | |

### Implementation Phase 6: Depreciation Calculation Engine

- **GOAL-006**: Implement depreciation calculations for all methods

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-056 | Create CalculateDepreciation Action | | |
| TASK-057 | Implement straight-line method (depreciable_amount / useful_life / 12) | | |
| TASK-058 | Handle partial first and last periods for straight-line | | |
| TASK-059 | Implement declining balance method (book_value * rate) | | |
| TASK-060 | Calculate declining balance rate (1 - (residual_value / cost) ^ (1 / useful_life)) | | |
| TASK-061 | Implement units of production method (depreciable_amount * units_used / total_expected_units) | | |
| TASK-062 | Handle edge case: stop depreciation when book_value = residual_value | | |
| TASK-063 | Use BCMath for all calculations with 4 decimal precision | | |
| TASK-064 | Create RunMonthlyDepreciation Action | | |
| TASK-065 | Query all active assets | | |
| TASK-066 | Calculate depreciation for current period | | |
| TASK-067 | Update DepreciationSchedule records | | |
| TASK-068 | Update Asset accumulated_depreciation and current_book_value | | |
| TASK-069 | Create AssetTransaction for each depreciation | | |

### Implementation Phase 7: Depreciation GL Posting

- **GOAL-007**: Create Actions for posting depreciation to GL

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-070 | Create PostDepreciationToGL Action | | |
| TASK-071 | Accept accounting_period_id or date range | | |
| TASK-072 | Query unposted depreciation schedules | | |
| TASK-073 | Group by depreciation_expense_account and accumulated_depreciation_account | | |
| TASK-074 | Create journal entry: Debit Depreciation Expense, Credit Accumulated Depreciation | | |
| TASK-075 | Add journal entry lines for each asset category or cost center | | |
| TASK-076 | Link journal_entry_id to DepreciationSchedule records | | |
| TASK-077 | Mark schedules as is_posted = true | | |
| TASK-078 | Handle reversal journal entries for corrections | | |

### Implementation Phase 8: Asset Revaluation and Impairment

- **GOAL-008**: Implement asset revaluation and impairment with approval

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-079 | Create RevalueAsset Action | | |
| TASK-080 | Accept asset_id, new_valuation_amount, revaluation_date | | |
| TASK-081 | Calculate revaluation surplus/deficit (new_value - current_book_value) | | |
| TASK-082 | Create AssetTransaction for revaluation | | |
| TASK-083 | Update Asset acquisition_cost and recalculate depreciable_amount | | |
| TASK-084 | Regenerate depreciation schedule for remaining useful life | | |
| TASK-085 | Create journal entry: Debit/Credit Asset, Credit/Debit Revaluation Reserve | | |
| TASK-086 | Create RecordAssetImpairment Action | | |
| TASK-087 | Accept asset_id, impairment_amount, impairment_date | | |
| TASK-088 | Calculate impairment loss (book_value - recoverable_amount) | | |
| TASK-089 | Create AssetTransaction for impairment | | |
| TASK-090 | Update Asset accumulated_depreciation and book_value | | |
| TASK-091 | Transition status to impaired | | |
| TASK-092 | Create journal entry: Debit Impairment Loss, Credit Accumulated Impairment | | |

### Implementation Phase 9: Asset Disposal

- **GOAL-009**: Implement asset disposal with gain/loss calculation

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-093 | Create DisposeAsset Action | | |
| TASK-094 | Accept asset_id, disposal_date, disposal_amount, disposal_method enum | | |
| TASK-095 | Disposal methods: sale, scrap, donation, trade-in | | |
| TASK-096 | Calculate final book value at disposal date | | |
| TASK-097 | Calculate gain/loss (disposal_amount - book_value) | | |
| TASK-098 | Create AssetTransaction for disposal | | |
| TASK-099 | Update Asset disposal fields and transition status to disposed | | |
| TASK-100 | Create journal entry for disposal | | |
| TASK-101 | Debit: Cash/Receivable (disposal_amount), Accumulated Depreciation | | |
| TASK-102 | Credit: Asset Cost, Gain on Disposal (or Debit Loss on Disposal) | | |

### Implementation Phase 10: Asset Transfer

- **GOAL-010**: Implement asset transfers between cost centers/locations

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-103 | Create TransferAsset Action | | |
| TASK-104 | Accept asset_id, to_cost_center_id, to_location, transfer_date | | |
| TASK-105 | Validate asset is active and not fully depreciated | | |
| TASK-106 | Create AssetTransaction for transfer with from/to cost centers | | |
| TASK-107 | Update Asset cost_center_id and location | | |
| TASK-108 | Optionally create GL entry if transferring between companies | | |
| TASK-109 | Send notification to new custodian | | |

### Implementation Phase 11: Physical Inventory Reconciliation

- **GOAL-011**: Implement physical asset inventory tracking and reconciliation

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-110 | Create AssetInventory model for tracking counts | | |
| TASK-111 | Add inventory_date, location, counted_by fields | | |
| TASK-112 | Add status enum (in_progress, completed, reconciled) | | |
| TASK-113 | Create AssetInventoryItem model for line items | | |
| TASK-114 | Add asset_id, is_found, condition, notes fields | | |
| TASK-115 | Create StartPhysicalInventory Action | | |
| TASK-116 | Generate inventory list for selected location/category | | |
| TASK-117 | Create RecordInventoryCount Action | | |
| TASK-118 | Mark assets as found/not found | | |
| TASK-119 | Create ReconcileInventory Action | | |
| TASK-120 | Identify missing assets and generate alerts | | |
| TASK-121 | Update asset status for missing items | | |

### Implementation Phase 12: Filament Resources

- **GOAL-012**: Create Filament resources for asset management

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-122 | Create AssetCategoryResource | | |
| TASK-123 | Add hierarchical tree view for categories | | |
| TASK-124 | Add form fields for category defaults | | |
| TASK-125 | Create AssetResource | | |
| TASK-126 | Add form with all asset fields grouped logically | | |
| TASK-127 | Add asset_tag barcode generation | | |
| TASK-128 | Add media uploader for asset photos and documents | | |
| TASK-129 | Add table columns: serial_number, asset_name, category, status, book_value | | |
| TASK-130 | Add filters: category, status, cost_center, location | | |
| TASK-131 | Add actions: Capitalize, Revalue, Impair, Dispose, Transfer | | |
| TASK-132 | Create ViewAsset page showing depreciation schedule | | |
| TASK-133 | Add timeline widget showing all transactions | | |
| TASK-134 | Create AssetTransferResource for managing transfers | | |
| TASK-135 | Create PhysicalInventoryResource for inventory counts | | |

### Implementation Phase 13: Asset Reports

- **GOAL-013**: Create asset reporting pages and exports

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-136 | Create AssetRegisterReportPage | | |
| TASK-137 | Show all assets with key details and current book values | | |
| TASK-138 | Add filters: category, status, location, acquisition date range | | |
| TASK-139 | Add grouping by category with subtotals | | |
| TASK-140 | Create DepreciationScheduleReportPage | | |
| TASK-141 | Show projected depreciation for selected period | | |
| TASK-142 | Support multi-year projection | | |
| TASK-143 | Create AssetDisposalReportPage | | |
| TASK-144 | Show disposed assets with gain/loss analysis | | |
| TASK-145 | Create AssetMaintenanceReportPage | | |
| TASK-146 | Show assets requiring maintenance or warranty expiring | | |
| TASK-147 | Implement Excel export for all reports | | |

### Implementation Phase 14: Scheduled Jobs

- **GOAL-014**: Create scheduled jobs for automation

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-148 | Create RunMonthlyDepreciationJob | | |
| TASK-149 | Schedule to run on first day of each month | | |
| TASK-150 | Calculate and post depreciation for previous month | | |
| TASK-151 | Send completion notification to accounting team | | |
| TASK-152 | Create AssetMaintenanceAlertJob | | |
| TASK-153 | Check warranty expiry dates and maintenance schedules | | |
| TASK-154 | Send alerts to custodians and asset managers | | |
| TASK-155 | Create AssetRevaluationReminderJob | | |
| TASK-156 | Remind for assets due for revaluation (based on policy) | | |

### Implementation Phase 15: Testing and Documentation

- **GOAL-015**: Create comprehensive tests and documentation

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-157 | Create unit tests for straight-line depreciation | | |
| TASK-158 | Test partial first and last periods | | |
| TASK-159 | Create unit tests for declining balance depreciation | | |
| TASK-160 | Create unit tests for units of production depreciation | | |
| TASK-161 | Create unit tests for disposal gain/loss calculations | | |
| TASK-162 | Create feature test for asset acquisition workflow | | |
| TASK-163 | Create feature test for capitalization and depreciation | | |
| TASK-164 | Create feature test for revaluation and impairment | | |
| TASK-165 | Create feature test for disposal and GL posting | | |
| TASK-166 | Create feature test for asset transfer workflow | | |
| TASK-167 | Test depreciation run performance with 10,000+ assets | | |
| TASK-168 | Update ARCHITECTURAL_DECISIONS.md with asset module design | | |
| TASK-169 | Update PROGRESS_CHECKLIST.md with completion status | | |
| TASK-170 | Create fixed asset management user guide | | |

## 3. Alternatives

- **ALT-001**: External fixed asset system integration vs built-in - Chose built-in for control and customization
- **ALT-002**: Pre-calculated depreciation schedule vs on-demand calculation - Chose pre-calculated for performance
- **ALT-003**: Component depreciation (separate useful lives) vs whole asset - Deferred component tracking to future phase
- **ALT-004**: Tax depreciation vs book depreciation - Started with book depreciation, tax tracking deferred

## 4. Dependencies

- **DEP-001**: Account model with asset, accumulated depreciation, and expense account types
- **DEP-002**: JournalEntry and JournalEntryLine models for GL integration
- **DEP-003**: CostCenter model for departmental asset tracking
- **DEP-004**: FiscalYear and AccountingPeriod for depreciation scheduling
- **DEP-005**: PurchaseOrder model for acquisition tracking (optional)
- **DEP-006**: Spatie Laravel Media Library for asset photos and documents
- **DEP-007**: Laravel Queue for monthly depreciation job

## 5. Files

- **FILE-001**: `database/migrations/YYYY_MM_DD_create_asset_categories_table.php`
- **FILE-002**: `database/migrations/YYYY_MM_DD_create_assets_table.php`
- **FILE-003**: `database/migrations/YYYY_MM_DD_create_asset_transactions_table.php`
- **FILE-004**: `database/migrations/YYYY_MM_DD_create_depreciation_schedules_table.php`
- **FILE-005**: `database/migrations/YYYY_MM_DD_create_asset_inventories_table.php`
- **FILE-006**: `database/migrations/YYYY_MM_DD_create_asset_inventory_items_table.php`
- **FILE-007**: `app/Models/AssetCategory.php`
- **FILE-008**: `app/Models/Asset.php`
- **FILE-009**: `app/Models/AssetTransaction.php`
- **FILE-010**: `app/Models/DepreciationSchedule.php`
- **FILE-011**: `app/Models/AssetInventory.php`
- **FILE-012**: `app/Actions/RecordAssetAcquisition.php`
- **FILE-013**: `app/Actions/CapitalizeAsset.php`
- **FILE-014**: `app/Actions/CalculateDepreciation.php`
- **FILE-015**: `app/Actions/RunMonthlyDepreciation.php`
- **FILE-016**: `app/Actions/PostDepreciationToGL.php`
- **FILE-017**: `app/Actions/RevalueAsset.php`
- **FILE-018**: `app/Actions/RecordAssetImpairment.php`
- **FILE-019**: `app/Actions/DisposeAsset.php`
- **FILE-020**: `app/Actions/TransferAsset.php`
- **FILE-021**: `app/Filament/Resources/AssetResource.php`
- **FILE-022**: `app/Filament/Resources/AssetCategoryResource.php`
- **FILE-023**: `app/Filament/Pages/AssetRegisterReportPage.php`
- **FILE-024**: `app/Console/Commands/RunMonthlyDepreciationJob.php`

## 6. Testing

- **TEST-001**: Test straight-line depreciation calculates correctly for full years
- **TEST-002**: Test partial first year depreciation (mid-year acquisition)
- **TEST-003**: Test partial last year depreciation stops at residual value
- **TEST-004**: Test declining balance depreciation calculates rate correctly
- **TEST-005**: Test declining balance stops at residual value
- **TEST-006**: Test units of production depreciation with varying usage
- **TEST-007**: Test disposal gain calculation when sale > book value
- **TEST-008**: Test disposal loss calculation when sale < book value
- **TEST-009**: Test revaluation recalculates depreciation schedule correctly
- **TEST-010**: Test impairment reduces book value and adjusts depreciation
- **TEST-011**: Test asset transfer updates cost center and location
- **TEST-012**: Test GL posting for depreciation creates correct journal entries
- **TEST-013**: Test GL posting for disposal handles all account movements
- **TEST-014**: Test monthly depreciation run handles 10,000+ assets within 10 minutes
- **TEST-015**: Test physical inventory reconciliation identifies missing assets

## 7. Risks & Assumptions

### Risks
- **RISK-001**: Incorrect depreciation calculations due to edge cases - Mitigation: Comprehensive unit tests and manual review
- **RISK-002**: Performance issues with large asset counts - Mitigation: Database indexes, query optimization, queue jobs
- **RISK-003**: Asset revaluation errors impacting financial statements - Mitigation: Approval workflow and audit trail
- **RISK-004**: Missing assets not detected promptly - Mitigation: Regular physical inventory and alerts

### Assumptions
- **ASSUMPTION-001**: Assets are capitalized in the month they are placed in service
- **ASSUMPTION-002**: Depreciation is calculated monthly for accuracy
- **ASSUMPTION-003**: Residual value is set at acquisition and rarely changes
- **ASSUMPTION-004**: Asset transfers within the company do not affect depreciation
- **ASSUMPTION-005**: Physical inventory is performed at least annually

## 8. Related Specifications / Further Reading

- [System Architecture Specification](../spec/architecture-nexus-erp.md)
- [Accounting Module Planning](../ACCOUNTING_MODULE_PLANNING.md)
- [General Ledger Implementation Plan](./feature-general-ledger-1.md)
- [IAS 16 Property, Plant and Equipment](https://www.ifrs.org/issued-standards/list-of-standards/ias-16-property-plant-and-equipment/)
- [IAS 36 Impairment of Assets](https://www.ifrs.org/issued-standards/list-of-standards/ias-36-impairment-of-assets/)
