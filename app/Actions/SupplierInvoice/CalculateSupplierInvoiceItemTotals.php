<?php

namespace App\Actions\SupplierInvoice;

use App\Models\SupplierInvoiceItem;
use Lorisleiva\Actions\Concerns\AsAction;

class CalculateSupplierInvoiceItemTotals
{
    use AsAction;

    /**
     * Calculate totals for a supplier invoice item.
     *
     * Calculation formula:
     * 1. Base Total = quantity × unit_price
     * 2. Discount Amount = Base Total × (discount_percent / 100)
     * 3. After Discount = Base Total - Discount Amount
     * 4. Tax Amount = After Discount × (tax_rate / 100)
     * 5. Line Total = After Discount + Tax Amount
     */
    public function handle(SupplierInvoiceItem $item): SupplierInvoiceItem
    {
        $baseTotal = $item->quantity * $item->unit_price;
        $item->discount_amount = $baseTotal * ($item->discount_percent / 100);
        $afterDiscount = $baseTotal - $item->discount_amount;
        $item->tax_amount = $afterDiscount * ($item->tax_rate / 100);
        $item->line_total = $afterDiscount + $item->tax_amount;

        $item->save();

        return $item;
    }
}
