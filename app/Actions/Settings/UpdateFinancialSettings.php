<?php

namespace App\Actions\Settings;

use Lorisleiva\Actions\Concerns\AsAction;
use App\Settings\FinancialSettings;

class UpdateFinancialSettings
{
    use AsAction;

    /**
     * Update financial settings
     */
    public function handle(array $data): FinancialSettings
    {
        $settings = app(FinancialSettings::class);

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
            'default_currency' => ['sometimes', 'required', 'string', 'size:3'],
            'currency_symbol' => ['sometimes', 'required', 'string', 'max:10'],
            'currency_position' => ['sometimes', 'required', 'in:before,after'],
            'decimal_places' => ['sometimes', 'required', 'integer', 'min:0', 'max:4'],
            'decimal_separator' => ['sometimes', 'required', 'string', 'max:1'],
            'thousands_separator' => ['sometimes', 'required', 'string', 'max:1'],
            'default_tax_rate' => ['sometimes', 'required', 'numeric', 'min:0', 'max:100'],
            'tax_inclusive_pricing' => ['sometimes', 'boolean'],
            'financial_year_start' => ['sometimes', 'required', 'string', 'regex:/^\d{2}-\d{2}$/'],
            'invoice_prefix' => ['sometimes', 'required', 'string', 'max:10'],
            'quote_prefix' => ['sometimes', 'required', 'string', 'max:10'],
            'purchase_order_prefix' => ['sometimes', 'required', 'string', 'max:10'],
            'invoice_number_length' => ['sometimes', 'required', 'integer', 'min:4', 'max:10'],
            'auto_invoice_numbering' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Action authorization
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('manage_financial_settings');
    }
}