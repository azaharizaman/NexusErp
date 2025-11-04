<?php

namespace App\Actions\StatusManagement;

use App\Models\StatusTransition;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateStatusTransitionAction
{
    use AsAction;

    /**
     * Create a status transition with optional approval conditions.
     */
    public function handle(int $statusFromId, int $statusToId, ?array $condition = null): StatusTransition
    {
        return StatusTransition::create([
            'status_from_id' => $statusFromId,
            'status_to_id' => $statusToId,
            'condition' => $condition,
        ]);
    }

    /**
     * Validation rules for transition creation.
     */
    public function rules(): array
    {
        return [
            'status_from_id' => ['required', 'exists:model_statuses,id'],
            'status_to_id' => ['required', 'exists:model_statuses,id', 'different:status_from_id'],
            'condition' => ['nullable', 'array'],
        ];
    }
}
