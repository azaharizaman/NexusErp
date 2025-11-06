# Architectural Decisions

## 2025-11-06 AP Foundation Phase 2 - Payment Voucher Model
- **Implemented PaymentVoucher model** for supplier payment management with allocation tracking
- **Models Created:**
  - `PaymentVoucher` - Supplier payment vouchers with status workflow, allocation tracking, and GL integration
  - `PaymentVoucherAllocation` - Links payment vouchers to supplier invoices for payment tracking
- **PaymentVoucher Features:**
  - **Serial Numbering:** PV-YYYY-XXXX pattern with yearly reset (configured in config/serial-pattern.php)
  - **Status Workflow:** draft → approved → paid → cancelled (using Spatie ModelStatus)
  - **Payment Methods:** cash, bank_transfer, credit_card, debit_card, cheque, online, other (matching PaymentReceipt)
  - **Payment References:** reference_number, bank_name, bank_account_number, cheque_number, cheque_date, transaction_id
  - **Allocation Tracking:** allocated_amount and unallocated_amount (decimal 20,4) for tracking payments to invoices
  - **Multi-currency Support:** Currency and exchange_rate (decimal 20,6) tracking
  - **Hold Flag:** is_on_hold boolean for temporary payment holds
  - **GL Integration:** journal_entry_id, is_posted_to_gl, posted_to_gl_at fields for accounting integration
  - **Audit Trail:** created_by, updated_by, requested_by, approved_by, approved_at, paid_by, paid_at, voided_by, voided_at
  - **Business Methods:**
    - `allocateToInvoice()` - Allocates payment to a supplier invoice with validation
    - `isFullyAllocated()` - Checks if payment is fully allocated using bccomp with 4 decimal precision
    - `recalculateAllocations()` - Recalculates total allocated amount from allocation records
    - `canApprove()`, `canPay()`, `canVoid()` - Status transition guards
  - **Relationships:** company, supplier, supplierInvoice, currency, journalEntry, allocations, requester, approver, payer, voider, creator, updater
  - **Scopes:** draft, submitted, approved, paid, voided (using Spatie ModelStatus scopes)
- **PaymentVoucherAllocation Features:**
  - Links payment vouchers to supplier invoices
  - Tracks allocated_amount per invoice (decimal 20,4 for consistency)
  - Audit fields: created_by, updated_by with timestamps
  - **Relationships:** paymentVoucher, supplierInvoice, creator, updater
  - **Scopes:** forPayment, forInvoice
- **Design Decisions:**
  - Used decimal(20,4) for all amount fields to match PaymentReceipt precision
  - Used decimal(20,6) for exchange_rate for better currency precision
  - Temporarily removed foreign key constraint for supplier_invoice_id as supplier_invoices table doesn't exist yet
  - Added fallback in allocateToInvoice() method for missing recordPayment() on SupplierInvoice model
  - Used consistent payment method enum values across PaymentReceipt and PaymentVoucher
  - Followed same allocation pattern as Accounts Receivable for consistency

## 2025-11-06 Phase 3 - Accounts Receivable Module (AR)
- **Implemented comprehensive Accounts Receivable system** with full GL integration
- **Models Created:**
  - `SalesInvoice` - Customer invoices with status workflow, payment tracking, and GL integration
  - `SalesInvoiceItem` - Invoice line items with tax and discount calculation
  - `PaymentReceipt` - Customer payments with multiple payment methods and allocation tracking
  - `PaymentReceiptAllocation` - Links payments to specific invoices
  - `CustomerCreditNote` - Credit notes for returns, adjustments, and discounts
