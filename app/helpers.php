<?php

use App\Helpers\SettingsHelper;

if (!function_exists('settings')) {
    /**
     * Get settings helper instance
     *
     * @return SettingsHelper
     */
    function settings(): SettingsHelper
    {
        return app('settings');
    }
}

if (!function_exists('app_name')) {
    /**
     * Get application name from settings
     *
     * @return string
     */
    function app_name(): string
    {
        return SettingsHelper::appName();
    }
}

if (!function_exists('company_name')) {
    /**
     * Get company name from settings
     *
     * @return string
     */
    function company_name(): string
    {
        return SettingsHelper::companyName();
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format currency amount using settings
     *
     * @param float $amount
     * @return string
     */
    function format_currency(float $amount): string
    {
        return SettingsHelper::formatCurrency($amount);
    }
}

if (!function_exists('default_currency')) {
    /**
     * Get default currency from settings
     *
     * @return string
     */
    function default_currency(): string
    {
        return SettingsHelper::defaultCurrency();
    }
}

if (!function_exists('is_maintenance_mode')) {
    /**
     * Check if maintenance mode is enabled
     *
     * @return bool
     */
    function is_maintenance_mode(): bool
    {
        return SettingsHelper::isMaintenanceMode();
    }
}