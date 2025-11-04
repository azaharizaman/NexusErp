<?php

namespace App\Actions\StatusManagement;

use App\Models\ModelStatus;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateStatusAction
{
    use AsAction;

    /**
     * Create a new status for a document model.
     */
    public function handle(int $documentModelId, string $statusName, ?string $color = 'gray', ?string $description = null): ModelStatus
    {
        return ModelStatus::create([
            'document_model_id' => $documentModelId,
            'name' => $statusName,
            'color' => $color,
            'description' => $description,
        ]);
    }

    /**
     * Validation rules for status creation.
     */
    public function rules(): array
    {
        return [
            'document_model_id' => ['required', 'exists:document_models,id'],
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ];
    }
}
