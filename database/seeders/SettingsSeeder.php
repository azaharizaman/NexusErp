<?php

namespace Database\Seeders;

use App\Settings\GeneralSettings;
use App\Settings\CompanySettings;
use App\Settings\FinancialSettings;
use App\Settings\UomSettings;
use App\Settings\NotificationSettings;
use Spatie\LaravelSettings\Exceptions\MissingSettings;
use Spatie\LaravelSettings\Settings;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Initialize General Settings
    $generalSettings = $this->resolveSettings(GeneralSettings::class);
        $generalSettings->fill([
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
        ]);
        $generalSettings->save();

        // Initialize Company Settings
    $companySettings = $this->resolveSettings(CompanySettings::class);
        $companySettings->fill([
            'company_name' => 'Your Company Name',
            'company_registration_number' => null,
            'company_tax_number' => null,
            'company_phone' => null,
            'company_email' => config('mail.from.address', 'admin@example.com'),
            'company_website' => null,
            'company_address_line_1' => null,
            'company_address_line_2' => null,
            'company_city' => null,
            'company_state' => null,
            'company_postal_code' => null,
            'company_country' => 'MY',
            'company_logo' => null,
            'company_description' => null,
        ]);
        $companySettings->save();

        // Initialize Financial Settings
    $financialSettings = $this->resolveSettings(FinancialSettings::class);
        $financialSettings->fill([
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
        ]);
        $financialSettings->save();

        // Initialize UOM Settings
    $uomSettings = $this->resolveSettings(UomSettings::class);
        $uomSettings->fill([
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
        ]);
        $uomSettings->save();

        // Initialize Notification Settings
    $notificationSettings = $this->resolveSettings(NotificationSettings::class);
        $notificationSettings->fill([
            'email_notifications' => true,
            'sms_notifications' => false,
            'browser_notifications' => true,
            'notify_on_low_inventory' => true,
            'notify_on_order_updates' => true,
            'notify_on_payment_received' => true,
            'notify_on_invoice_overdue' => true,
            'notify_on_system_errors' => true,
            'admin_email' => config('mail.from.address', 'admin@example.com'),
            'admin_phone' => null,
            'notification_batch_size' => 50,
            'notification_frequency' => 'immediate',
        ]);
        $notificationSettings->save();

        $this->command->info('Settings have been initialized with default values.');
    }

    /**
     * @template T of Settings
     *
     * @param  class-string<T>  $settingsClass
     * @return T
     */
    protected function resolveSettings(string $settingsClass): Settings
    {
        try {
            return app($settingsClass);
        } catch (MissingSettings $exception) {
            /** @var Settings $settingsInstance */
            $settingsInstance = $settingsClass::fake($settingsClass::defaults(), loadMissingValues: false);

            return $settingsInstance;
        }
    }
}