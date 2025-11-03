<?php

namespace App\Filament\Pages;

use App\Settings\NotificationSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageNotificationSettings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBell;

    protected static string $settings = NotificationSettings::class;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 5;

    protected static ?string $title = 'Notification Settings';

    protected static ?string $navigationLabel = 'Notifications';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('email_notifications')
                    ->required(),
                Toggle::make('sms_notifications')
                    ->required(),
                Toggle::make('browser_notifications')
                    ->required(),
                Toggle::make('notify_on_low_inventory')
                    ->required(),
                Toggle::make('notify_on_order_updates')
                    ->required(),
                Toggle::make('notify_on_payment_received')
                    ->required(),
                Toggle::make('notify_on_invoice_overdue')
                    ->required(),
                Toggle::make('notify_on_system_errors')
                    ->required(),
                TextInput::make('admin_email')
                    ->email()
                    ->required(),
                TextInput::make('admin_phone')
                    ->tel(),
                TextInput::make('notification_batch_size')
                    ->numeric()
                    ->integer()
                    ->required(),
                TextInput::make('notification_frequency')
                    ->required(),
            ]);
    }
}
