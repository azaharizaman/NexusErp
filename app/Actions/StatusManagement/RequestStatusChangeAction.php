<?php

namespace App\Actions\StatusManagement;

use App\Models\DocumentModel;
use App\Models\StatusRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class RequestStatusChangeAction
{
    use AsAction;

    /**
     * Create a status change request and notify approvers.
     */
    public function handle(
        Model $model,
        int $documentModelId,
        int $currentStatusId,
        int $requestedStatusId,
        ?array $approvers = null
    ): StatusRequest {
        $statusRequest = StatusRequest::create([
            'document_model_id' => $documentModelId,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'current_status_id' => $currentStatusId,
            'requested_status_id' => $requestedStatusId,
            'approvers' => $approvers,
            'is_approved' => false,
            'requested_by' => Auth::id(),
        ]);

        // TODO: Send notifications to approvers
        // Notification::send($approvers, new StatusChangeRequested($statusRequest));

        return $statusRequest;
    }

    /**
     * Validation rules for status change request.
     */
    public function rules(): array
    {
        return [
            'document_model_id' => ['required', 'exists:document_models,id'],
            'current_status_id' => ['required', 'exists:model_statuses,id'],
            'requested_status_id' => ['required', 'exists:model_statuses,id'],
            'approvers' => ['nullable', 'array'],
        ];
    }
}
