# Architectural Decisions

## 2025-11-04 Status Management System Implementation
- **Implemented comprehensive status management system** based on design document `SPATIE_LARAVEL_MODEL_STATUS_SPECIAL_DESIGN_CHANGE.md`
- Created centralized system for configuring statuses, transitions, and approval workflows across all models
- **New Models Created:**
  - `DocumentModel` - Represents configurable models/documents that can have statuses
  - `ModelStatus` - Stores status definitions with color coding and sorting (uses Spatie Sortable)
  - `StatusTransition` - Defines valid status transitions with optional conditions
  - `ApprovalWorkflow` - Configures approval requirements for transitions (roles, staff, approval type)
  - `StatusRequest` - Tracks status change requests requiring approval
- **Laravel Actions for Status Management:**
  - `CreateStatusAction` - Creates new statuses for document models
  - `CreateStatusTransitionAction` - Defines status transitions with approval conditions
  - `RequestStatusChangeAction` - Creates status change requests and notifies approvers
  - `ApproveStatusChangeAction` - Approves/rejects status change requests
  - `CheckApprovalStatusAction` - Checks current status of approval requests
  - `TransitionStatusAction` - Performs actual status transitions using Spatie ModelStatus
- **Filament Resources in Nexus Panel:**
  - `DocumentModelResource` - Manage document models that can have statuses
  - `ModelStatusResource` - Configure statuses with reorderable list
  - `StatusTransitionResource` - Define transitions and approval workflows
  - `StatusRequestResource` - View and manage approval requests (read-only)
- **Key Features:**
  - Centralized status configuration for all models using HasStatuses trait
  - Flexible approval workflows with single or group approval types
  - Role-based and staff-specific approval assignments
  - Complete audit trail for status change requests
  - Integration with existing Spatie ModelStatus package
  - All resources added to "Status Management" navigation group in Nexus panel
- **Database Schema:**
  - `document_models` - Stores model configurations
  - `model_statuses` - Stores status definitions with colors and sorting
  - `status_transitions` - Defines valid transitions with conditions
  - `approval_workflows` - Configures approval requirements
  - `status_requests` - Tracks approval requests with morphic relationship to any model
- **Existing Models Review:**
  - Verified all models using HasStatuses trait follow best practices
  - All status checks use `latestStatus() === 'status'` pattern as per custom instructions
  - Models properly using Spatie ModelStatus: PaymentVoucher, PaymentSchedule, PurchaseOrder, etc.

## 2025-11-04 Phase 6 - Payments & Settlements Module Complete
- **Implemented Phase 6 of Purchase Management Module** with complete payment processing, scheduling, and payables tracking
- Created three new models with full Filament resources and business logic Actions:
  - `PaymentVoucher` (PV-YYYY-XXXX) - Payment vouchers with approval workflow (draft → submitted → approved → paid → voided)
  - `PaymentSchedule` (PS-YYYY-XXXX) - Payment schedules with due dates, milestones, and automatic tracking
  - `PayableLedger` - Multi-currency payables ledger with exchange rate snapshots and running balance calculation
- **Payment Voucher Features:**
  - Multiple payment methods support (Bank Transfer, Cash, Cheque, Credit Card, Wire Transfer)
  - Links to supplier invoices and payment schedules
  - Complete audit trail with requester, approver, and payer tracking
  - Bank details and transaction tracking (bank name, account number, cheque number, transaction ID)
  - Void capability with reason tracking
  - Status workflow managed via Spatie ModelStatus
- **Payment Schedule Features:**
  - Auto-generation from purchase orders or supplier invoices using GeneratePaymentSchedules action
  - Support for various payment terms (Net 30, Net 60, 50% Advance, etc.)
  - Milestone tracking for progress-based payments
  - Automatic calculation of paid and outstanding amounts
  - Reminder system for upcoming due dates
  - Overdue and upcoming schedule scopes for easy filtering
- **Payable Ledger Features:**
  - Multi-currency support with automatic exchange rate application
  - Debit/credit tracking in both base and foreign currencies
  - Running balance calculation per supplier
  - Exchange rate snapshots for historical accuracy
  - Transaction type tracking (invoice, payment, credit_note, debit_note)
  - Integration with payment vouchers and supplier invoices
- **Business Logic Actions:**
  - `CreatePaymentVoucher` - Creates new payment voucher and sets initial status
  - `ApprovePaymentVoucher` - Approves voucher and updates approval tracking
  - `RecordPayment` - Records payment, updates ledger, schedules, and invoice status in a transaction
  - `GeneratePaymentSchedules` - Auto-generates schedules from PO/Invoice with various payment terms
  - `CreateLedgerEntry` - Creates ledger entries with currency conversion and balance calculation
  - `CalculateSupplierBalance` - Calculates outstanding balance for a supplier as of a specific date
