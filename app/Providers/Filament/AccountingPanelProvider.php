<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Filament\Actions\Action;
use Filament\Pages\Dashboard;
use Filament\Facades\Filament;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class AccountingPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('accounting')
            ->path('accounting')
            ->brandName('NexusERP - Accounting Module')
            ->colors([
                'primary' => Color::Green,
            ])
            ->navigationGroups([
                'Chart of Accounts & Setup',
                'General Ledger',
                'Accounts Receivable',
                'Accounts Payable',
                'Banking & Cash',
                'Financial Reports',
                'Budgeting & Planning',
                'Fixed Assets',
                'Multi-Currency',
                'Consolidation',
                'Dimensions & Analytics',
                'Tax Management',
                'Audit & Compliance',
                'Administration',
            ])
            ->userMenuItems([
                Action::make('switchToNexusPanel')
                    ->label('Nexus Panel')
                    ->icon('heroicon-o-squares-2x2')
                    ->visible(fn (): bool => Filament::getCurrentPanel()?->getId() !== 'nexus')
                    ->url(function (): string {
                        $panel = Filament::getPanel('nexus');

                        return $panel->getUrl() ?? url($panel->getPath());
                    }),
                Action::make('switchToPurchasePanel')
                    ->label('Purchase Module')
                    ->icon('heroicon-o-shopping-cart')
                    ->visible(fn (): bool => Filament::getCurrentPanel()?->getId() !== 'purchase-module')
                    ->url(function (): string {
                        $panel = Filament::getPanel('purchase-module');

                        return $panel->getUrl() ?? url($panel->getPath());
                    }),
            ])
            ->discoverResources(in: app_path('Filament/Accounting/Resources'), for: 'App\Filament\Accounting\Resources')
            ->discoverPages(in: app_path('Filament/Accounting/Pages'), for: 'App\Filament\Accounting\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Accounting/Widgets'), for: 'App\Filament\Accounting\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
