# AP Module Foundation - GitHub Issues

This directory contains the implementation plan and GitHub issue creation resources for the Accounts Payable (AP) Module Foundation.

## Files

- **implementation-plan.md** - Detailed implementation plan with requirements, architecture, and technical specifications
- **github-issues.md** - Structured breakdown of the implementation into 6 GitHub issues
- **create-issues.sh** - Bash script to automatically create all GitHub issues

## Creating GitHub Issues

You have two options to create the GitHub issues:

### Option 1: Automated Script (Recommended)

Run the automated script that will create all 6 issues with proper labels, milestone, and descriptions:

```bash
cd docs/ways-of-work/plan/ap-module/foundation
./create-issues.sh
# Or specify a different repository:
# ./create-issues.sh owner/repo
```

**Prerequisites:**
- GitHub CLI (`gh`) must be installed and authenticated
- You must have write access to the repository
- Script uses `set -euo pipefail` for robust error handling

### Option 2: Manual Creation

If you prefer to create issues manually or review them first:

1. Review the `github-issues.md` file which contains detailed issue descriptions
2. For each issue, copy the content and create it manually via:
   - GitHub web interface (https://github.com/azaharizaman/NexusErp/issues/new)
   - GitHub CLI: `gh issue create --repo azaharizaman/NexusErp`

## Issue Overview

The implementation is broken down into 6 logical components:

| # | Title | Labels | Priority |
|---|-------|--------|----------|
| 1 | Database Schema & Models | `feature`, `accounting`, `ap`, `database`, `models` | High (Foundation) |
| 2 | Business Logic Actions | `feature`, `accounting`, `ap`, `actions`, `business-logic` | High |
| 3 | Filament Resources | `feature`, `accounting`, `ap`, `filament`, `ui` | Medium |
| 4 | REST API Endpoints | `feature`, `accounting`, `ap`, `api` | Low (Optional) |
| 5 | GL Integration | `feature`, `accounting`, `ap`, `gl-integration` | High |
| 6 | Testing & Documentation | `testing`, `documentation`, `accounting`, `ap` | High |

## Recommended Implementation Order

1. **Issue 1: Database Schema & Models** - Foundation layer that everything else depends on
2. **Issue 2: Business Logic Actions** - Core business rules and processing logic
3. **Issue 5: GL Integration** - Critical financial integration with General Ledger
4. **Issue 3: Filament Resources** - User interface for managing AP operations
5. **Issue 4: API Endpoints** - REST API (optional, implement if external API access is needed)
6. **Issue 6: Testing & Documentation** - Comprehensive testing and documentation

## Milestone

All issues should be assigned to the **"AP Module Foundation"** milestone, which represents the initial implementation of the Accounts Payable module.

## Dependencies

Issues have the following dependencies:
- Issues 2, 3, 4, 5 all depend on Issue 1 (Database Schema & Models)
- Issue 3 depends on Issue 2 (Business Logic Actions)
- Issue 6 can be worked on concurrently with other issues but should be completed last

## Related Documentation

- [Implementation Plan](./implementation-plan.md) - Full technical specification
- [GitHub Issues Breakdown](./github-issues.md) - Detailed issue descriptions
- [Accounting Module Planning](/ACCOUNTING_MODULE_PLANNING.md) - Overall accounting module plan
- [Progress Checklist](/PROGRESS_CHECKLIST.md) - Project-wide progress tracking
