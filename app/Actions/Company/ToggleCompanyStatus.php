<?php

namespace App\Actions\Company;

use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class ToggleCompanyStatus
{
    use AsAction;

    /**
     * Toggle company active status (activate/deactivate)
     */
    public function handle(Company $company, ?bool $status = null): Company
    {
        return DB::transaction(function () use ($company, $status) {
            // If status is not provided, toggle current status
            $newStatus = $status ?? ! $company->is_active;

            if (! $newStatus) {
                $activeChildCount = $company->childCompanies()
                    ->where('is_active', true)
                    ->count();

                if ($activeChildCount > 0) {
                    throw ValidationException::withMessages([
                        'is_active' => __("Cannot deactivate '{$company->name}' while it has active child companies. Deactivate or reassign all child companies first."),
                    ]);
                }
            }

            $company->update([
                'is_active' => $newStatus,
                'status_changed_at' => now(),
                'status_changed_by' => auth()->id(),
            ]);

            // Refresh the model to get updated data
            $company->refresh();

            return $company;
        });
    }

    /**
     * Mark company as inactive
     */
    public function markInactive(Company $company): Company
    {
        return $this->handle($company, false);
    }

    /**
     * Mark company as active
     */
    public function markActive(Company $company): Company
    {
        return $this->handle($company, true);
    }

    /**
     * Action authorization
     */
    public function authorize(Company $company): bool
    {
        return auth()->check() && auth()->user()->can('update_companies');
    }

    /**
     * Get success message based on the action
     */
    public function getSuccessMessage(Company $company): string
    {
        return $company->is_active
            ? "Company '{$company->name}' has been activated successfully."
            : "Company '{$company->name}' has been deactivated successfully.";
    }

    /**
     * Use as controller method
     */
    public function asController(Company $company): Company
    {
        return $this->handle($company);
    }
}
