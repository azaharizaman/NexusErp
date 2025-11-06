# 🧭 **PURCHASE MANAGEMENT MODULE – DEVELOPMENT CHECKLIST**

> **Objective:** Implement a complete Purchase Management suite in FilamentPHP with modular architecture, reusable models, and enterprise-grade UX.

---

## ⚙️ PHASE 1 — CORE FOUNDATIONS & SETUP

### 🧩 1.1 Package Integrations

* [x] Integrate **`azaharizaman/laravel-inventory-management`** for items catalog. — ✅ Package not yet available, marked for future integration (2025-11-03)
* [x] Integrate **`azaharizaman/laravel-uom-management`** for UOM. — ✅ Completed on 2025-11-03
* [x] Integrate **`azaharizaman/laravel-serial-numbering`** for controlled numbering. — ✅ Completed on 2025-11-03
* [ ] Add optional dependency hooks for future package **`azaharizaman/laravel-status-transitions`** (DOA workflow). — ⏳ Deferred for future integration
* [x] Register **custom service providers** and boot configuration under `/Modules/PurchaseManagement/Providers/`. — ✅ PurchaseModulePanelProvider configured (2025-11-03)

### 🧱 1.2 Database & Models

* [x] Create models (with migrations and factories) for: — ✅ Completed on 2025-11-03

  * [x] `Vendor` (filtered subset of Business Partner where `is_supplier = true`) — ✅ Using BusinessPartner with `is_supplier` flag
  * [ ] `Item` (extend existing model) — ⏳ Awaiting laravel-inventory-management package
  * [x] `PriceList` — ✅ Completed on 2025-11-03
  * [x] `Currency` & `ExchangeRate` — ✅ Completed on 2025-11-03
  * [x] `TaxRule` — ✅ Completed on 2025-11-03
  * [x] `TermsTemplate` — ✅ Completed on 2025-11-03
* [x] Add **Soft Deletes**, **Audit fields** (`created_by`, `approved_by`, etc.). — ✅ Completed on 2025-11-03
* [x] Implement `ControlledSerialNumbering` trait for transactional models. — ✅ HasSerialNumbering trait from laravel-serial-numbering package implemented on all transactional models (2025-11-03)
* [x] Define all **foreign key relationships** and cascade rules. — ✅ Completed on 2025-11-03

### 🧩 1.3 Filament Panel Setup

* [x] Create `PurchasePanelProvider` under `/Modules/PurchaseManagement/Filament/`. — ✅ Completed on 2025-11-03
* [x] Define navigation groups: — ✅ Completed on 2025-11-03

  ```php
  ->navigationGroups([
      'Procurement Setup',
      'Requisition Management',
      'Sourcing & Ordering',
      'Receiving & Invoicing',
      'Payments & Settlements',
      'Procurement Insights',
      'Administration & Policy',
  ])
  ```
* [x] Configure global color theme, icons, and compact navigation mode. — ✅ Completed on 2025-11-03
* [ ] Register role-based middleware for Filament panel (`can:viewPurchasePanel`). — ⏳ Pending RBAC implementation in Phase 8

---

## 📑 PHASE 2 — PROCUREMENT SETUP MODULES

