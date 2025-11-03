<?php

namespace App\Filament\PurchaseModule\Widgets;

use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;
use App\Models\RequestForQuotation;
use App\Models\PaymentVoucher;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProcurementStatsWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $pendingPRs = PurchaseRequest::where('status', 'submitted')->count();
        $openPOs = PurchaseOrder::whereIn('status', ['draft', 'approved', 'issued'])->count();
        $openRFQs = RequestForQuotation::whereIn('status', ['draft', 'sent'])->count();
        $pendingPayments = PaymentVoucher::whereIn('status', ['draft', 'pending_approval', 'approved'])->count();

        return [
            Stat::make('Pending Purchase Requests', $pendingPRs)
                ->description('Awaiting approval')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),
            Stat::make('Open Purchase Orders', $openPOs)
                ->description('Not yet closed')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),
            Stat::make('Open RFQs', $openRFQs)
                ->description('Awaiting quotations')
                ->descriptionIcon('heroicon-m-document-magnifying-glass')
                ->color('success'),
            Stat::make('Pending Payments', $pendingPayments)
                ->description('Awaiting payment')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('danger'),
        ];
    }
}
