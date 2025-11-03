<?php

namespace App\Actions\Utils;

use App\Helpers\SettingsHelper;
use Lorisleiva\Actions\Concerns\AsAction;

class FormatCurrency
{
    use AsAction;

    /**
     * Format a monetary amount according to application settings
     */
    public function handle(float $amount, ?string $currency = null): string
    {
        $financial = SettingsHelper::financial();
        $config = [
            'decimal_places' => (int) data_get($financial, 'decimal_places', 2),
            'decimal_separator' => (string) data_get($financial, 'decimal_separator', '.'),
            'thousands_separator' => (string) data_get($financial, 'thousands_separator', ','),
            'currency_symbol' => (string) data_get($financial, 'currency_symbol', '$'),
            'currency_position' => (string) data_get($financial, 'currency_position', 'before'),
        ];

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