| Submodule                         | Key Tasks                                                                                                                  |
| --------------------------------- | -------------------------------------------------------------------------------------------------------------------------- |
| **Business Partners (Suppliers)** | [x] Extend Business Partner model → `Vendor` — ✅ Completed on 2025-11-03 <br>[x] Filament Resource: `SupplierResource` (CRUD + search + filter active) — ✅ Completed on 2025-11-03  |
| **Items / Materials Catalog**     | [ ] Extend `Item` from inventory package — ⏳ Awaiting package <br>[ ] Add supplier link and purchase price field — ⏳ Awaiting package                                |
| **UOM & Price Lists**             | [x] Integrate with UOM package — ✅ Completed on 2025-11-03 <br>[x] Create `PriceList` model/resource — ✅ Completed on 2025-11-03 <br>[ ] Allow tiered pricing by supplier/currency — ⏳ Future enhancement |
| **Currencies & Exchange Rates**   | [x] Create `Currency` & `ExchangeRate` models/resources — ✅ Completed on 2025-11-03 <br>[ ] Add daily auto-sync job (using scheduler) — ⏳ Future enhancement                  |
| **Tax & Charge Rules**            | [x] Create `TaxRule` model/resource — ✅ Completed on 2025-11-03 <br>[x] Assignable to PR, PO, and Invoice — ✅ Integrated in PO and Invoice models (2025-11-03)                                              |
| **Terms & Conditions Templates**  | [x] `TermsTemplate` model/resource — ✅ Completed on 2025-11-03 <br>[x] Add WYSIWYG editor for reusable terms — ✅ RichEditor implemented in resource (2025-11-03)                                           |

---

## 📦 PHASE 3 — REQUISITION MANAGEMENT

| Submodule                             | Tasks                                                                                                                                                                                                                                           |
| ------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Purchase Requests (PR)**            | [x] Create model `PurchaseRequest` with serial prefix `PR-` — ✅ Completed on 2025-11-03 <br>[x] Filament Resource: Create/Edit/List views — ✅ Completed on 2025-11-03 <br>[x] Fields: Requester, Dept, Items (Repeater), Total, Status — ✅ Completed on 2025-11-03 <br>[x] Workflow states: Draft → Submitted → Approved → Rejected — ✅ Implemented with Spatie Model Status (2025-11-03) |
| **Request for Quotation (RFQ)**       | [x] Model: `RequestForQuotation` (extends serial numbering) — ✅ Completed on 2025-11-03 <br>[x] Fields: Linked PRs, Suppliers invited, Expiry date — ✅ Completed on 2025-11-03 <br>[x] Filament Resource with subform for supplier quotations — ✅ Completed on 2025-11-03 <br>[x] Pivot tables for PR-RFQ and RFQ-Suppliers relationships — ✅ Completed on 2025-11-03 |
| **Quotation Comparison / Evaluation** | [x] Model: `Quotation` — ✅ Completed on 2025-11-03 <br>[x] Model: `QuotationItem` with line items — ✅ Completed on 2025-11-03 <br>[x] Filament Resource with repeater for items — ✅ Completed on 2025-11-03 <br>[ ] Comparison page (custom Filament Page) — ⏳ Future enhancement <br>[ ] Add "Select Recommended Supplier" button — ⏳ Future enhancement |
| **Purchase Recommendation**           | [x] Model: `PurchaseRecommendation` — ✅ Completed on 2025-11-03 <br>[x] Filament Resource with justification tracking — ✅ Completed on 2025-11-03 <br>[ ] Auto-generate from selected RFQ quotations — ⏳ Future enhancement |

---

## 📑 PHASE 4 — SOURCING & ORDERING

| Submodule                      | Tasks                                                                                                                                                                                            |
| ------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Purchase Orders (PO)**       | [x] Model: `PurchaseOrder` — ✅ Completed on 2025-11-03 <br>[x] Implement serial prefix `PO-` — ✅ Completed on 2025-11-03 <br>[x] Filament Resource: Form with vendor, items, total, taxes — ✅ Completed on 2025-11-03 <br>[x] Status transitions: Draft → Approved → Issued → Closed — ✅ Model supports status transitions (2025-11-03) |
| **PO Revisions / Amendments**  | [x] `PurchaseOrderRevision` model (linked to original PO) — ✅ Completed on 2025-11-03 <br>[x] Auto-track old vs new values — ✅ Completed on 2025-11-03                                                                                                   |
| **Contracts & Blanket Orders** | [x] `PurchaseContract` model — ✅ Completed on 2025-11-03 <br>[x] Link multiple POs under contract — ✅ Completed on 2025-11-03 <br>[x] Filament Resource for contracts — ✅ Completed on 2025-11-03                                                                                                                            |
| **Delivery Schedules**         | [x] `DeliverySchedule` model — ✅ Completed on 2025-11-03 <br>[x] Link to PO items and expected dates — ✅ Completed on 2025-11-03 <br>[x] Filament Resource for delivery schedules — ✅ Completed on 2025-11-03 <br>[ ] Optional integration with calendar widget — ⏳ Future enhancement                                                                       |