- **SalesInvoice Features:**
  - **Serial Numbering:** SI-YYYY-XXXX pattern with yearly reset
  - **Status Workflow:** draft → issued → partially_paid → paid → overdue → cancelled
  - **Payment Tracking:** Tracks paid_amount and outstanding_amount in real-time
  - **Multi-currency Support:** Currency and exchange rate tracking
  - **Tax Calculation:** Automatic tax calculation from line items
  - **Discount Support:** Invoice-level and line-level discounts
  - **Addresses:** Billing and shipping address storage
  - **GL Integration:** journal_entry_id, is_posted_to_gl, posted_to_gl_at fields
  - **Audit Trail:** Created by, updated by, issued by, cancelled by with timestamps
  - **Business Methods:**
    - `calculateTotals()` - Recalculates subtotal, tax, total from line items
    - `isFullyPaid()` - Checks if invoice is fully paid
    - `isOverdue()` - Checks if invoice is overdue based on due date
    - `updatePaymentStatus()` - Updates status based on paid amount
    - `recordPayment()` - Records a payment and updates outstanding balance
  - **Relationships:** company, customer, fiscalYear, accountingPeriod, currency, items, journalEntry, paymentAllocations, creditNotes
  - **Scopes:** draft, issued, partiallyPaid, paid, overdue, unpaid, postedToGl, forCustomer, forCompany
- **SalesInvoiceItem Features:**
  - Sortable behavior for line ordering
  - Item details: code, description, specifications
  - Quantity with UOM support
  - Unit price with 4 decimal precision
  - Discount: percentage or amount
  - Tax rate and tax amount calculation
  - Revenue account linkage for GL posting
  - **Business Methods:**
    - `calculateLineTotal()` - Calculates line total after discount
    - `calculateTaxAmount()` - Calculates tax on line total
    - `calculateAmounts()` - Calculates both line total and tax
    - `getTotalWithTaxAttribute()` - Returns line total including tax
  - **Relationships:** salesInvoice, uom, revenueAccount
- **PaymentReceipt Features:**
  - **Serial Numbering:** PR-YYYY-XXXX pattern with yearly reset
  - **Status Workflow:** draft → cleared → bounced → cancelled
  - **Payment Methods:** cash, bank_transfer, credit_card, debit_card, cheque, online, other
  - **Payment References:** reference_number, bank details, cheque details, transaction_id
  - **Allocation Tracking:** allocated_amount and unallocated_amount for advance payments
  - **Multi-currency Support:** Currency and exchange rate tracking
  - **GL Integration:** journal_entry_id, is_posted_to_gl, posted_to_gl_at fields
  - **Business Methods:**
    - `allocateToInvoice()` - Allocates payment to specific invoice with validation
    - `isFullyAllocated()` - Checks if entire payment is allocated
    - `recalculateAllocations()` - Recalculates allocated and unallocated amounts
  - **Relationships:** company, customer, currency, journalEntry, allocations
  - **Scopes:** draft, cleared, bounced, unallocated, postedToGl, forCustomer, forCompany, byPaymentMethod
- **PaymentReceiptAllocation Features:**
  - Links payment_receipt_id to sales_invoice_id
  - Tracks allocated_amount per invoice
  - Unique constraint prevents duplicate allocations
  - **Scopes:** forPayment, forInvoice
- **CustomerCreditNote Features:**
  - **Serial Numbering:** CN-YYYY-XXXX pattern with yearly reset
  - **Status Workflow:** draft → issued → applied → cancelled
  - **Reason Types:** return, price_adjustment, discount, error_correction, service_issue, other
  - **Invoice Linkage:** Optional link to originating sales invoice
  - **Multi-currency Support:** Currency and exchange rate tracking
  - **GL Integration:** journal_entry_id, is_posted_to_gl, posted_to_gl_at fields
  - **Approval Tracking:** approved_by, approved_at fields
  - **Business Methods:**
    - `applyToInvoice()` - Applies credit note to linked invoice, reducing outstanding balance
    - `canBeApplied()` - Validates if credit note can be applied
  - **Relationships:** company, customer, salesInvoice, fiscalYear, accountingPeriod, currency, journalEntry
  - **Scopes:** draft, issued, applied, postedToGl, forCustomer, forCompany, forInvoice, byReason
- **Actions Implemented:**
  - `PostSalesInvoice` - Creates GL entry: Debit AR, Credit Revenue (per line), Credit Tax Payable
  - `AllocatePaymentToInvoices` - Allocates payment to invoices with comprehensive validation
    - Manual allocation with array of invoice_id → amount pairs
    - Automatic allocation (FIFO) to oldest unpaid invoices
    - Updates both payment and invoice records atomically
  - `PostPaymentReceipt` - Creates GL entry: Debit Cash/Bank, Credit AR
  - `PostCreditNote` - Creates GL entry: Debit Sales Returns, Credit AR