- **Serial Numbering:**
  - Added `PaymentVoucher` (PV-YYYY-XXXX) pattern to `config/serial-pattern.php`
  - Added `PaymentSchedule` (PS-YYYY-XXXX) pattern to `config/serial-pattern.php`
- All models follow established patterns: HasSerialNumbering, HasStatuses, SoftDeletes, audit fields
- Filament Resources added to "Payments & Settlements" navigation group with proper icons and labels

## 2025-11-03 Phase 4 - Purchase Orders and Sourcing Module Complete
- **Implemented Phase 4 of Purchase Management Module** with complete CRUD for Purchase Orders, Contracts, and Delivery Schedules
- Created four new models with full Filament resources:
  - `PurchaseOrder` (PO-YYYY-XXXX) - Main purchase order with line items, status tracking, and approval workflow
  - `PurchaseOrderItem` - Sortable line items with automatic tax and discount calculations
  - `PurchaseContract` (PC-YYYY-XXXX) - Blanket orders and framework agreements with utilization tracking
  - `DeliverySchedule` (DS-YYYY-XXXX) - Delivery schedules linked to PO items with reminder system
  - `PurchaseOrderRevision` - Tracks amendments and changes to POs with old/new value comparison
- **Purchase Order Features:**
  - Real-time calculation of line totals, tax amounts, and discounts using Filament's reactive forms
  - Support for multiple line items using Repeater component with sortable behavior
  - Integration with suppliers, currencies, price lists, and terms templates
  - Status workflow: Draft → Approved → Issued → Closed
  - Links to purchase recommendations and contracts
  - Separate shipping and billing addresses
  - Payment terms, delivery terms, and Incoterms support
  - Rich text editor for terms and conditions
  - Internal notes field for private documentation
- **Purchase Contract Features:**
  - Support for blanket orders, framework agreements, and long-term contracts
  - Contract value tracking with utilized and remaining value calculation
  - Active contract scopes and expiration tracking
  - Links to multiple purchase orders under the contract
- **Delivery Schedule Features:**
  - Scheduled, confirmed, and delivered status tracking
  - Quantity tracking (scheduled, delivered, remaining)
  - Automatic reminder system with configurable days before delivery
  - Tracking number and delivery location support
  - Integration with purchase orders and PO items
- All models follow established patterns: HasSerialNumbering, HasStatuses, SoftDeletes, audit fields

## 2025-11-03 Serial Numbering Implementation - HasSerialNumbering Trait
- **Implemented HasSerialNumbering trait** from `azaharizaman/laravel-serial-numbering` package for thread-safe serial number generation
- Year-based auto-numbering format: RFQ-YYYY-XXXX, PR-YYYY-XXXX, PR-REC-YYYY-XXXX, QT-YYYY-XXXX, PO-YYYY-XXXX, PC-YYYY-XXXX, DS-YYYY-XXXX
- Features:
  - Database-backed sequential numbering with atomic locks to prevent race conditions
  - Audit logging of all serial number generations via `serial_logs` table
  - Yearly reset of sequence numbers (configurable in `config/serial-pattern.php`)
  - Support for serial voiding and tracking
- **All procurement documents** now use controlled numbering:
  - `RequestForQuotation` (RFQ-YYYY-XXXX) - Internal procurement document
  - `PurchaseRequest` (PR-YYYY-XXXX) - Internal requisition document
  - `PurchaseRecommendation` (PR-REC-YYYY-XXXX) - Internal recommendation document
  - `Quotation` (QT-YYYY-XXXX) - Supplier quotation document
  - `PurchaseOrder` (PO-YYYY-XXXX) - Purchase order document
  - `PurchaseContract` (PC-YYYY-XXXX) - Contract document
  - `DeliverySchedule` (DS-YYYY-XXXX) - Delivery schedule document
- Configuration stored in `config/serial-pattern.php` with patterns for each document type

## 2025-11-03 Standard Fields Implementation
- Added standard fields to all transactional models: `requested_by`, `approved_by`, `approved_at`, `remarks`
- All models now include complete audit trail: `created_by`, `updated_by`, `requested_by`, `approved_by` with corresponding User relationships
- Relationships: `requester()`, `approver()`, `creator()`, `updater()` methods added for audit tracking
## 2025-11-03 Project Documentation Update
- Updated README.md from standard Laravel boilerplate to NexusERP-specific documentation to properly communicate project purpose, architecture, and development workflow.
- New README includes comprehensive sections on project purpose, features, tech stack, installation, development workflow, project structure, contribution guidelines, and references to all existing documentation files.
- This ensures new developers and contributors can quickly understand the project's goals, architecture, and how to get started.

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
🎯 Phase 3 - Request for Quotations (COMPLETED - 2025-11-03):