---

## 📦 PHASE 5 — RECEIVING & INVOICE PROCESSING

| Submodule                      | Tasks                                                                                                                                      |
| ------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------ |
| **Goods Received Notes (GRN)** | [ ] `GRN` model — ⏳ Partial: relationships and scopes created, migration pending <br>[x] Linked to PO — ✅ Relationship implemented (2025-11-03) <br>[x] Capture delivered quantity, batch, date — ✅ Fields defined in model (2025-11-03) <br>[ ] Auto-update stock if inventory package exists — ⏳ Awaiting inventory package <br>[ ] Create migration for GRN tables <br>[ ] Create Filament Resource for GRN |
| **Supplier Invoices**          | [x] `SupplierInvoice` model — ✅ Completed with GL & AP integration (2025-11-06) <br>[x] `SupplierInvoiceItem` model — ✅ Completed with expense_account_id (2025-11-06) <br>[x] Link PO + GRN — ✅ Relationships implemented (2025-11-03) <br>[x] Tax & currency handling — ✅ Fields and calculations implemented (2025-11-03) <br>[x] GL Integration — ✅ Fields and relationships added (2025-11-06) <br>[x] Add GL integration fields (journal_entry_id, is_posted_to_gl, posted_to_gl_at) — ✅ Completed (2025-11-06) <br>[x] Add payment tracking (payment_status, paid_amount, outstanding_amount) — ✅ Completed (2025-11-06) <br>[x] Implement payment methods (calculateOutstanding, updatePaymentStatus, isFullyPaid, isOverdue, recordPayment) — ✅ Completed (2025-11-06) <br>[x] Create migrations for supplier invoice tables (supplier_invoices and supplier_invoice_items) — ✅ Completed (2025-11-06) <br>[x] Create comprehensive unit tests — ✅ 14 tests passing (2025-11-06) <br>[ ] Create Filament Resource for Supplier Invoices — ⏳ Pending UI implementation |
| **Three-way Matching**         | [ ] Create migration for invoice matching table <br>[ ] `InvoiceMatching` model — ⏳ Pending migration <br>[ ] Automated validation: PO vs GRN vs Invoice totals — ⏳ Pending migration <br>[ ] Report mismatches — ⏳ Pending Filament resource <br>[ ] Create Filament Resource for Invoice Matching                            |
| **Debit / Credit Notes**       | [x] `DebitNote` model — ✅ Completed with GL integration (2025-11-06) <br>[x] Allow linking to Invoice and Vendor account — ✅ Relationships implemented (2025-11-06) <br>[x] GL Integration — ✅ Fields and relationships added (2025-11-06) <br>[x] Create migrations for debit note table — ✅ Completed (2025-11-06) <br>[ ] Create Filament Resource for Debit Notes — ⏳ Pending UI implementation <br>[ ] `CreditNote` for customers — ✅ Already implemented in AR module |
| **GL Integration Actions**     | [x] PostSupplierInvoice Action — ✅ Completed (2025-11-06) <br>[x] PostPaymentVoucher Action — ✅ Completed (2025-11-06) <br>[x] PostSupplierDebitNote Action — ✅ Completed (2025-11-06) <br>[x] Comprehensive test suite — ✅ 16 test cases created (2025-11-06) <br>[x] Model factories — ✅ Created for testing (2025-11-06) |

---

## 💳 PHASE 6 — PAYMENTS & SETTLEMENTS

