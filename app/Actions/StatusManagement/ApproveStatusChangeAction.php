<?php

namespace App\Actions\StatusManagement;

use App\Models\StatusRequest;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class ApproveStatusChangeAction
{
    use AsAction;

    /**
     * Approve or reject a status change request.
     */
    public function handle(int $requestId, bool $decision, ?string $rejectionReason = null): StatusRequest
    {
        $statusRequest = StatusRequest::findOrFail($requestId);

        $statusRequest->update([
            'is_approved' => $decision,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => $decision ? null : $rejectionReason,
        ]);

        if ($decision) {
            // If approved, trigger the actual status transition
            TransitionStatusAction::run(
                $statusRequest->model,
                $statusRequest->current_status_id,
                $statusRequest->requested_status_id
            );
        }

        // TODO: Send notification to requester
        // $statusRequest->requester->notify(new StatusChangeProcessed($statusRequest));

        return $statusRequest->fresh();
    }

    /**
     * Validation rules for approval.
     */
    public function rules(): array
    {
        return [
            'request_id' => ['required', 'exists:status_requests,id'],
            'decision' => ['required', 'boolean'],
            'rejection_reason' => ['nullable', 'string', 'required_if:decision,false'],
        ];
    }
}
