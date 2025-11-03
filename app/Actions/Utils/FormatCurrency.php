<?php

namespace App\Actions\Utils;

use App\Helpers\SettingsHelper;
use App\Settings\FinancialSettings;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\LaravelSettings\Exceptions\MissingSettings;

class FormatCurrency
{
    use AsAction;

    /**
     * Format a monetary amount according to application settings
     */
    public function handle(float $amount, ?string $currency = null): string
    {
        $config = FinancialSettings::defaults();

        try {
            $financial = SettingsHelper::financial();

            $config = [
                'decimal_places' => $financial->decimal_places,
                'decimal_separator' => $financial->decimal_separator,
                'thousands_separator' => $financial->thousands_separator,
                'currency_symbol' => $financial->currency_symbol,
                'currency_position' => $financial->currency_position,
            ];
        } catch (MissingSettings $exception) {
            // Fallback to defaults when settings are not yet persisted
        }

        $formattedAmount = number_format(
            $amount,
            $config['decimal_places'],
            $config['decimal_separator'],
            $config['thousands_separator']
        );

        $symbol = $currency ? $this->getCurrencySymbol($currency) : $config['currency_symbol'];

        return $config['currency_position'] === 'before'
            ? $symbol . $formattedAmount
            : $formattedAmount . $symbol;
    }

    /**
     * Get currency symbol for a given currency code
     */
    private function getCurrencySymbol(string $currency): string
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'MYR' => 'RM',
            'SGD' => 'S$',
            'AUD' => 'A$',
            'CAD' => 'C$',
        ];

        return $symbols[$currency] ?? $currency;
    }

    /**
     * Static helper method
     */
    public static function run(float $amount, ?string $currency = null): string
    {
        return (new static)->handle($amount, $currency);
    }
}