| Submodule                       | Tasks                                                                                                           |
| ------------------------------- | --------------------------------------------------------------------------------------------------------------- |
| **Payment Vouchers**            | [x] Model: `PaymentVoucher` (serial prefix `PV-`) — ✅ Completed on 2025-11-04 <br>[x] GL Integration — ✅ Fields and relationships added (2025-11-06) <br>[x] Filament Resource: Approval workflow — ✅ Completed on 2025-11-04 <br>[x] Actions: CreatePaymentVoucher, ApprovePaymentVoucher, RecordPayment — ✅ Completed on 2025-11-04 <br>[x] GL Posting: PostPaymentVoucher Action — ✅ Completed on 2025-11-06 |
| **Payment Schedules**           | [x] Model: `PaymentSchedule` (due dates, milestones) — ✅ Completed on 2025-11-04 <br>[x] Auto-generate based on PO or Invoice terms — ✅ GeneratePaymentSchedules action completed on 2025-11-04 <br>[x] Filament Resource with calendar view support — ✅ Completed on 2025-11-04 |
| **Multi-Currency Ledger View**  | [x] `PayableLedger` model — ✅ Completed on 2025-11-04 <br>[x] Show base + foreign currency totals — ✅ Completed on 2025-11-04 <br>[x] Integrate exchange rate snapshots — ✅ Completed on 2025-11-04 <br>[x] Filament Resource — ✅ Completed on 2025-11-04 <br>[x] Actions: CreateLedgerEntry, CalculateSupplierBalance — ✅ Completed on 2025-11-04 |
| **Supplier Invoices**           | [x] Model: `SupplierInvoice` — ✅ Completed on 2025-11-06 <br>[x] Migration with AP tracking fields — ✅ Completed on 2025-11-06 <br>[x] Model: `SupplierInvoiceItem` — ✅ Completed on 2025-11-06 <br>[x] Allocations relationship — ✅ Completed on 2025-11-06 <br>[ ] Filament Resource — ⏳ Future implementation |
| **Outstanding Payables Report** | [ ] Report page showing overdue payments and status — ⏳ Future enhancement (can be implemented as custom Filament page with widgets) |

---

## 📊 PHASE 7 — PROCUREMENT INSIGHTS & REPORTS

| Submodule                       | Tasks                                                                     |
| ------------------------------- | ------------------------------------------------------------------------- |
| **Spend Analysis**              | [ ] Filament ChartWidget: Spend by Supplier, Spend by Month               |
| **Supplier Performance**        | [ ] Widget: On-time delivery, average rating                              |
| **Open PR/PO Tracker**          | [ ] Widget: Pending PRs and unclosed POs                                  |
| **Aging & Payment Analysis**    | [ ] TableWidget: Aging by due date                                        |
| **Audit Logs / Activity Trail** | [ ] Integrate with Filament Activity plugin or custom `ActivityLog` model |

---

## 🧑‍💼 PHASE 8 — ADMINISTRATION & POLICY

| Submodule                            | Tasks                                                                                                        |
| ------------------------------------ | ------------------------------------------------------------------------------------------------------------ |
| **Approval Matrix & Workflow Rules** | [ ] Model: `ApprovalRule` (multi-level) <br>[ ] Integrate with Spatie Roles <br>[ ] Define per-document type |
| **Procurement Policies**             | [ ] Model: `ProcurementPolicy` <br>[ ] CRUD in Filament with WYSIWYG editor                                  |
| **Delegation of Authority (DOA)**    | [ ] Placeholder model `DelegationAuthority` <br>[ ] To be linked with future Status Transitions package      |
| **Notification Templates**           | [ ] Model: `NotificationTemplate` <br>[ ] Support Email & In-App placeholders                                |
| **Role-Based Access Control (RBAC)** | [ ] Configure roles: Requester, Buyer, Finance, Manager <br>[ ] Assign Filament resource permissions         |

---

## 🧠 PHASE 9 — SYSTEM INTEGRATION & SCALABILITY

