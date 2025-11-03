<?php

namespace App\Actions\Company;

use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteCompany
{
    use AsAction;

    /**
     * Soft delete or permanently delete a company
     */
    public function handle(Company $company, bool $forceDelete = false): bool
    {
        return DB::transaction(function () use ($company, $forceDelete) {
            if ($forceDelete) {
                return $company->forceDelete();
            }

            return $company->delete();
        });
    }

    /**
     * Action authorization
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('delete_companies');
    }
}