- **GL Posting Rules:**
  - **Sales Invoice Posting:**
    - Debit: Accounts Receivable (1200) - Total invoice amount
    - Credit: Revenue Accounts (per line item) - Line totals
    - Credit: Tax Payable (2130) - Total tax amount
  - **Payment Receipt Posting:**
    - Debit: Cash/Bank Account (1100) - Payment amount
    - Credit: Accounts Receivable (1200) - Payment amount
  - **Credit Note Posting:**
    - Debit: Sales Returns/Allowances (4100) - Credit note amount
    - Credit: Accounts Receivable (1200) - Credit note amount
- **Filament Resources:**
  - `SalesInvoiceResource` - Full CRUD with:
    - Line items Repeater with real-time calculation
    - Automatic due date calculation from credit days
    - Post to GL action with validation
    - Status badge coloring
    - Filters: status, customer, date range, posted to GL, trashed
    - 4 page files: List, Create, Edit, View
- **Database Design:**
  - `sales_invoices` table: invoice_number (unique), customer details, fiscal period, dates, currency, amounts, status, GL integration, addresses, audit fields
  - `sales_invoice_items` table: item details, quantity, pricing, discounts, tax, revenue_account_id, sort_order
  - `payment_receipts` table: receipt_number (unique), customer, payment details, payment method, currency, amounts, GL integration, status, audit fields
  - `payment_receipt_allocations` table: payment-to-invoice linkage with allocated_amount, unique constraint
  - `customer_credit_notes` table: credit_note_number (unique), customer, invoice linkage, reason, currency, amount, GL integration, approval, audit fields
  - Comprehensive indexes on company+status, customer+status/date, GL posting flags
- **Serial Patterns Added:**
  - salesinvoice: SI-{year}-{number}, start: 1, digits: 4, reset: yearly
  - paymentreceipt: PR-{year}-{number}, start: 1, digits: 4, reset: yearly
  - customercreditnote: CN-{year}-{number}, start: 1, digits: 4, reset: yearly
- **Integration Points:**
  - Ready for Sales Order integration (sales_order_id field in SalesInvoice)
  - Ready for Customer Aging Report (using invoice dates and outstanding amounts)
  - Ready for AR Dashboard Widgets (outstanding by customer, aging buckets)
  - Ready for Credit Limit checking (customer balance calculation)
  - Links to BackOffice package: BusinessPartner (customers), Currency, UnitOfMeasure
- **Payment Allocation Logic:**
  - Manual allocation: User specifies which invoices to apply payment to
  - Automatic allocation: FIFO approach (oldest invoices first)
  - Validations: Same customer, not fully paid, not cancelled, amount limits
  - Updates both payment (allocated_amount) and invoice (paid_amount, outstanding_amount)
  - Atomic transaction ensures data consistency
- **Status Workflows:**
  - **Invoice:** draft → issued (manual) → partially_paid (auto) → paid (auto) → overdue (auto)
  - **Payment:** draft → cleared (manual) → bounced (manual if check bounces)
  - **Credit Note:** draft → issued (manual) → applied (auto when applied to invoice)
- **Future Considerations:**
  - Customer credit limit checking before invoice issuance
  - AR Aging Report (0-30, 31-60, 61-90, 90+ days buckets)
  - Customer statement generation
  - Automatic overdue notification emails
  - Payment reminders based on due date
  - Customer balance widget for dashboard
  - Integration with Sales Order module (generate invoice from SO)
  - Recurring invoice templates (similar to recurring journal entries)
  - Bulk payment allocation from Excel/CSV
  - Customer portal for viewing invoices and making payments

## 2025-11-06 Filament v4.2 Compatibility Fixes
- **Fixed SalesInvoiceResource for Filament v4.2 Schema-based forms**
- **Key Changes Made:**
  - Updated form method signature from `public static function form(Form $form): Form` to `public static function form(Schema $schema): Schema`
  - Changed form implementation from `Form::make()->schema([...])` to `$schema->components([...])`
  - Updated component usage from `Forms\Components\Section::make()` to `Components\Section::make()`
  - Fixed import statements to use `Filament\Forms\Components` and `Filament\Tables`
  - Corrected currency model reference from `\AzahariZaman\Backoffice\Models\Currency` to `\App\Models\Currency`
  - Fixed number_format type casting issues by casting decimal fields to float
