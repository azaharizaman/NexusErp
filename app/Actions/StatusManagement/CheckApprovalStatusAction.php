<?php

namespace App\Actions\StatusManagement;

use App\Models\StatusRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class CheckApprovalStatusAction
{
    use AsAction;

    /**
     * Check the current status of a status change request.
     */
    public function handle(int $requestId): array
    {
        $statusRequest = StatusRequest::with([
            'currentStatus',
            'requestedStatus',
            'requester',
            'approver',
        ])->findOrFail($requestId);

        return [
            'id' => $statusRequest->id,
            'is_approved' => $statusRequest->is_approved,
            'status' => $statusRequest->is_approved ? 'approved' : ($statusRequest->approved_at ? 'rejected' : 'pending'),
            'current_status' => $statusRequest->currentStatus->name,
            'requested_status' => $statusRequest->requestedStatus->name,
            'requested_by' => $statusRequest->requester->name,
            'approved_by' => $statusRequest->approver?->name,
            'approved_at' => $statusRequest->approved_at,
            'rejection_reason' => $statusRequest->rejection_reason,
        ];
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        return [
            'request_id' => ['required', 'exists:status_requests,id'],
        ];
    }
}
