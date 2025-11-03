<?php

namespace App\Providers\Filament;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class PurchaseModulePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('purchase-module')
            ->path('purchase-module')
            ->brandName('NexusERP - Purchase Module')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->navigationGroups([
                'Procurement Setup',
                'Requisition Management',
                'Sourcing & Ordering',
                'Receiving & Invoicing',
                'Payments & Settlements',
                'Procurement Insights',
                'Administration & Policy',
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
            ])
            ->discoverResources(in: app_path('Filament/PurchaseModule/Resources'), for: 'App\Filament\PurchaseModule\Resources')
            ->discoverPages(in: app_path('Filament/PurchaseModule/Pages'), for: 'App\Filament\PurchaseModule\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/PurchaseModule/Widgets'), for: 'App\Filament\PurchaseModule\Widgets')
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
