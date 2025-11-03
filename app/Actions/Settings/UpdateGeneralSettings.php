<?php

namespace App\Actions\Settings;

use Lorisleiva\Actions\Concerns\AsAction;
use App\Settings\GeneralSettings;
use App\Settings\CompanySettings;
use App\Settings\FinancialSettings;
use App\Settings\UomSettings;
use App\Settings\NotificationSettings;

class UpdateGeneralSettings
{
    use AsAction;

    /**
     * Update general application settings
     */
    public function handle(array $data): GeneralSettings
    {
        $settings = app(GeneralSettings::class);

        foreach ($data as $key => $value) {
            if (property_exists($settings, $key)) {
                $settings->$key = $value;
            }
        }

        $settings->save();

        return $settings;
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'app_name' => ['sometimes', 'required', 'string', 'max:255'],
            'app_description' => ['sometimes', 'nullable', 'string', 'max:500'],
            'app_version' => ['sometimes', 'nullable', 'string', 'max:50'],
            'app_environment' => ['sometimes', 'required', 'in:production,staging,development'],
            'timezone' => ['sometimes', 'required', 'string', 'timezone'],
            'locale' => ['sometimes', 'required', 'string', 'max:10'],
            'items_per_page' => ['sometimes', 'required', 'integer', 'min:5', 'max:100'],
            'date_format' => ['sometimes', 'required', 'string', 'max:50'],
            'time_format' => ['sometimes', 'required', 'string', 'max:50'],
            'debug_mode' => ['sometimes', 'boolean'],
            'maintenance_mode' => ['sometimes', 'boolean'],
            'maintenance_message' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Action authorization
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('manage_settings');
    }
}