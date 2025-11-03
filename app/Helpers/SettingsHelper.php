<?php

namespace App\Helpers;

use App\Settings\GeneralSettings;
use App\Settings\CompanySettings;
use App\Settings\FinancialSettings;
use App\Settings\UomSettings;
use App\Settings\NotificationSettings;

class SettingsHelper
{
    /**
     * Get general settings instance
     */
    public static function general(): GeneralSettings
    {
        return app(GeneralSettings::class);
    }

    /**
     * Get company settings instance
     */
    public static function company(): CompanySettings
    {
        return app(CompanySettings::class);
    }

    /**
     * Get financial settings instance
     */
    public static function financial(): FinancialSettings
    {
        return app(FinancialSettings::class);
    }

    /**
     * Get UOM settings instance
     */
    public static function uom(): UomSettings
    {
        return app(UomSettings::class);
    }

    /**
     * Get notification settings instance
     */
    public static function notifications(): NotificationSettings
    {
        return app(NotificationSettings::class);
    }

    /**
     * Get application name from settings
     */
    public static function appName(): string
    {
        return self::general()->app_name;
    }

    /**
     * Get application description from settings
     */
    public static function appDescription(): string
    {
        return self::general()->app_description;
    }

    /**
     * Get default currency from settings
     */
    public static function defaultCurrency(): string
    {
        return self::financial()->default_currency;
    }

    /**
     * Get currency symbol from settings
     */
    public static function currencySymbol(): string
    {
        return self::financial()->currency_symbol;
    }

    /**
     * Format currency amount
     */
    public static function formatCurrency(float $amount): string
    {
        $settings = self::financial();
        
        $formattedAmount = number_format(
            $amount,
            $settings->decimal_places,
            $settings->decimal_separator,
            $settings->thousands_separator
        );

        return $settings->currency_position === 'before'
            ? $settings->currency_symbol . $formattedAmount
            : $formattedAmount . $settings->currency_symbol;
    }

    /**
     * Get company name from settings
     */
    public static function companyName(): string
    {
        return self::company()->company_name;
    }

    /**
     * Get default timezone from settings
     */
    public static function timezone(): string
    {
        return self::general()->timezone;
    }

    /**
     * Check if maintenance mode is enabled
     */
    public static function isMaintenanceMode(): bool
    {
        return self::general()->maintenance_mode;
    }

    /**
     * Get maintenance message
     */
    public static function maintenanceMessage(): ?string
    {
        return self::general()->maintenance_message;
    }

    /**
     * Get default weight unit
     */
    public static function defaultWeightUnit(): string
    {
        return self::uom()->default_weight_unit;
    }

    /**
     * Get default length unit
     */
    public static function defaultLengthUnit(): string
    {
        return self::uom()->default_length_unit;
    }

    /**
     * Get default volume unit
     */
    public static function defaultVolumeUnit(): string
    {
        return self::uom()->default_volume_unit;
    }

    /**
     * Check if email notifications are enabled
     */
    public static function emailNotificationsEnabled(): bool
    {
        return self::notifications()->email_notifications;
    }

    /**
     * Get admin email for notifications
     */
    public static function adminEmail(): string
    {
        return self::notifications()->admin_email;
    }
}