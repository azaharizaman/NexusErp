# Architectural Decisions

## 2025-11-03 Panel Switching via User Menu
- Added Filament user menu actions in both `nexus` and `purchase-module` panels to generate deep links with `Filament::getPanel()->getUrl()`, providing the recommended panel-switching experience in v4.
- Restored the Nexus panel topbar so the shared user menu (and panel switcher action) remains accessible in the default location.

## 2025-11-03 Panel-Specific Branding
- Applied `brandName()` per panel so the topbar reflects the active module (`NexusERP` vs. `NexusERP - Purchase Module`), keeping navigation context clear for multi-panel users.
