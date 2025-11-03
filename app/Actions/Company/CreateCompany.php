<?php

namespace App\Actions\Company;

use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Validation\ValidationException;

class CreateCompany
{
    use AsAction;

    /**
     * Create a new company with all necessary setup
     */
    public function handle(array $data): Company
    {
        return DB::transaction(function () use ($data) {
            // Validate company code uniqueness
            if (Company::where('code', $data['code'])->exists()) {
                throw ValidationException::withMessages([
                    'code' => 'Company code already exists.'
                ]);
            }

            // Create the company
            $company = Company::create([
                'name' => $data['name'],
                'code' => $data['code'],
                'description' => $data['description'] ?? null,
                'parent_company_id' => $data['parent_company_id'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            // Log company creation (optional - if you have activity logging package)
            // activity()
            //     ->performedOn($company)
            //     ->log('Company created');

            return $company;
        });
    }

    /**
     * Validation rules for company creation
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:backoffice_companies,code'],
            'description' => ['nullable', 'string'],
            'parent_company_id' => ['nullable', 'integer', 'exists:backoffice_companies,id'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Action authorization
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('create_companies');
    }

    /**
     * Use as controller method
     */
    public function asController(): Company
    {
        $data = request()->validate($this->rules());
        return $this->handle($data);
    }

    /**
     * Use as job
     */
    public function asJob(array $data): void
    {
        $this->handle($data);
    }
}