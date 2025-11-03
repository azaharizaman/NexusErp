<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class NotificationSettings extends Settings
{
    public bool $email_notifications;
    public bool $sms_notifications;
    public bool $browser_notifications;
    public bool $notify_on_low_inventory;
    public bool $notify_on_order_updates;
    public bool $notify_on_payment_received;
    public bool $notify_on_invoice_overdue;
    public bool $notify_on_system_errors;
    public string $admin_email;
    public ?string $admin_phone;
    public int $notification_batch_size;
    public string $notification_frequency;

    public static function group(): string
    {
        return 'notifications';
    }

    public static function defaults(): array
    {
        return [
            'email_notifications' => true,
            'sms_notifications' => false,
            'browser_notifications' => true,
            'notify_on_low_inventory' => true,
            'notify_on_order_updates' => true,
            'notify_on_payment_received' => true,
            'notify_on_invoice_overdue' => true,
            'notify_on_system_errors' => true,
            'admin_email' => config('mail.from.address', ''),
            'admin_phone' => null,
            'notification_batch_size' => 50,
            'notification_frequency' => 'immediate',
        ];
    }
}