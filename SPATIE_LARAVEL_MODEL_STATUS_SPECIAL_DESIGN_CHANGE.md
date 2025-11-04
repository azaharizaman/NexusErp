# Detailed Technical Specification for Status Management System

## Overview
The goal is to create a system for managing and configuring statuses for models (documents) within the NexusErp platform. This feature enables users to:
- Configure which models can have specific statuses.
- Define transitions between statuses with customizable approval workflows.
- Assign conditions based on roles/groups for approving status transitions.

The system integrates the following packages:
1. **Spatie/Laravel-model-status**: For managing model statuses.
2. **azaharizaman/laravel-serial-numbering**: To associate statuses with document serial numbers.
3. **azaharizaman/laravel-backoffice**: For managing staff roles to enforce approval workflows.
4. **Laravel Actions**: To define reusable actions for managing status transitions and approvals.

---

## Components

### 1. Backend
- Extend **Spatie/Laravel-model-status**:
   - Add conditional logic to enforce approval workflows for transitions.
   - Track approval statuses for pending transitions.

- Utilize **Laravel Actions Package**:
   - Define actions for creating statuses, transitions, initiating requests, and handling approvals.

- Integrate existing packages:
   1. **azaharizaman/laravel-serial-numbering**: Link statuses to uniquely identified documents.
   2. **azaharizaman/laravel-backoffice**: Set permissions for staff roles/groups on transitions and approvals.

---

### 2. Frontend
A Nexus Panel interface for:
1. CRUD management of statuses and transitions.
2. Configuring transitions with conditions (approval workflows).
3. Assigning roles or specific staff members to approval workflows.

---

## Database Design

### Tables

#### **models**
Represents the customizable models/documents.

| Column          | Type         | Description                      |
|------------------|--------------|-----------------------------------|
| id              | BIGINT, PK   | Unique Identifier                |
| name            | String       | Name of the model                |
| serial_number   | String       | Serialized document identifier   |
| created_at      | Timestamp    | Created Date                     |
| updated_at      | Timestamp    | Updated Date                     |

---

#### **statuses**
Stores possible statuses for models.

| Column          | Type         | Description                      |
|------------------|--------------|-----------------------------------|
| id              | BIGINT, PK   | Unique Identifier                |
| model_id        | BIGINT, FK   | Reference to Models table        |
| name            | String       | Name of status                   |
| created_at      | Timestamp    | Created Date                     |
| updated_at      | Timestamp    | Updated Date                     |

---

#### **status_transitions**
Tracks valid status changes for models.

| Column          | Type         | Description                      |
|------------------|--------------|-----------------------------------|
| id              | BIGINT, PK   | Unique Identifier                |
| status_from_id  | BIGINT, FK   | Transition from status ID        |
| status_to_id    | BIGINT, FK   | Transition to status ID          |
| condition       | JSON         | JSON to define approval logic    |
| created_at      | Timestamp    | Created Date                     |
| updated_at      | Timestamp    | Updated Date                     |

---

#### **approval_workflows**
Stores approval workflows defining the required roles/staff for transitions.

| Column          | Type         | Description                      |
|------------------|--------------|-----------------------------------|
| id              | BIGINT, PK   | Unique Identifier                |
| status_transition_id | BIGINT, FK | Reference to transitions table |
| required_roles  | JSON         | Roles required for approval      |
| staff_ids       | JSON         | Specific staff assigned (if any) |
| approval_type   | Enum         | `single` or `group` process      |
| created_at      | Timestamp    | Created Date                     |
| updated_at      | Timestamp    | Updated Date                     |

---

#### **status_requests**
Tracks approval requests and their statuses.

| Column              | Type      | Description                    |
|---------------------|-----------|--------------------------------|
| id                  | BIGINT PK | Unique Identifier             |
| model_id            | BIGINT FK | Reference to Models table     |
| current_status_id   | BIGINT FK | Current status of model       |
| requested_status_id | BIGINT FK | Requested status              |
| approvers           | JSON      | Staff involved in the request |
| is_approved         | Boolean   | Approval status (True/False)  |
| created_at          | Timestamp | Created Date                  |
| updated_at          | Timestamp | Updated Date                  |

---

## Laravel Actions

### Suggested Actions

1. **CreateStatusAction**
   - Inputs: Model ID, Status Name.
   - Creates a new status entry for a model.

2. **CreateStatusTransitionAction**
   - Inputs: From Status, To Status, Approval Conditions.
   - Creates a transition with user-defined approval workflows.

3. **RequestStatusChangeAction**
   - Inputs: Model ID, Current Status, Requested Status.
   - Creates a change request and notifies the approvers.

4. **ApproveStatusChangeAction**
   - Inputs: Request ID, Approver ID, Decision (Approve/Reject).
   - Handles approvals and updates the request status.

5. **CheckApprovalStatusAction**
   - Inputs: Status Request ID.
   - Returns the current status of the request (approved/pending/rejected).

6. **TransitionStatusAction**
   - Inputs: Model ID, Current Status, Requested Status.
   - Verifies approval conditions and transitions the status.

---

### Example Action Implementation

```php
namespace App\Actions;

use Lorisleiva\Actions\Action;

class CreateStatusAction extends Action
{
    public function handle($modelId, $statusName)
    {
        return Status::create([
            'model_id' => $modelId,
            'name' => $statusName,
        ]);
    }
}
```
