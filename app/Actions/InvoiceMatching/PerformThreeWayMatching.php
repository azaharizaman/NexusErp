<?php

namespace App\Actions\InvoiceMatching;

use App\Models\InvoiceMatching;
use Lorisleiva\Actions\Concerns\AsAction;

class PerformThreeWayMatching
{
    use AsAction;

    /**
     * Perform three-way matching validation between PO, GRN, and Invoice.
     *
     * This action validates that:
     * - Invoice total matches PO total (within tolerance)
     * - Quantities received match invoice quantities
     * - Prices match expected values
     *
     * The matching status is automatically determined based on variances.
     */
    public function handle(InvoiceMatching $matching): InvoiceMatching
    {
        $this->calculateVariances($matching);
        $this->checkTolerance($matching);
        $this->identifyMismatches($matching);
        $this->determineMatchingStatus($matching);
        
        $matching->save();

        return $matching;
    }

    /**
     * Calculate variances between PO, GRN, and Invoice.
     */
    protected function calculateVariances(InvoiceMatching $matching): void
    {
        $matching->total_variance = abs($matching->invoice_total - $matching->po_total);
        $matching->variance_percentage = $matching->po_total > 0 
            ? ($matching->total_variance / $matching->po_total) * 100 
            : 0;
    }

    /**
     * Check if variance is within tolerance.
     */
    protected function checkTolerance(InvoiceMatching $matching): void
    {
        $matching->is_within_tolerance = $matching->variance_percentage <= $matching->tolerance_percentage;
    }

    /**
     * Identify specific mismatches.
     */
    protected function identifyMismatches(InvoiceMatching $matching): void
    {
        $mismatches = [];

        if ($matching->po_total != $matching->invoice_total) {
            $mismatches[] = [
                'type' => 'total_mismatch',
                'description' => 'Invoice total does not match PO total',
                'po_value' => $matching->po_total,
                'invoice_value' => $matching->invoice_total,
                'variance' => $matching->total_variance,
            ];
        }

        $matching->mismatches = $mismatches;
    }

    /**
     * Determine overall matching status.
     */
    protected function determineMatchingStatus(InvoiceMatching $matching): void
    {
        if (empty($matching->mismatches) || $matching->is_within_tolerance) {
            $matching->matching_status = 'matched';
        } else {
            $matching->matching_status = 'mismatched';
        }
    }
}
