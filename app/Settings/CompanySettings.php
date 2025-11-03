<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class CompanySettings extends Settings
{
    public string $company_name;
    public ?string $company_registration_number;
    public ?string $company_tax_number;
    public ?string $company_phone;
    public ?string $company_email;
    public ?string $company_website;
    public ?string $company_address_line_1;
    public ?string $company_address_line_2;
    public ?string $company_city;
    public ?string $company_state;
    public ?string $company_postal_code;
    public ?string $company_country;
    public ?string $company_logo;
    public ?string $company_description;

    public static function group(): string
    {
        return 'company';
    }

    public static function defaults(): array
    {
        return [
            'company_name' => '',
            'company_registration_number' => null,
            'company_tax_number' => null,
            'company_phone' => null,
            'company_email' => null,
            'company_website' => null,
            'company_address_line_1' => null,
            'company_address_line_2' => null,
            'company_city' => null,
            'company_state' => null,
            'company_postal_code' => null,
            'company_country' => null,
            'company_logo' => null,
            'company_description' => null,
        ];
    }
}