✅ Request for Quotation (RFQ) Implementation:

Models Created:
RequestForQuotation model with:

Auto-generating RFQ number with RFQ- prefix (RFQ-000001, RFQ-000002, etc.)
Fields: company, rfq_date, expiry_date, status, description, terms_and_conditions, currency, notes
Many-to-many relationship with PurchaseRequests via purchase_request_rfq pivot table
Many-to-many relationship with invited suppliers via rfq_suppliers pivot table
One-to-many relationship with Quotations
Soft deletes and audit fields (created_by, updated_by)
Spatie Model Status integration for workflow states (draft, sent, received, evaluated, closed, cancelled)
Scopes: draft(), sent(), received(), evaluated()
Database Tables:

request_for_quotations - main RFQ table
purchase_request_rfq - pivot table linking RFQs to multiple Purchase Requests
rfq_suppliers - tracks invited suppliers per RFQ with status (invited, declined, submitted)
Filament Resource:

RequestForQuotationResource in Purchase Module panel
Navigation group: "Requisition Management"
Full CRUD pages: List, Create, Edit
Form sections: RFQ Details, Purchase Requests (multi-select), Invited Suppliers (multi-select), Additional Information
Table with status badges, linked PRs display, invited suppliers display
Filters for status and soft deletes


✅ Quotation Implementation:

Models Created:
Quotation model with:

Auto-generating quotation number with QT- prefix (QT-000001, QT-000002, etc.)
Fields: rfq, supplier, quotation_date, valid_until, currency, subtotal, tax_amount, total_amount, status, terms_and_conditions, notes, delivery_lead_time_days, payment_terms, is_recommended
Belongs to RequestForQuotation and BusinessPartner (supplier)
One-to-many relationship with QuotationItems
Method: calculateTotals() to sum item totals and taxes
Soft deletes and audit fields
Spatie Model Status integration
Scopes: recommended(), submitted(), accepted()
QuotationItem model with:

Fields: item_description, item_code, quantity, uom, unit_price, line_total, tax_rate, tax_amount, specifications, notes
Spatie Eloquent Sortable for ordering items
Belongs to Quotation
Method: calculateTotals() for line item calculations
Database Tables:

quotations - supplier quotations with pricing and terms
quotation_items - line items with tax calculations
Filament Resource:

QuotationResource in Purchase Module panel
Navigation group: "Requisition Management"
Form with repeater for line items with auto-calculation support
Sections: Quotation Details, Quotation Items (repeater), Totals, Additional Information
Table with recommended flag, status badges, money formatting
Filters for status, recommended flag, and soft deletes


✅ Purchase Recommendation Implementation:

Model Created:
PurchaseRecommendation model with:

Auto-generating recommendation number with PR-REC- prefix
Fields: rfq, recommended_quotation, company, recommendation_date, status, justification, comparison_notes, recommended_total, currency
Belongs to RequestForQuotation and Quotation
Relationships for company, currency, approver
Approval tracking (approved_by, approved_at)
Soft deletes and audit fields
Spatie Model Status integration
Scopes: approved(), pending()
Database Table:

purchase_recommendations - recommendations with justifications
Filament Resource:

PurchaseRecommendationResource in Purchase Module panel
Navigation group: "Requisition Management"
Form sections: Recommendation Details, Justification, Approval Information
Dynamic quotation filtering based on selected RFQ
Table with supplier display, approval tracking, status badges


📁 Files Created for Phase 3:
Models:

RequestForQuotation.php
Quotation.php
QuotationItem.php
PurchaseRecommendation.php
Migrations:

2025_11_03_104208_create_request_for_quotations_table.php (includes pivot tables)
2025_11_03_104231_create_quotations_table.php
2025_11_03_104239_create_quotation_items_table.php
2025_11_03_104239_create_purchase_recommendations_table.php
Filament Resources:

app/Filament/PurchaseModule/Resources/RequestForQuotations/* (Resource, Pages, Schemas, Tables)
app/Filament/PurchaseModule/Resources/Quotations/* (Resource, Pages, Schemas, Tables)
app/Filament/PurchaseModule/Resources/PurchaseRecommendations/* (Resource, Pages, Schemas, Tables)
Factories (created but not yet implemented):

RequestForQuotationFactory.php
QuotationFactory.php
QuotationItemFactory.php
PurchaseRecommendationFactory.php
🔄 Remaining Items for Phase 3:

Custom Filament page for quotation comparison (optional enhancement)
Auto-generation of recommendations from selected quotations (can be implemented as an action)
Factory implementations for testing
The foundation for requisition management is now complete with a fully functional RFQ system that can track quotations from multiple suppliers, compare them, and generate purchase recommendations.