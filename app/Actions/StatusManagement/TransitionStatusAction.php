<?php

namespace App\Actions\StatusManagement;

use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\ModelStatus\HasStatuses;

class TransitionStatusAction
{
    use AsAction;

    /**
     * Transition a model's status after verifying approval conditions.
     */
    public function handle(Model $model, int $currentStatusId, int $requestedStatusId): bool
    {
        // Ensure the model uses HasStatuses trait
        if (! in_array(HasStatuses::class, class_uses_recursive($model))) {
            throw new \InvalidArgumentException('Model ' . get_class($model) . ' must use HasStatuses trait');
        }

        // Get the status names from IDs
        $requestedStatus = \App\Models\ModelStatus::findOrFail($requestedStatusId);

        // Set the new status using Spatie's setStatus method
        $model->setStatus($requestedStatus->name);

        return true;
    }

    /**
     * Validation rules for transition.
     */
    public function rules(): array
    {
        return [
            'current_status_id' => ['required', 'exists:model_statuses,id'],
            'requested_status_id' => ['required', 'exists:model_statuses,id'],
        ];
    }
}
