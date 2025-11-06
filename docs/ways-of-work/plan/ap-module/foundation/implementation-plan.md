---
goal: Implement Accounts Payable (AP) Module Foundation
version: 1.0
date_created: 2025-11-06
last_updated: 2025-11-06
owner: Development Team
status: 'Planned'
tags: ["feature", "accounting", "ap", "payable", "supplier"]
---

# Implement Accounts Payable (AP) Module Foundation

![Status: Planned](https://img.shields.io/badge/status-Planned-blue)

## Goal

The Accounts Payable (AP) Module Foundation aims to establish a robust system for managing supplier invoices, payment vouchers, debit notes, and general ledger integration within the NexusErp application. This foundation will enable efficient tracking of supplier payments, automate payment allocation processes, and ensure accurate financial reporting through seamless GL integration. By implementing three-way matching validation and comprehensive approval workflows, the system will reduce payment errors and improve cash flow management. The module will mirror the existing AR module structure while focusing on outbound payments to suppliers, providing a complete payables management solution.

## Requirements

### Functional Requirements
- **REQ-001**: Integrate with Purchase Module's SupplierInvoice model for payment tracking
- **REQ-002**: Implement payment voucher system with AP- prefix serial numbering and batch processing capabilities
- **REQ-003**: Support supplier debit notes with DN- prefix for purchase returns and adjustments
- **REQ-004**: Implement three-way matching (PO-GRN-Invoice) validation for invoice approval
- **REQ-005**: Support payment terms, due date calculation, and payment hold workflows
- **REQ-006**: Enable GL integration with double-entry bookkeeping for all AP transactions
- **REQ-007**: Provide comprehensive audit trails for all payment and approval activities

### Technical Requirements
- **REQ-008**: Must reuse existing SupplierInvoice model from Purchase Module
- **REQ-009**: Must implement HasSerialNumbering trait for transactional models
- **REQ-010**: Must use BCMath for precise financial calculations
- **REQ-011**: Must follow spatie/laravel-model-status for workflow management
- **REQ-012**: Must integrate with existing JournalEntry and Account models

### Integration Requirements
- **REQ-013**: Link to PurchaseOrder model for three-way matching validation
- **REQ-014**: Connect with GRN (Goods Received Note) model when available
- **REQ-015**: Integrate with GL Account model for expense and AP account management
- **REQ-016**: Support currency conversion and exchange rate handling

### Security Requirements
- **REQ-017**: Implement payment approval workflow with role-based authorization
- **REQ-018**: Require specific permissions for payment holds and GL posting
- **REQ-019**: Maintain audit trail of all approvers and posting activities
- **REQ-020**: Ensure data validation and sanitization for all financial inputs

## Technical Considerations

### System Architecture Overview

```mermaid
graph TB
    subgraph "Frontend Layer (Filament Admin Panel)"
        F1[Payment Voucher Resource]
        F2[Supplier Invoice Resource]
        F3[Debit Note Resource]
        F4[AP Dashboard Widgets]
    end

    subgraph "API Layer (Laravel Controllers & Resources)"
        A1[PaymentVoucherController]
        A2[SupplierInvoiceController]
        A3[DebitNoteController]
        A4[AP Reports API]
    end

    subgraph "Business Logic Layer (Laravel Actions)"
        B1[PostSupplierInvoice Action]
        B2[AllocatePaymentToInvoices Action]
        B3[ValidateThreeWayMatch Action]
        B4[ApprovePaymentVoucher Action]
    end

    subgraph "Data Layer (Eloquent Models & Database)"
        D1[PaymentVoucher Model]
        D2[PaymentVoucherAllocation Model]
        D3[SupplierDebitNote Model]
        D4[InvoiceMatching Model]
        D5[JournalEntry Model]
    end

    subgraph "Infrastructure Layer (Laravel Services)"
        I1[Serial Numbering Service]
        I2[Currency Conversion Service]
        I3[GL Posting Service]
        I4[Background Job Queue]
    end

    F1 --> A1
    F2 --> A2
    F3 --> A3
    F4 --> A4

    A1 --> B2
    A2 --> B1
    A3 --> B1
    A4 --> B3

    B1 --> I3
    B2 --> I3
    B3 --> D4
    B4 --> I4

    B1 --> D5
    B2 --> D1
    B3 --> D4
    B4 --> D1

    D1 --> I1
    D3 --> I1
    D2 --> I2
    D5 --> I2
```

**Technology Stack Selection:**
- **Frontend Layer**: FilamentPHP v4.1 for admin panel with Laravel Blade templates
- **API Layer**: Laravel controllers with resource classes for RESTful endpoints
- **Business Logic Layer**: Laravel Actions (lorisleiva/laravel-actions) for granular, testable business logic
- **Data Layer**: Eloquent ORM with MySQL database, Redis for caching
- **Infrastructure Layer**: Laravel services with database transactions and queue system

**Integration Points:**
- Purchase Module: SupplierInvoice model integration
- GL Module: JournalEntry posting and Account model relationships
- Currency Module: Exchange rate handling via ariaieboy/filament-currency
- Status Management: spatie/laravel-model-status for workflow states

**Deployment Architecture:**
- Docker containerization for consistent environments
- Background job processing via Laravel Queue
- Database migrations for schema versioning
- Environment-based configuration management

**Scalability Considerations:**
- Horizontal scaling through load balancers for multiple app instances
- Database read replicas for reporting queries
- Redis caching for frequently accessed financial data
- Queue-based processing for bulk payment operations

### Database Schema Design

```mermaid
erDiagram
    PaymentVoucher ||--o{ PaymentVoucherAllocation : contains
    PaymentVoucherAllocation ||--|| SupplierInvoice : allocates_to
    SupplierInvoice ||--o{ SupplierDebitNote : has_debit_notes
    SupplierInvoice ||--o{ InvoiceMatching : matches_with
    InvoiceMatching ||--o| PurchaseOrder : references
    InvoiceMatching ||--o| GoodsReceivedNote : references
    PaymentVoucher ||--o| JournalEntry : posts_to
    SupplierInvoice ||--o| JournalEntry : posts_to
    SupplierDebitNote ||--o| JournalEntry : posts_to

    PaymentVoucher {
        id BIGINT PK
        serial_number VARCHAR
        company_id BIGINT FK
        supplier_id BIGINT FK
        payment_date DATE
        amount DECIMAL
        currency_id BIGINT FK
        exchange_rate DECIMAL
        payment_method ENUM
        bank_name VARCHAR
        cheque_number VARCHAR
        status ENUM
        is_on_hold BOOLEAN
        approved_by BIGINT FK
        approved_at TIMESTAMP
        journal_entry_id BIGINT FK
        is_posted_to_gl BOOLEAN
        posted_to_gl_at TIMESTAMP
        created_at TIMESTAMP
        updated_at TIMESTAMP
    }

    PaymentVoucherAllocation {
        id BIGINT PK
        payment_voucher_id BIGINT FK
        supplier_invoice_id BIGINT FK
        allocated_amount DECIMAL
        created_at TIMESTAMP
        updated_at TIMESTAMP
    }

    SupplierDebitNote {
        id BIGINT PK
        serial_number VARCHAR
        company_id BIGINT FK
        supplier_id BIGINT FK
        supplier_invoice_id BIGINT FK
        debit_note_number VARCHAR
        debit_note_date DATE
        debit_amount DECIMAL
        currency_id BIGINT FK
        exchange_rate DECIMAL
        reason ENUM
        description TEXT
        notes TEXT
        status ENUM
        journal_entry_id BIGINT FK
        is_posted_to_gl BOOLEAN
        posted_to_gl_at TIMESTAMP
        created_at TIMESTAMP
        updated_at TIMESTAMP
    }

    InvoiceMatching {
        id BIGINT PK
        purchase_order_id BIGINT FK
        grn_id BIGINT FK
        supplier_invoice_id BIGINT FK
        match_status ENUM
        variance_amount DECIMAL
        variance_reason TEXT
        created_at TIMESTAMP
        updated_at TIMESTAMP
    }
```

**Table Specifications:**
- All monetary fields use DECIMAL(15,4) for precision
- Serial numbers use VARCHAR with unique constraints
- Status enums use predefined values with defaults
- Foreign keys include cascade delete where appropriate
- Timestamps include timezone handling

**Indexing Strategy:**
- Composite indexes on (company_id, status) for filtering
- Unique indexes on serial numbers and allocation pairs
- Foreign key indexes for join performance
- Partial indexes on active records (is_posted_to_gl = false)

**Foreign Key Relationships:**
- Company-scoped relationships for multi-tenancy
- Soft deletes for audit trail preservation
- Polymorphic relationships for flexible GL integration

**Database Migration Strategy:**
- Version-controlled migrations in timestamped files
- Rollback support for safe deployments
- Data seeding for initial AP account setup
- Migration testing in CI/CD pipeline

### API Design

**Endpoints:**

- `GET /api/payment-vouchers` - List payment vouchers with filtering
- `POST /api/payment-vouchers` - Create new payment voucher
- `GET /api/payment-vouchers/{id}` - Get payment voucher details
- `PUT /api/payment-vouchers/{id}` - Update payment voucher
- `POST /api/payment-vouchers/{id}/allocate` - Allocate payment to invoices
- `POST /api/payment-vouchers/{id}/approve` - Approve payment voucher
- `POST /api/supplier-invoices/{id}/post-to-gl` - Post invoice to GL
- `GET /api/ap-reports/outstanding` - Get outstanding payables report

**Request/Response Formats:**

```typescript
interface PaymentVoucherRequest {
  supplier_id: number;
  payment_date: string;
  amount: number;
  currency_id: number;
  payment_method: 'cash' | 'cheque' | 'bank_transfer' | 'wire';
  allocations: Array<{
    supplier_invoice_id: number;
    allocated_amount: number;
  }>;
}

interface PaymentVoucherResponse {
  id: number;
  serial_number: string;
  supplier: Supplier;
  payment_date: string;
  amount: number;
  currency: Currency;
  status: 'draft' | 'approved' | 'paid' | 'cancelled';
  allocations: PaymentAllocation[];
  created_at: string;
  updated_at: string;
}
```

**Authentication and Authorization:**
- Laravel Sanctum for API token authentication
- Role-based permissions (payment.create, payment.approve, gl.post)
- Company-scoped data access
- Audit logging for all API operations

**Error Handling:**
- HTTP status codes (400 for validation, 403 for unauthorized, 404 for not found)
- Structured error responses with error codes and messages
- Validation error details for form corrections
- Rate limiting with 429 responses

**Rate Limiting and Caching:**
- API rate limiting: 100 requests/minute per user
- Response caching for read-only endpoints (5 minutes)
- Database query result caching for reports
- Redis-based session storage

### Frontend Architecture

#### Component Hierarchy Documentation

The AP module leverages FilamentPHP v4.1 for consistent admin interface components.

**Payment Voucher Management Page Layout:**

```
AP Module Navigation
├── Payment Vouchers Resource
│   ├── List Table (Filament Table)
│   │   ├── Serial Number Column
│   │   ├── Supplier Name Column (relationship)
│   │   ├── Payment Date Column
│   │   ├── Amount Column (currency formatted)
│   │   ├── Status Badge Column
│   │   └── Actions (View, Edit, Allocate)
│   ├── Create Form (Filament Form)
│   │   ├── Supplier Select (relationship)
│   │   ├── Payment Date Picker
│   │   ├── Amount Input (currency)
│   │   ├── Payment Method Select
│   │   ├── Bank Details Section (conditional)
│   │   └── Allocation Repeater
│   └── Edit Form (similar to create)
├── Supplier Invoices Resource
│   ├── List Table with payment status
│   ├── Invoice Details View
│   └── Payment History Tab
└── Debit Notes Resource
    ├── List Table
    ├── Create Form with reason selection
    └── Application Form (link to invoice)
```

**State Flow Diagram:**

```mermaid
stateDiagram-v2
    [*] --> Draft: Create Payment Voucher
    Draft --> Allocating: Add Invoice Allocations
    Allocating --> Allocated: Save Allocations
    Allocated --> Approved: Approve Voucher
    Approved --> Posted: Post to GL
    Posted --> Paid: Mark as Paid
    Draft --> Cancelled: Cancel Voucher
    Allocating --> Cancelled
    Approved --> Cancelled
    Posted --> Cancelled
```

**Reusable Component Library:**
- CurrencyInput component for monetary values
- StatusBadge component for workflow states
- AllocationTable component for payment distributions
- AuditTrail component for change history

**State Management Patterns:**
- Filament's built-in form state management
- Livewire for reactive components
- Session storage for form drafts
- Database persistence for workflow states

**TypeScript Interfaces:**

```typescript
interface PaymentVoucher {
  id: number;
  serial_number: string;
  supplier_id: number;
  payment_date: string;
  amount: number;
  currency_id: number;
  status: PaymentStatus;
  allocations: PaymentAllocation[];
}

interface PaymentAllocation {
  supplier_invoice_id: number;
  allocated_amount: number;
  invoice: SupplierInvoice;
}

type PaymentStatus = 'draft' | 'approved' | 'paid' | 'cancelled';
```

### Security Performance

**Authentication/Authorization Requirements:**
- Multi-factor authentication for payment approvals
- Role-based access control (RBAC) with granular permissions
- Company data isolation for multi-tenant security
- Session timeout and secure logout mechanisms

**Data Validation and Sanitization:**
- Server-side validation for all financial inputs
- XSS protection for user-generated content
- SQL injection prevention via Eloquent ORM
- File upload validation for payment attachments

**Performance Optimization Strategies:**
- Database query optimization with eager loading
- Redis caching for frequently accessed data
- Background job processing for GL posting
- Database indexing on critical query paths

**Caching Mechanisms:**
- Application-level caching for configuration data
- Database query result caching for reports
- Page-level caching for static content
- CDN integration for asset delivery