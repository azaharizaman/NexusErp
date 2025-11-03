# Company Status Management Feature

## Overview

A new Laravel Action has been implemented to manage company active/inactive status with full Filament integration. This feature allows you to easily activate or deactivate companies through the admin interface with proper confirmations and bulk operations.

## Action Implementation

### ToggleCompanyStatus Action

**Location**: `app/Actions/Company/ToggleCompanyStatus.php`

**Key Features**:
- ✅ Toggle company status (active ↔ inactive)
- ✅ Force set specific status
- ✅ Track status changes with timestamps
- ✅ Proper authorization checks
- ✅ Transaction-safe operations
- ✅ Success message generation

**Usage Examples**:

```php
use App\Actions\Company\ToggleCompanyStatus;

// Toggle current status
$company = ToggleCompanyStatus::run($company);

// Force specific status
$company = ToggleCompanyStatus::run($company, false); // Deactivate
$company = ToggleCompanyStatus::run($company, true);  // Activate

// Using instance methods
$action = new ToggleCompanyStatus();
$company = $action->markInactive($company);
$company = $action->markActive($company);

// Get success message
$message = $action->getSuccessMessage($company);
```

## Filament Integration

### 1. View Company Page

**Location**: `app/Filament/Resources/Companies/Pages/ViewCompany.php`

**Features**:
- ✅ Dynamic button label (Activate/Deactivate)
- ✅ Color-coded buttons (Green for activate, Red for deactivate)
- ✅ Confirmation modal with contextual messages
- ✅ Real-time UI updates after action
- ✅ Success notifications
- ✅ Permission-based visibility

**Button Behavior**:
- **Active Company**: Shows "Deactivate Company" button (red, with X icon)
- **Inactive Company**: Shows "Activate Company" button (green, with check icon)
- **Confirmation**: Requires user confirmation with descriptive messages
- **Feedback**: Shows success notification after action completion

### 2. Companies Table (List View)

**Location**: `app/Filament/Resources/Companies/Tables/CompaniesTable.php`

**Features**:
- ✅ Enhanced status column with colored icons
- ✅ Row-level toggle actions for individual companies
- ✅ Bulk activate/deactivate actions
- ✅ Smart bulk actions (only affect companies that need status change)
- ✅ Batch notifications with counts

**Table Enhancements**:
- **Status Column**: Green check for active, red X for inactive
- **Row Actions**: Quick toggle button for each company
- **Bulk Actions**: 
  - "Activate Selected" - Activates all selected inactive companies
  - "Deactivate Selected" - Deactivates all selected active companies

## User Interface

### Visual Indicators

**Active Companies**:
- ✅ Green check circle icon
- ✅ "Deactivate" button appears in red
- ✅ Status shows as "Active"

**Inactive Companies**:
- ❌ Red X circle icon  
- ✅ "Activate" button appears in green
- ✅ Status shows as "Inactive"

### Confirmation Dialogs

**Deactivation**:
```
Title: "Deactivate Company"
Message: "Are you sure you want to deactivate this company? 
         This will prevent the company from being used in new transactions."
Button: "Deactivate" (Red)
```

**Activation**:
```
Title: "Activate Company"
Message: "Are you sure you want to activate this company? 
         This will allow the company to be used in transactions again."
Button: "Activate" (Green)
```

## Security & Permissions

**Authorization**: 
- Requires `update_companies` permission
- Checks performed at action level and UI level
- Users without permission won't see the toggle buttons

**Data Integrity**:
- All operations wrapped in database transactions
- Status changes tracked with timestamps
- User tracking for audit purposes (if implemented)

## Technical Implementation

### Database Updates

The action updates the following fields:
```php
[
    'is_active' => $newStatus,
    'status_changed_at' => now(),        // Timestamp tracking
    'status_changed_by' => auth()->id(), // User tracking
]
```

### Action Methods

```php
// Core method - toggles or sets specific status
public function handle(Company $company, ?bool $status = null): Company

// Convenience methods
public function markInactive(Company $company): Company
public function markActive(Company $company): Company

// UI helpers
public function getSuccessMessage(Company $company): string
```

### Error Handling

- Database transaction rollback on errors
- Proper exception handling
- User-friendly error messages
- Graceful degradation if permissions are missing

## Testing

**Test File**: `tests/Feature/Actions/Company/ToggleCompanyStatusTest.php`

**Test Coverage**:
- ✅ Toggle functionality
- ✅ Specific activation/deactivation
- ✅ Force status setting
- ✅ Success message generation
- ✅ Database persistence
- ✅ Controller integration

**Run Tests**:
```bash
php artisan test tests/Feature/Actions/Company/ToggleCompanyStatusTest.php
```

## Usage Scenarios

### Single Company Management
1. Navigate to Company view page
2. Click "Activate" or "Deactivate" button
3. Confirm action in modal
4. See success notification
5. Button and status update automatically

### Bulk Operations
1. Go to Companies list page
2. Select multiple companies using checkboxes
3. Choose "Activate Selected" or "Deactivate Selected" from bulk actions
4. Confirm bulk operation
5. See notification with count of affected companies

### Programmatic Usage
```php
// In your controllers or other parts of the application
use App\Actions\Company\ToggleCompanyStatus;

// Quick toggle
$company = ToggleCompanyStatus::run($company);

// Conditional logic
if ($company->is_active) {
    $company = ToggleCompanyStatus::run($company, false);
    Mail::to($admin)->send(new CompanyDeactivatedMail($company));
}
```

## Benefits

1. **Consistent Business Logic**: All status changes go through the same action
2. **Audit Trail**: Track when and who changed company status
3. **User-Friendly Interface**: Clear visual indicators and confirmations
4. **Bulk Operations**: Efficient management of multiple companies
5. **Permission Control**: Secure access control
6. **Testable**: Comprehensive test coverage
7. **Reusable**: Can be used from anywhere in the application

## Future Enhancements

Potential improvements you could add:

1. **Status History**: Track all status changes with reasons
2. **Batch Processing**: Queue bulk operations for large datasets
3. **Email Notifications**: Notify stakeholders of status changes
4. **Conditional Rules**: Prevent deactivation if company has active orders
5. **API Integration**: RESTful endpoints for external systems
6. **Scheduled Actions**: Automatically activate/deactivate based on dates

This implementation provides a solid foundation for company status management that can be extended as your business requirements evolve.