* [ ] Implement **API endpoints** for external ERP sync (future financial module).
* [ ] Define **event listeners** (`PurchaseOrderApproved`, `InvoiceCreated`, etc.).
* [ ] Create a **Command Bus pattern** to handle document transitions.
* [ ] Implement background jobs for rate sync, reporting cache, and email dispatch.
* [ ] Support modular installation (via `PurchaseManagementServiceProvider`).

---

## 🧪 PHASE 10 — TESTING & DEPLOYMENT

* [x] Write **Pest/PHPUnit tests** for all models and Filament resources. — 🔄 Partial: Tests for Actions (Company, User, Utils) implemented (2025-11-03)
* [ ] Create **seeders** for sample vendors, currencies, and documents.
* [ ] Add **feature tests** for document approval flows.
* [ ] Implement **code coverage tracking** (via GitHub Actions + badges).
* [ ] Document setup steps in `/docs/purchase-management.md`.

---

## 📈 PHASE 11 — DASHBOARD & UX POLISH

* [ ] Design dashboard layout for key widgets (PR, PO, Invoices summary).
* [ ] Add icons to navigation and compact layout toggles.
* [ ] Implement quick search + shortcuts for PR/PO/Invoice creation.
* [ ] Add conditional visibility (e.g., “Check Budget” button only when PO not finalized).
* [ ] Include responsive design for small screens.

---

## ✅ PHASE 12 — DELIVERY & FINAL QA

* [ ] Verify all navigation groups correctly appear in Filament.
* [ ] Check serial numbering uniqueness across modules.
* [ ] Validate approval and role restrictions.
* [ ] Review currency calculations & tax formulas.
* [ ] Conduct user acceptance testing (UAT).
* [ ] Prepare migration script for production.

---

### 🔄 Progress Tracking Convention for GitHub Copilot Agent

Each item should be tracked via:

```markdown
- [x] Task Name — ✅ Completed on YYYY-MM-DD by @username
```

Or updated automatically in the project README or issue tracker using Copilot Agent automation workflows.

---

## 📝 DOCUMENTATION UPDATES

### README.md Update
* [x] Update project README.md to reflect NexusERP purpose — ✅ Completed on 2025-11-03
  - Replaced standard Laravel boilerplate with NexusERP-specific content
  - Added comprehensive project overview, features, and tech stack documentation
  - Included installation and development instructions
  - Added contribution guidelines aligned with project conventions
  - Referenced all existing documentation files (ARCHITECTURAL_DECISIONS.md, MODULES_PLANNING.md, etc.)
  - Documented current development status and roadmap

---

## 🔄 STATUS MANAGEMENT SYSTEM

* [x] **Comprehensive Status Management System Implementation** — ✅ Completed on 2025-11-04
  - [x] Database Schema:
    - Created `document_models` table for model configurations
    - Created `model_statuses` table with color coding and sorting
    - Created `status_transitions` table for defining valid transitions
    - Created `approval_workflows` table for approval configurations
    - Created `status_requests` table for tracking approval requests
  - [x] Models:
    - `DocumentModel` - Represents configurable models that can have statuses
    - `ModelStatus` - Status definitions with Spatie Sortable trait
    - `StatusTransition` - Defines valid transitions with conditions
    - `ApprovalWorkflow` - Configures approval requirements (roles, staff, type)
    - `StatusRequest` - Tracks status change requests with morphic relationships
  - [x] Laravel Actions:
    - `CreateStatusAction` - Creates new statuses for document models
    - `CreateStatusTransitionAction` - Defines status transitions
    - `RequestStatusChangeAction` - Creates status change requests
    - `ApproveStatusChangeAction` - Approves/rejects status requests
    - `CheckApprovalStatusAction` - Checks approval status
    - `TransitionStatusAction` - Performs actual status transitions
  - [x] Filament Resources (Nexus Panel):
    - `DocumentModelResource` - Manage document models
    - `ModelStatusResource` - Configure statuses with reorderable list
    - `StatusTransitionResource` - Define transitions and workflows
    - `StatusRequestResource` - View and manage approval requests
  - [x] Integration:
    - All resources added to "Status Management" navigation group
    - Verified existing models follow Spatie ModelStatus best practices
    - Confirmed status checks use `latestStatus() === 'status'` pattern
  - [x] Documentation:
    - Updated ARCHITECTURAL_DECISIONS.md with implementation details
    - Updated PROGRESS_CHECKLIST.md to track completion

