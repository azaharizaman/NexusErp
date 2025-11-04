## General
- Before you start working on a tast, plan out what your approach will be in step by step manner.
- Always refer to the existing codebase and follow the established patterns and conventions.
- Write clean, maintainable, and well-documented code.
- Ensure that you have a good understanding of FilamentPHP version 4, Laravel 12, and any other relevant technologies used in the project.
- Write unit and feature tests for any new functionality you implement.
- Use the existing test suites to verify that your changes do not introduce any regressions.
- Follow the project's coding standards and best practices.
- Communicate with the team if you encounter any challenges or need clarification on requirements.
- Always use context7 to pull up the latest documentation on filamentPHP version 4 or specifically 4.1 before starting any changes to the code.
- After completing task, please update the ARCHITECTURAL_DECISIONS.md file with any new architectural decisions made during the implementation.
- Document any changes made to the codebase, including the rationale behind the changes and any relevant context. This documentation should be clear and concise to help future developers understand the reasoning behind the changes.
- Update the PROGRESS_CHECKLIST.md file to reflect the completion of the task, including any relevant details about the implementation that was suggested or can be hold for future improvements. Do not remove any existing items from the checklist; only add new items as needed and mark those that are completed.

## Models
- Models that implement status must use spatie model-status package and implement the HasStatuses trait.
- Models that has sortable behaviour must implement spatie eloquent-sortable package and implement the Sortable interface and use the Sortable trait.
- Models that have currency or money properties must implement ariaieboy/filament-currency package and the correct schema property. https://github.com/ariaieboy/filament-currency

## Business Logic
- Business logic should be implemented using lorisleiva/laravel-actions package. Each action should be placed in the App\Actions namespace and be as granular as possible. Actions can be grouped into sub-namespaces as needed.
- Actions should be invokable classes that encapsulate a single piece of business logic. They should be reusable and testable.
- Actions can be used in controllers, models, or other parts of the application to perform specific tasks.

## Filament Resources - Tables and Forms
- **ALWAYS** display meaningful relationship columns instead of raw IDs in tables and forms.
  - For table columns: Use `TextColumn::make('relationship.attribute')` format, e.g., `TextColumn::make('supplier.name')` instead of `TextColumn::make('supplier_id')`
  - For select fields in forms: Use `->relationship('relationshipName', 'displayColumn')` format, e.g., `->relationship('supplier', 'name')` instead of `->relationship('supplier', 'id')`
  - Common examples:
    - Suppliers: Use `supplier.name`
    - Invoices: Use `supplierInvoice.invoice_number` or `invoice.invoice_number`
    - Purchase Orders: Use `purchaseOrder.po_number`
    - Vouchers: Use `paymentVoucher.voucher_number`
    - Currencies: Use `currency.code` or `currency.name`
- **Audit Fields in Tables**: Display audit fields as relationship columns showing user names, not IDs.
  - Use `TextColumn::make('creator.name')->label('Created By')` instead of `TextColumn::make('created_by')`
  - Use `TextColumn::make('updater.name')->label('Updated By')` instead of `TextColumn::make('updated_by')`
  - Mark these as `->toggleable(isToggledHiddenByDefault: true)` if they don't need to be visible by default
- **Audit Fields in Forms**: NEVER include audit fields (`created_by`, `updated_by`) in forms as they are managed automatically by the system.
  - Remove these fields from forms entirely
  - If they must be displayed for reference, mark them as `->disabled()` and `->dehydrated(false)`
- See `docs/filament-best-practices.md` for detailed examples and patterns.

## Carbon Date Handling
- **ALWAYS** use `copy()` before mutating Carbon dates to prevent unintended side effects.
  - ❌ Incorrect: `$date->addDays(30)` (mutates original)
  - ✅ Correct: `$date->copy()->addDays(30)` (preserves original)
- This is critical when using dates in loops or generating multiple schedules from the same base date.

## Spatie ModelStatus Usage
- **ALWAYS** use strict equality comparison to check current status.
  - ❌ Incorrect: `$this->latestStatus('submitted') !== null`
  - ✅ Correct: `$this->latestStatus() === 'submitted'`
- Use `latestStatus()` without parameters to get the current status name, then compare with `===`.
- This ensures you're checking the current status, not just whether a status exists in history.
