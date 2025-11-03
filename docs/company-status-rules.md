# Company Status Rules

## Deactivation Safety

- A company cannot be deactivated while any of its child companies remain active and still reference it as their parent.
- Attempting to deactivate a company with active descendants surfaces an inline error in the Filament company view and the toggle action is halted.
- To complete a deactivation, either deactivate each child company or reassign those child companies to a different parent first.

_Last updated: 2025-11-03._
