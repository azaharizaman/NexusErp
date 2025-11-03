<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class UomSettings extends Settings
{
    public string $default_weight_unit;
    public string $default_length_unit;
    public string $default_volume_unit;
    public string $default_area_unit;
    public string $default_temperature_unit;
    public bool $enable_compound_units;
    public bool $enable_custom_units;
    public bool $auto_convert_units;
    public int $conversion_precision;
    public bool $show_unit_codes;
    public bool $show_unit_names;

    public static function group(): string
    {
        return 'uom';
    }

    public static function defaults(): array
    {
        return [
            'default_weight_unit' => 'KG',
            'default_length_unit' => 'M',
            'default_volume_unit' => 'L',
            'default_area_unit' => 'M2',
            'default_temperature_unit' => 'C',
            'enable_compound_units' => true,
            'enable_custom_units' => true,
            'auto_convert_units' => false,
            'conversion_precision' => 4,
            'show_unit_codes' => true,
            'show_unit_names' => true,
        ];
    }
}