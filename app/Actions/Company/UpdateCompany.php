<?php

namespace App\Actions\Company;

use App\Models\Company;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Support\Facades\DB;

class UpdateCompany
{
    use AsAction;

    /**
     * Update an existing company
     */
    public function handle(Company $company, array $data): Company
    {
        return DB::transaction(function () use ($company, $data) {
            // Update the company
            $company->update($data);

            // Refresh the model to get updated data
            $company->refresh();

            return $company;
        });
    }

    /**
     * Validation rules for company update
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => ['sometimes', 'required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'website' => ['nullable', 'url', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Action authorization
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('update_companies');
    }
}