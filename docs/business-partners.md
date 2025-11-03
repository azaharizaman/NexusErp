# Business Partners

Business partners represent the organisations we collaborate with, including suppliers, customers, or entities that act as both. Partners can be linked hierarchically—for example, "Microsoft (Malaysia) Sdn Bhd" can reference "Microsoft Corporation" as its parent while retaining its own contacts.

## Data Model

- `business_partners`
  - Stores core profile information (name, code, email, phone, website, notes).
  - Flags indicate whether the partner is a supplier, customer, or both.
  - A self-referencing `parent_business_partner_id` column links a partner to another partner.
- `business_partner_contacts`
  - Holds the contacts that belong to a business partner and cascades on delete.

## Filament Resource

The Business Partner resource allows:

- Creating, viewing, editing, and soft-deleting business partners.
- Managing partner contacts via a relation manager.
- Preventing parent assignments that would create circular hierarchies during updates.

_Last updated: 2025-11-03._
