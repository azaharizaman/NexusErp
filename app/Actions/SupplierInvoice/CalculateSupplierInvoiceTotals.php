<?php

namespace App\Actions\SupplierInvoice;

use App\Models\SupplierInvoice;
use Lorisleiva\Actions\Concerns\AsAction;

class CalculateSupplierInvoiceTotals
{
    use AsAction;

    /**
     * Calculate totals for a supplier invoice from its line items.
     *
     * Calculation:
     * 1. Subtotal = Sum of all item line_total
     * 2. Tax Amount = Sum of all item tax_amount
     * 3. Total Amount = Subtotal + Tax Amount - Discount Amount
     * 4. Outstanding Amount = Total Amount - Paid Amount
     */
    public function handle(SupplierInvoice $invoice): SupplierInvoice
    {
        $invoice->subtotal = $invoice->items->sum('line_total');
        $invoice->tax_amount = $invoice->items->sum('tax_amount');
        $invoice->total_amount = $invoice->subtotal + $invoice->tax_amount - $invoice->discount_amount;
        $invoice->outstanding_amount = $invoice->total_amount - $invoice->paid_amount;
        
        $invoice->save();

        return $invoice;
    }
}