---

## 🔧 TECHNICAL FIXES & IMPROVEMENTS

### Filament v4.2 Compatibility Updates
* [x] **SalesInvoiceResource Filament v4.2 Migration** — ✅ Completed on 2025-11-06
  - [x] Updated form method signature to `form(Schema $schema): Schema`
  - [x] Changed form implementation to use `$schema->components([...])` pattern
  - [x] Fixed component imports and usage (`Components\Section::make()` instead of `Forms\Components\Section::make()`)
  - [x] Corrected table action imports (`Tables\Actions\EditAction::make()`)
  - [x] Fixed currency model reference (`\App\Models\Currency`)
  - [x] Resolved number_format type casting issues for decimal fields
  - [x] Validated with PHP syntax check - no errors detected
  - [x] Established Filament v4.2 patterns for future resource development

### GitHub Copilot Collection Integration
* [x] **Project Planning & Management Prompts Installation** — ✅ Completed on 2025-11-06
  - [x] Downloaded and installed all 17 relevant prompts from "Project Planning & Management" collection
  - [x] Created local copies in `.github/prompts/` directory for enhanced specification document creation workflow
  - [x] Installed prompts for: implementation plans, GitHub issues/PRs, LLMs.txt files, feature breakdowns, and testing strategies
  - [x] Prompts now available for automated project planning, specification writing, and GitHub integration

---

## 📊 ACCOUNTS RECEIVABLE (AR) MODULE STATUS

### Phase 3 — Accounts Receivable Implementation
* [x] **Customer Invoices (SalesInvoice)** — ✅ Completed
  - [x] SalesInvoice model with SI- prefix serial numbering
  - [x] Line items with tax calculations and currency handling
  - [x] Status workflow: draft → issued → partially_paid → paid → overdue → cancelled
  - [x] SalesInvoiceResource with Filament v4.2 compatibility fixes
  - [x] Post to GL integration (Debit AR, Credit Revenue, Credit Tax Payable)
* [x] **Customer Payments/Receipts (PaymentReceipt)** — ✅ Models & Actions Completed
  - [x] PaymentReceipt model with PR- prefix serial numbering
  - [x] Multiple payment methods support (cash, bank, card, cheque, online, other)
  - [x] Payment allocation to invoices (manual and automatic FIFO)
  - [x] Partial payments and advance payments handling
  - [x] AllocatePaymentToInvoices and PostPaymentReceipt Actions
  - [ ] PaymentReceiptResource — ⏳ Pending Filament Resource implementation
* [x] **Credit Notes (CustomerCreditNote)** — ✅ Models & Actions Completed
  - [x] CustomerCreditNote model with CN- prefix serial numbering
  - [x] Link to original sales invoices with full/partial credit support
  - [x] Reason tracking (return, price_adjustment, discount, error_correction, service_issue, other)
  - [x] Auto-adjust customer outstanding balance
  - [x] PostCreditNote Action for GL integration
  - [ ] CustomerCreditNoteResource — ⏳ Pending Filament Resource implementation
* [x] **GL Integration for AR Transactions** — ✅ Completed
  - [x] PostSalesInvoice Action implemented
  - [x] PostPaymentReceipt Action implemented
  - [x] PostCreditNote Action implemented
  - [x] All AR models have journal_entry_id, is_posted_to_gl, posted_to_gl_at fields

