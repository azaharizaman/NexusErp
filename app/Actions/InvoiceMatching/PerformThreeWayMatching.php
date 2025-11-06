<?php

namespace App\Actions\InvoiceMatching;

use App\Models\InvoiceMatching;
use App\Models\SupplierInvoice;
use Lorisleiva\Actions\Concerns\AsAction;

class PerformThreeWayMatching
{
    use AsAction;

    /**
     * Perform three-way matching validation between PO, GRN, and Invoice.
     *
     * This action delegates to ValidateThreeWayMatch for detailed validation.
     * It can accept either an InvoiceMatching record or a SupplierInvoice.
     *
     * @param  InvoiceMatching|SupplierInvoice  $matchingOrInvoice
     * @param  float  $tolerancePercentage
     * @return InvoiceMatching
     */
    public function handle(
        InvoiceMatching|SupplierInvoice $matchingOrInvoice,
        float $tolerancePercentage = 5.0
    ): InvoiceMatching {
        // If InvoiceMatching is passed, get the invoice
        if ($matchingOrInvoice instanceof InvoiceMatching) {
            $invoice = $matchingOrInvoice->supplierInvoice;
            $tolerancePercentage = $matchingOrInvoice->tolerance_percentage ?? $tolerancePercentage;
        } else {
            $invoice = $matchingOrInvoice;
        }

        // Use ValidateThreeWayMatch action for detailed validation
        return ValidateThreeWayMatch::run($invoice, $tolerancePercentage);
    }
}
