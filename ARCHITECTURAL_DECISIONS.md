# Architectural Decisions

## 2025-11-03 Panel Switching via User Menu
- Added Filament user menu actions in both `nexus` and `purchase-module` panels to generate deep links with `Filament::getPanel()->getUrl()`, providing the recommended panel-switching experience in v4.
- Restored the Nexus panel topbar so the shared user menu (and panel switcher action) remains accessible in the default location.

## 2025-11-03 Panel-Specific Branding
- Applied `brandName()` per panel so the topbar reflects the active module (`NexusERP` vs. `NexusERP - Purchase Module`), keeping navigation context clear for multi-panel users.

## 2025-11-03 Config-Backed Application Settings
- Replaced Spatie settings (classes, seeders, and Filament plugin) with a declarative `config/nexus-settings.php` array to simplify defaults management and unblock seeding in environments without persisted settings.
- Updated helper APIs to read from config so existing consumers continue to function while relying on stateless configuration.
Summary of Completed Work
I've successfully implemented Phase 1 (Core Foundations & Setup) and Phase 2 (Procurement Setup Modules) of the Purchase Management Module as documented in PROGRESS_CHECKLIST.md.

✅ Phase 1 Completed:
1.1 Package Integrations:

✅ Integrated azaharizaman/laravel-uom-management
✅ Integrated azaharizaman/laravel-serial-numbering
ℹ️ laravel-inventory-management package doesn't exist yet - marked for future integration
1.2 Database & Models:
Created complete models with migrations and factories:

✅ Currency model with soft deletes, audit fields, active/base scopes
✅ ExchangeRate model with foreign keys to currencies, effective dates, active/current scopes
✅ PriceList model with supplier linking, currency support, effective date ranges
✅ TaxRule model with percentage/fixed types, compound tax support, tax calculation method
✅ TermsTemplate model with categories, default flag, rich text content support
✅ All models include soft deletes, created_by/updated_by audit fields
✅ Proper foreign key relationships and cascade rules defined
1.3 Filament Panel Setup:

✅ Updated PurchaseModulePanelProvider with navigation groups:
Procurement Setup
Requisition Management
Sourcing & Ordering
Receiving & Invoicing
Payments & Settlements
Procurement Insights
Administration & Policy
✅ Configured theme with Amber primary color
✅ Panel switching between Nexus and Purchase Module enabled
✅ Phase 2 Completed:
Procurement Setup Modules:

✅ Suppliers: Complete CRUD via SupplierResource (reuses BusinessPartner with is_supplier=true filter)
✅ Currency Resource: Full CRUD with generated forms, tables, and view pages
✅ Exchange Rate Resource: Full CRUD with currency relationships
✅ Price List Resource: Full CRUD with supplier and currency linking
✅ Tax Rule Resource: Full CRUD with tax calculation logic
✅ Terms Template Resource: Full CRUD with categorization support
All resources properly assigned to "Procurement Setup" navigation group.

📁 Files Created/Modified:
Models:

Currency.php
ExchangeRate.php
PriceList.php
TaxRule.php
TermsTemplate.php
Migrations:

2025_11_03_084940_create_currencies_table.php
2025_11_03_084947_create_exchange_rates_table.php
2025_11_03_084948_create_price_lists_table.php
2025_11_03_084949_create_tax_rules_table.php
2025_11_03_084949_create_terms_templates_table.php
Filament Resources:

app/Filament/PurchaseModule/Resources/Suppliers/* (fixed type errors)
app/Filament/PurchaseModule/Resources/Currencies/*
app/Filament/PurchaseModule/Resources/ExchangeRates/*
app/Filament/PurchaseModule/Resources/PriceLists/*
app/Filament/PurchaseModule/Resources/TaxRules/*
app/Filament/PurchaseModule/Resources/TermsTemplates/*
Configuration:

Updated PurchaseModulePanelProvider.php with navigation groups
Updated PROGRESS_CHECKLIST.md marking completed items with dates
🎯 Next Steps:
The foundation is now complete for building out the transactional modules in Phases 3-6:

Phase 3: Requisition Management (PR, RFQ, Quotation Comparison)
Phase 4: Sourcing & Ordering (PO, Contracts, Delivery Schedules)
Phase 5: Receiving & Invoice Processing (GRN, Invoices, Three-way Matching)
Phase 6: Payments & Settlements
All migrations ran successfully, and the Purchase Module panel is ready with proper navigation structure and foundational models.


Summary of Phase 3 Progress
I've successfully started Phase 3 (Requisition Management) with the following accomplishments:

✅ Completed:
Purchase Requests (PR) - FULLY IMPLEMENTED:

Models Created:

PurchaseRequest model with:

Auto-generating PR number with PR- prefix
Fields: requester, department, company, request_date, required_date, status, total_amount, currency, purpose, notes, rejection_reason
Audit fields: created_by, updated_by, approved_by, approved_at, rejected_by, rejected_at
Soft deletes enabled
Spatie Model Status integration for workflow states (draft, submitted, approved, rejected)
Relationships: requester, company, currency, items, approver, rejector
Scopes: draft(), submitted(), approved(), rejected()
Method: calculateTotal() to sum item totals
PurchaseRequestItem model with:

Fields: item_description, item_code, quantity, uom_id, estimated_unit_price, estimated_total, specifications, notes
Spatie Eloquent Sortable for ordering items
Relationship to parent PurchaseRequest
Method: calculateTotal() for line item calculations
Migrations Created:

purchase_requests table with all required fields and foreign keys
purchase_request_items table for line items with sort ordering
Filament Resource Generated:

PurchaseRequestResource created in Purchase Module panel
Navigation group: "Requisition Management"
Full CRUD pages: List, Create, Edit
Auto-generated forms and tables
📝 Key Features Implemented:
Serial numbering for PR numbers (PR-000001, PR-000002, etc.)
Complete workflow support with status tracking
Approval/rejection tracking with timestamps and user references
Multi-line item support with sortable items
Currency support for international procurement
Soft deletes for data retention
Audit trail with created_by/updated_by fields
🔄 Database Changes:
Fresh migration run successfully with all Phase 1, 2, and 3 (partial) tables created
Fixed foreign key constraints for business_partners and price_lists
📁 Files Created:
PurchaseRequest.php
PurchaseRequestItem.php
2025_11_03_100540_create_purchase_requests_table.php
PurchaseRequestFactory.php
PurchaseRequestItemFactory.php
app/Filament/PurchaseModule/Resources/PurchaseRequests/* (Resource, Pages, Schemas, Tables)
🎯 Next Steps for Phase 3:
Request for Quotation (RFQ) model and resource
Quotation model with comparison functionality
Purchase Recommendation model
Custom Filament page for quotation comparison
The foundation for requisition management is now in place with a fully functional Purchase Request system that can track requests from creation through approval workflow.