- **Filament v4.2 Patterns Established:**
  - Use `Schema $schema` parameter in form methods
  - Use `$schema->components([...])` instead of `Form::make()->schema([...])`
  - Import components as `use Filament\Forms\Components;` and use `Components\ClassName::make()`
  - Import tables as `use Filament\Tables;` and use `Tables\Actions\ActionName::make()`
- **Validation:** PHP syntax check passed, all compile errors resolved

## 2025-11-05 Phase 2 - General Ledger Management (Journal Entries & GL Posting)
- **Implemented core double-entry bookkeeping** with comprehensive journal entry system
- **Models Created:**
  - `JournalEntry` - Main journal entry model with serial numbering (JE-YYYY-XXXX), status workflow, and full audit trail
  - `JournalEntryLine` - Individual debit/credit lines with account, cost center, and dimensional tracking
  - `RecurringJournalTemplate` - Templates for auto-generating recurring journal entries
- **JournalEntry Features:**
  - **Serial Numbering:** JE-YYYY-XXXX pattern with yearly reset (added to `config/serial-pattern.php`)
  - **Entry Types:** manual, automatic, opening, closing, adjusting, reversing, reclassification, intercompany
  - **Status Workflow:** draft → submitted → posted → cancelled
  - **Reversal Support:** Full reversal functionality with automatic line swapping (debit ↔ credit)
  - **Inter-company Support:** Reciprocal entry tracking for inter-company transactions
  - **Source Tracking:** Polymorphic relationship to source documents (e.g., PurchaseOrder, SalesInvoice)
  - **Balance Validation:** Built-in `isBalanced()` method validates debits = credits
  - **Posting Method:** `post()` method updates account balances and prevents duplicate posting
  - **Multi-currency:** Support for foreign currency transactions with exchange rate tracking
- **JournalEntryLine Features:**
  - Debit and credit amounts with 4 decimal precision
  - Foreign currency support with separate foreign debit/credit fields
  - Dimensional analytics: cost center, department (future), project (future)
  - Sortable behavior for maintaining line order
  - Helper methods: `isDebit()`, `isCredit()`, `getNetAmountAttribute()`
- **RecurringJournalTemplate Features:**
  - Frequency options: daily, weekly, biweekly, monthly, quarterly, half-yearly, yearly
  - Occurrence tracking: max occurrences and actual count
  - Start/end date management with next generation date calculation
  - JSON template lines for storing entry structure
  - `shouldGenerate()` validation method
  - `generate()` method creates journal entries from template
  - `calculateNextGenerationDate()` for automatic scheduling
- **Actions Implemented:**
  - `PostJournalEntry` - Posts JE to GL, validates balance, updates account balances, prevents duplicate posting
  - `ReverseJournalEntry` - Creates reversal entry with swapped debits/credits, links to original
  - `GenerateRecurringJournalEntries` - Batch generates JEs from templates, with dry-run and post-immediately options
- **GL Posting Engine:**
  - Atomic transaction-based posting to ensure data integrity
  - Account balance updates respect account type (Asset/Expense increase with debits, Liability/Equity/Income increase with credits)
  - Validation of accounting period status (must be open)
  - Comprehensive error handling with specific exception types
  - Full audit trail with posting user and timestamp
- **Filament Resources:**
  - `JournalEntryResource` - Full CRUD with repeater for lines, real-time balance calculation, status badges
  - Post action with confirmation
  - Reverse action for posted entries
  - Filters: status, entry type, date range, trashed
  - 4 page files: List, Create, View, Edit
- **Database Design:**
  - `journal_entries` table: comprehensive fields for all entry types, audit fields, reversal tracking, inter-company tracking
  - `journal_entry_lines` table: debit/credit amounts, dimensional tracking, currency support
  - `recurring_journal_templates` table: template configuration, occurrence tracking, JSON lines storage
  - Proper indexes on company_id, status, fiscal_year_id, accounting_period_id, entry_date
- **Integration Points:**
  - Ready for auto-generation from Purchase Module (supplier invoices, payment vouchers)
  - Ready for auto-generation from future Sales Module (customer invoices, receipts)
  - Ready for auto-generation from future Inventory Module (COGS, stock adjustments)
