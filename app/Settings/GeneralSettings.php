<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $app_name;
    public string $app_description;
    public string $app_logo;
    public string $app_favicon;
    public string $timezone;
    public string $date_format;
    public string $time_format;
    public string $datetime_format;
    public string $default_language;
    public bool $maintenance_mode;
    public ?string $maintenance_message;

    public static function group(): string
    {
        return 'general';
    }

    public static function defaults(): array
    {
        return [
            'app_name' => config('app.name', 'NexusERP'),
            'app_description' => 'Enterprise Resource Planning System',
            'app_logo' => '',
            'app_favicon' => '',
            'timezone' => config('app.timezone', 'UTC'),
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i:s',
            'datetime_format' => 'Y-m-d H:i:s',
            'default_language' => 'en',
            'maintenance_mode' => false,
            'maintenance_message' => null,
        ];
    }
}