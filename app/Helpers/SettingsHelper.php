<?php

namespace App\Helpers;

class SettingsHelper
{
    /**
     * Resolve the singleton instance from the container.
     */
    protected static function instance(): self
    {
        return app(self::class);
    }

    /**
     * Forward static calls to the singleton instance for backwards compatibility.
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        return self::instance()->$name(...$arguments);
    }

    /**
     * Retrieve a configuration subset by key.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return config("nexus-settings.$key", $default);
    }

    /**
     * Retrieve all settings grouped by category.
     */
    public function all(): array
    {
        return config('nexus-settings', []);
    }

    /**
     * Retrieve general settings as an associative array.
     */
    public function general(): array
    {
        return $this->get('general', []);
    }

    /**
     * Retrieve company settings as an associative array.
     */
    public function company(): array
    {
        return $this->get('company', []);
    }

    /**
     * Retrieve financial settings as an associative array.
     */
    public function financial(): array
    {
        return $this->get('financial', []);
    }

    /**
     * Retrieve unit of measurement settings as an associative array.
     */
    public function uom(): array
    {
        return $this->get('uom', []);
    }

    /**
     * Retrieve notification settings as an associative array.
     */
    public function notifications(): array
    {
        return $this->get('notifications', []);
    }

    /**
     * Derived general settings helpers.
     */
    public function appName(): string
    {
        return (string) data_get($this->general(), 'app_name', config('app.name', 'NexusERP'));
    }

    public function appDescription(): string
    {
        return (string) data_get($this->general(), 'app_description', '');
    }

    public function timezone(): string
    {
        return (string) data_get($this->general(), 'timezone', config('app.timezone', 'UTC'));
    }

    public function isMaintenanceMode(): bool
    {
        return (bool) data_get($this->general(), 'maintenance_mode', false);
    }

    public function maintenanceMessage(): ?string
    {
        $message = data_get($this->general(), 'maintenance_message');

        return $message === '' ? null : $message;
    }

    /**
     * Derived company settings helpers.
     */
    public function companyName(): string
    {
        return (string) data_get($this->company(), 'company_name', '');
    }

    /**
     * Derived financial settings helpers.
     */
    public function defaultCurrency(): string
    {
        return (string) data_get($this->financial(), 'default_currency', 'USD');
    }

    public function currencySymbol(): string
    {
        return (string) data_get($this->financial(), 'currency_symbol', '$');
    }

    public function formatCurrency(float $amount): string
    {
        $financial = $this->financial();

        $formattedAmount = number_format(
            $amount,
            (int) data_get($financial, 'decimal_places', 2),
            (string) data_get($financial, 'decimal_separator', '.'),
            (string) data_get($financial, 'thousands_separator', ',')
        );

        $position = data_get($financial, 'currency_position', 'before');
        $symbol = (string) data_get($financial, 'currency_symbol', '$');

        return $position === 'before'
            ? $symbol.$formattedAmount
            : $formattedAmount.$symbol;
    }

    /**
     * Derived unit of measurement helpers.
     */
    public function defaultWeightUnit(): string
    {
        return (string) data_get($this->uom(), 'default_weight_unit', 'KG');
    }

    public function defaultLengthUnit(): string
    {
        return (string) data_get($this->uom(), 'default_length_unit', 'M');
    }

    public function defaultVolumeUnit(): string
    {
        return (string) data_get($this->uom(), 'default_volume_unit', 'L');
    }

    /**
     * Derived notification helpers.
     */
    public function emailNotificationsEnabled(): bool
    {
        return (bool) data_get($this->notifications(), 'email_notifications', true);
    }

    public function adminEmail(): string
    {
        return (string) data_get($this->notifications(), 'admin_email', 'admin@example.com');
    }
}