- **Future Considerations:**
  - Scheduled job for auto-generating recurring entries (use `GenerateRecurringJournalEntries` action)
  - Email notifications for posted/reversed entries
  - Excel/CSV import for bulk journal entry upload
  - GL account reconciliation functionality
  - Audit report showing all postings by user/date

## 2025-11-05 Phase 1 - Accounting Module Foundation
- **Created new Accounting Panel** as a separate Filament panel following the same architecture as Purchase Module
- **Panel Configuration:**
  - Panel ID: `accounting`
  - Panel Path: `/accounting`
  - Brand Name: "NexusERP - Accounting Module"
  - Primary Color: Green (to differentiate from Purchase Module's Amber)
  - 14 navigation groups defined for comprehensive accounting functionality
  - User menu includes shortcuts to Nexus and Purchase Module panels
- **Navigation Groups Structure:**
  1. Chart of Accounts & Setup
  2. General Ledger
  3. Accounts Receivable
  4. Accounts Payable
  5. Banking & Cash
  6. Financial Reports
  7. Budgeting & Planning
  8. Fixed Assets
  9. Multi-Currency
  10. Consolidation
  11. Dimensions & Analytics
  12. Tax Management
  13. Audit & Compliance
  14. Administration
- **Phase 1 Models Created (Chart of Accounts & Setup):**
  - `AccountGroup` - Hierarchical grouping of accounts with sortable behavior
  - `Account` - Chart of Accounts with full double-entry support, hierarchical structure, and balance tracking
  - `FiscalYear` - Fiscal year management with status workflow (draft → active → closed)
  - `AccountingPeriod` - Monthly/quarterly/yearly periods with open/closed/locked status
  - `CostCenter` - Hierarchical cost center structure for departmental accounting
- **Account Model Features:**
  - Comprehensive account types: Asset, Liability, Equity, Income, Expense
  - Detailed sub-types for each account type (e.g., Current Asset, Fixed Asset, etc.)
  - Hierarchical account structure with parent-child relationships
  - Account groups for better organization
  - Support for group accounts (header accounts) vs ledger accounts
  - Control account designation for AR/AP
  - Manual entry permission control
  - Opening and current balance tracking
  - Balance type (Debit/Credit) for proper double-entry bookkeeping
  - Multi-currency support with foreign currency accounts
  - Company-specific accounts for multi-company setups
  - Sortable behavior using Spatie EloquentSortable
  - Soft deletes and full audit trail (created_by, updated_by)
  - Helper method `updateBalance()` for transaction posting
  - Scopes for filtering by type, sub-type, active status, company
  - `getFullPathAttribute()` for hierarchical display (e.g., "Assets > Current Assets > Cash")
- **AccountGroup Model Features:**
  - Hierarchical grouping (parent-child relationships)
  - Type-based organization (Asset, Liability, Equity, Income, Expense)
  - Sortable behavior for custom ordering
  - Active/inactive status
  - Links to accounts within the group
- **FiscalYear Model Features:**
  - Company-specific fiscal years
  - Start and end date management
  - Status workflow integration via Spatie ModelStatus (draft, active, closed)
  - Default fiscal year designation
  - Locking mechanism to prevent changes to closed years
  - Automatic relationship with accounting periods
  - Closure tracking (closed_on, closed_by)
  - Unique code per company
- **AccountingPeriod Model Features:**
  - Belongs to fiscal year
  - Support for monthly, quarterly, half-yearly, yearly periods
  - Period numbering (1-12 for monthly, 1-4 for quarterly)
  - Status management (open, closed, locked)
  - Adjusting period flag for year-end adjustments
  - Closure tracking
  - `isCurrent()` helper method to check if today falls within period
  - Scopes for filtering by status and period type
- **CostCenter Model Features:**
  - Hierarchical structure (parent-child relationships)
  - Company-specific cost centers
  - Prepared for future integration with Department and Project models (relationships commented)
  - Group vs leaf cost center designation
  - Sortable behavior for custom ordering
  - Active/inactive status
  - `getFullPathAttribute()` for hierarchical display
- **Database Schema Design:**
  - All tables include soft deletes for data integrity
  - Comprehensive audit fields (created_by, updated_by) on all tables
  - Foreign key constraints with proper cascade/set null rules
  - Strategic indexes on frequently queried columns
  - Multi-company support baked into schema design
  - Unique constraints where appropriate (account_code, fiscal year code per company)
- **Integration Points:**
  - Currency and ExchangeRate models reused from Purchase Module
  - Company model reused from backoffice package
  - User model for audit trail tracking
  - TaxRule model will be shared with Purchase Module for tax configurations
- **Future Considerations (Marked in Code):**
  - Department and Project relationships prepared but commented out in CostCenter and Account models
  - Foreign key constraints deferred for department_id and project_id until those models exist
  - Journal Entry model will use these accounts for double-entry bookkeeping
  - General Ledger posting engine will update account current_balance
- **Filament Resource (Partial Implementation):**
  - Created AccountResource as example implementation
  - Form includes dynamic sub-type options based on account type selection
  - Comprehensive form sections: Account Information, Properties, Balance Information
  - Table with filterable columns, search, and soft delete support
  - View, Create, Edit, List pages scaffolded
  - Note: Full Filament v4 compatibility requires database connection for generation
- **Panel Provider Registration:**
  - `AccountingPanelProvider` registered in `bootstrap/providers.php`
  - Panel configured with resource auto-discovery from `app/Filament/Accounting/Resources`
  - Widget and page auto-discovery configured
  - Standard middleware stack applied
  - Authentication middleware enforced
- **Architectural Patterns Maintained:**
  - All models use HasFactory for testing support
  - SoftDeletes trait on all models for data preservation
  - Spatie EloquentSortable for user-controlled ordering where applicable
  - Spatie ModelStatus for workflow management where applicable
  - Consistent audit field naming (created_by, updated_by)
  - BelongsTo/HasMany relationships for hierarchies
  - Eloquent scopes for common queries
  - Attribute accessors for computed properties
- **Deferred Items:**
  - Full Filament resource generation requires database connection (database was not available)
  - Resources for AccountGroup, FiscalYear, AccountingPeriod, and CostCenter to be generated similarly
  - Business logic Actions for account management (CreateAccount, UpdateAccountBalance, CloseAccountingPeriod, etc.)
  - Seeder for standard chart of accounts templates by industry
  - Foreign key constraints to be added for department_id and project_id when those models exist

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

## 2025-11-06 GitHub Copilot Collection Integration
- **Enhanced Development Workflow** with comprehensive GitHub Copilot collection integration
- **Project Planning & Management Collection** - Downloaded and installed all 17 relevant prompts from the "Project Planning & Management" collection
- **Local Prompt Repository** - Created `.github/prompts/` directory structure for organized prompt storage and version control
- **Specification Document Creation Workflow** - Installed prompts for automated generation of:
  - Implementation plans (`create-implementation-plan.prompt.md`, `update-implementation-plan.prompt.md`)
  - GitHub issues from specifications (`create-github-issues-feature-from-specification.prompt.md`, `create-github-issues-feature-from-implementation-plan.prompt.md`)
  - GitHub workflow automation (`create-github-action-workflow-specification.prompt.md`, `gen-specs-as-issues.prompt.md`)
  - LLMs.txt file management (`create-llms.prompt.md`, `update-llms.prompt.md`)
  - Feature breakdown and planning (`breakdown-feature-implementation.prompt.md`, `breakdown-plan.prompt.md`)
  - Test planning and quality assurance (`breakdown-test.prompt.md`)
  - Specification management (`create-specification.prompt.md`, `update-specification.prompt.md`)
  - README generation (`readme-blueprint-generator.prompt.md`)
  - AGENTS.md creation (`create-agentsmd.prompt.md`)
- **Workflow Enhancement Benefits:**
  - **Automated Specification Writing** - Consistent, comprehensive specification documents
  - **GitHub Integration** - Direct creation of issues, PRs, and workflows from specifications
  - **Quality Assurance Planning** - ISTQB and ISO 25010 compliant test strategies
  - **Documentation Automation** - README and AGENTS.md generation for new projects
  - **Project Planning** - Structured implementation plans and feature breakdowns
- **Version Control Strategy** - All prompts committed to repository for team availability and version tracking
- **Future Extensibility** - Framework in place for adding additional Copilot collections as needed