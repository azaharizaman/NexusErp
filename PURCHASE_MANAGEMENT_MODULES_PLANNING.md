## MODULES

---

## 🧭 Overall Structure: “Purchase Management” Module Navigation

Here’s the high-level grouping you should aim for:

### 1️⃣ Procurement Setup

(*for configuration, master data, and static references*)

* **Business Partners** (subset of Business Partner where is_supplier is true)
* **Items / Materials Catalog** (extend items from azaharizaman/laravel-inventory-management package)
* **UOM & Price Lists** (extend UMO from package azaharizaman/laravel-uom-management)
* **Currencies & Exchange Rates**
* **Tax & Charge Rules**
* **Terms & Conditions Templates**

---

### 2️⃣ Requisition Management (Transactional models need to implement Controlled Serial Numbering by extending azaharizaman/laravel-serial-numbering package)

(*where internal requests are raised before sourcing*)

* **Purchase Requests (PR)**
* **Request for Quotation (RFQ)**
* **Quotation Comparison / Evaluation**
* **Purchase Recommendation**

---

### 3️⃣ Sourcing & Ordering (Transactional models need to implement Controlled Serial Numbering by extending azaharizaman/laravel-serial-numbering package)

(*supplier-facing processes, post-approval of requests*)

* **Purchase Orders (PO)**
* **PO Revisions / Amendments**
* **Contracts & Blanket Orders**
* **Delivery Schedules**

---

### 4️⃣ Receiving & Invoice Processing (Transactional models need to implement Controlled Serial Numbering by extending azaharizaman/laravel-serial-numbering package)

(*ensuring goods/services are received and matched with PO/invoices*)

* **Goods Received Notes (GRN)**
* **Supplier Invoices**
* **Three-way Matching (PO–GRN–Invoice)**
* **Debit / Credit Notes**

---

### 5️⃣ Payments & Settlements (Transactional models need to implement Controlled Serial Numbering by extending azaharizaman/laravel-serial-numbering package)

(*managing financial commitments, for multi-currency handling*)

* **Payment Vouchers**
* **Payment Schedules**
* **Multi-Currency Ledger View**
* **Outstanding Payables Report**

---

### 6️⃣ Procurement Insights & Reports

(*analytics, tracking KPIs, and audits*)

* **Spend Analysis**
* **Supplier Performance**
* **Open PR / PO Tracker**
* **Aging & Payment Analysis**
* **Audit Logs / Activity Trail**

---

### 7️⃣ Administration & Policy

(*for admins or procurement officers managing access or workflow rules*)

* **Approval Matrix & Workflow Rules**
* **Procurement Policies**
* **Delegation of Authority (DOA)** (Will be implemented later once package azaharizaman/laravel-status-transitions is complete)
* **Notification Templates (Email / In-App)**

---

## 🧩 Suggested Navigation Grouping for Filament

Below is how you can structure it in your Filament panel:

```php
// In your Purchase PanelProvider
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

Then, each **Resource** defines its group:

```php
public static function getNavigationGroup(): ?string
{
    return 'Requisition Management';
}
```

---

## 🧱 Suggested Filament Resource Mapping

| Group                   | Resource               | Model                             |
| ----------------------- | ---------------------- | --------------------------------- |
| Procurement Setup       | Suppliers              | `Vendor`                          |
|                         | Items / Materials      | `Item`                            |
|                         | Price Lists            | `PriceList`                       |
|                         | Currency & Exchange    | `Currency`, `ExchangeRate`        |
|                         | Tax Rules              | `TaxRule`                         |
| Requisition Management  | Purchase Requests      | `PurchaseRequest`                 |
|                         | RFQs                   | `RequestForQuotation`             |
|                         | Quotations             | `Quotation`                       |
|                         | Recommendations        | `PurchaseRecommendation`          |
| Sourcing & Ordering     | Purchase Orders        | `PurchaseOrder`                   |
|                         | PO Amendments          | `PurchaseOrderRevision`           |
|                         | Contracts              | `PurchaseContract`                |
|                         | Delivery Schedules     | `DeliverySchedule`                |
| Receiving & Invoicing   | Goods Received Notes   | `GRN`                             |
|                         | Supplier Invoices      | `SupplierInvoice`                 |
|                         | Three-way Matching     | `InvoiceMatching`                 |
|                         | Debit / Credit Notes   | `DebitNote`, `CreditNote`         |
| Payments & Settlements  | Payment Vouchers       | `PaymentVoucher`                  |
|                         | Payment Schedules      | `PaymentSchedule`                 |
|                         | Payables Ledger        | `PayableLedger`                   |
| Procurement Insights    | Spend Analysis         | (Custom Page or Chart Widget)     |
|                         | Supplier Performance   | (Widget)                          |
|                         | Open PR/PO Tracker     | (Widget)                          |
|                         | Audit Logs             | (Filament Page or Activity Model) |
| Administration & Policy | Approval Matrix        | `ApprovalRule`                    |
|                         | Policies               | `ProcurementPolicy`               |
|                         | DOA                    | `DelegationAuthority`             |
|                         | Notification Templates | `NotificationTemplate`            |

---

## 💡 Design Considerations

### 🪶 Keep Navigation Compact

* Only show “core” daily items (PR, RFQ, PO, Invoice) by default.
* Collapse setup/admin groups into expandable sections.
* Use icons + short labels (e.g., “PR”, “PO”, “GRN”) to save space.

### 🧩 Multi-Currency

* Include a background job or sync process to update exchange rates daily.
* Add “Currency” and “Exchange Rate” under *Procurement Setup*.
* Payment and Ledger pages should format amounts based on selected currency and base conversion.

### 🔐 Role-Based Access

Typical roles:

* **Requester:** can create PRs only.
* **Buyer/Procurement Officer:** can handle RFQs, POs.
* **Finance Officer:** can access Invoices, Payments.
* **Manager/Approver:** can review approvals and analytics.
  Use Filament’s `authorizeResource()` or Spatie Roles & Permissions.

### 📊 Analytics (for large clients)

Use Filament Widgets to display KPIs:

* “Total Spend by Supplier”
* “Top 10 Vendors”
* “Pending Approvals”
* “PO Aging Summary”

These can appear on a dashboard home page inside the Purchase panel.

---

## 🧠 Scalability Vision

When your company later adds other modules like:

* Facility Management
* Asset Management
* Inventory or HR
  you can reuse the same modular pattern: each panel gets its own navigation + dashboard, keeping UX tidy and enterprise-grade.

---
