<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class FinancialSettings extends Settings
{
    public string $default_currency;
    public string $currency_symbol;
    public string $currency_position;
    public int $decimal_places;
    public string $decimal_separator;
    public string $thousands_separator;
    public float $default_tax_rate;
    public bool $tax_inclusive_pricing;
    public string $financial_year_start;
    public string $invoice_prefix;
    public string $quote_prefix;
    public string $purchase_order_prefix;
    public int $invoice_number_length;
    public bool $auto_invoice_numbering;

    public static function group(): string
    {
        return 'financial';
    }

    public static function defaults(): array
    {
        return [
            'default_currency' => 'USD',
            'currency_symbol' => '$',
            'currency_position' => 'before',
            'decimal_places' => 2,
            'decimal_separator' => '.',
            'thousands_separator' => ',',
            'default_tax_rate' => 0.00,
            'tax_inclusive_pricing' => false,
            'financial_year_start' => '01-01',
            'invoice_prefix' => 'INV-',
            'quote_prefix' => 'QUO-',
            'purchase_order_prefix' => 'PO-',
            'invoice_number_length' => 6,
            'auto_invoice_numbering' => true,
        ];
    }
}