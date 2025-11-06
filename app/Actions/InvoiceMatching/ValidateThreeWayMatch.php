<?php

namespace App\Actions\InvoiceMatching;

use App\Models\GoodsReceivedNote;
use App\Models\InvoiceMatching;
use App\Models\PurchaseOrder;
use App\Models\SupplierInvoice;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Lorisleiva\Actions\Concerns\AsAction;

class ValidateThreeWayMatch
{
    use AsAction;

    /**
     * Perform detailed three-way matching validation.
     *
     * This action validates invoice against PO and GRN at item level:
     * - Compares quantities between PO, GRN, and Invoice
     * - Compares prices between PO and Invoice
     * - Calculates detailed variance amounts
     * - Generates comprehensive match report
     *
     * @param  SupplierInvoice  $invoice  The supplier invoice to validate
     * @param  float  $tolerancePercentage  Tolerance percentage for variances (default: 5%)
     * @return InvoiceMatching The matching record with validation results
     */
    public function handle(
        SupplierInvoice $invoice,
        float $tolerancePercentage = 5.0
    ): InvoiceMatching {
        // Validate that invoice has a PO
        if (!$invoice->purchase_order_id) {
            throw new InvalidArgumentException('Invoice must be linked to a Purchase Order for three-way matching');
        }

        $purchaseOrder = $invoice->purchaseOrder()->with('items.uom')->first();

        if (!$purchaseOrder) {
            throw new InvalidArgumentException('Purchase Order not found for invoice ' . $invoice->invoice_number);
        }

        // Load GRN if exists
        $grn = null;
        if ($invoice->goods_received_note_id) {
            $grn = $invoice->goodsReceivedNote()->with('items.uom')->first();
        }

        // Create or update matching record
        $matching = InvoiceMatching::firstOrNew([
            'supplier_invoice_id' => $invoice->id,
        ]);

        $matching->fill([
            'company_id' => $invoice->company_id,
            'purchase_order_id' => $invoice->purchase_order_id,
            'goods_received_note_id' => $invoice->goods_received_note_id,
            'po_total' => $purchaseOrder->total_amount,
            'grn_total' => $grn?->items->sum(fn ($item) => $item->received_quantity * 
                ($purchaseOrder->items->firstWhere('id', $item->purchase_order_item_id)?->unit_price ?? 0)),
            'invoice_total' => $invoice->total_amount,
            'tolerance_percentage' => $tolerancePercentage,
        ]);

        // Perform item-level validation
        $this->validateItems($matching, $invoice, $purchaseOrder, $grn);

        // Calculate variances
        $this->calculateVariances($matching);

        // Check tolerance
        $this->checkTolerance($matching);

        // Determine matching status
        $this->determineMatchingStatus($matching);

        $matching->save();

        return $matching;
    }

    /**
     * Validate items between PO, GRN, and Invoice.
     */
    protected function validateItems(
        InvoiceMatching $matching,
        SupplierInvoice $invoice,
        PurchaseOrder $purchaseOrder,
        ?GoodsReceivedNote $grn
    ): void {
        $mismatches = [];
        $totalQuantityVariance = 0;
        $totalPriceVariance = 0;

        // Load invoice items with relationships
        $invoiceItems = $invoice->items()->with(['purchaseOrderItem', 'goodsReceivedNoteItem'])->get();

        foreach ($invoiceItems as $invoiceItem) {
            $poItem = $invoiceItem->purchaseOrderItem;

            if (!$poItem) {
                $mismatches[] = [
                    'type' => 'item_not_in_po',
                    'item_code' => $invoiceItem->item_code,
                    'item_description' => $invoiceItem->item_description,
                    'invoice_quantity' => (float) $invoiceItem->quantity,
                    'invoice_price' => (float) $invoiceItem->unit_price,
                    'message' => 'Invoice item not found in Purchase Order',
                ];
                continue;
            }

            // Quantity validation
            $quantityMismatch = $this->validateQuantity($invoiceItem, $poItem, $grn);
            if ($quantityMismatch) {
                $mismatches[] = $quantityMismatch;
                $totalQuantityVariance += abs($quantityMismatch['quantity_variance']);
            }

            // Price validation
            $priceMismatch = $this->validatePrice($invoiceItem, $poItem);
            if ($priceMismatch) {
                $mismatches[] = $priceMismatch;
                $totalPriceVariance += abs($priceMismatch['price_variance']);
            }
        }

        $matching->mismatches = $mismatches;
        $matching->quantity_variance = $totalQuantityVariance;
        $matching->price_variance = $totalPriceVariance;
    }

    /**
     * Validate quantity between invoice, PO, and GRN.
     */
    protected function validateQuantity(
        $invoiceItem,
        $poItem,
        ?GoodsReceivedNote $grn
    ): ?array {
        $invoiceQty = (float) $invoiceItem->quantity;
        $poQty = (float) $poItem->quantity;

        // If GRN exists, compare with GRN quantity
        if ($grn && $invoiceItem->goods_received_note_item_id) {
            $grnItem = $invoiceItem->goodsReceivedNoteItem;
            if ($grnItem) {
                $grnQty = (float) $grnItem->received_quantity;

                if ($invoiceQty != $grnQty) {
                    return [
                        'type' => 'quantity_mismatch',
                        'item_code' => $invoiceItem->item_code,
                        'item_description' => $invoiceItem->item_description,
                        'po_quantity' => $poQty,
                        'grn_quantity' => $grnQty,
                        'invoice_quantity' => $invoiceQty,
                        'quantity_variance' => $invoiceQty - $grnQty,
                        'message' => 'Invoice quantity does not match GRN received quantity',
                    ];
                }
            }
        } else {
            // If no GRN, compare with PO quantity
            if ($invoiceQty > $poQty) {
                return [
                    'type' => 'quantity_exceeds_po',
                    'item_code' => $invoiceItem->item_code,
                    'item_description' => $invoiceItem->item_description,
                    'po_quantity' => $poQty,
                    'invoice_quantity' => $invoiceQty,
                    'quantity_variance' => $invoiceQty - $poQty,
                    'message' => 'Invoice quantity exceeds Purchase Order quantity',
                ];
            }
        }

        return null;
    }

    /**
     * Validate price between invoice and PO.
     */
    protected function validatePrice($invoiceItem, $poItem): ?array
    {
        $invoicePrice = (float) $invoiceItem->unit_price;
        $poPrice = (float) $poItem->unit_price;

        // Allow small floating point differences
        if (abs($invoicePrice - $poPrice) > 0.01) {
            $variance = $invoicePrice - $poPrice;
            $variancePercentage = $poPrice > 0 ? (abs($variance) / $poPrice) * 100 : 0;

            return [
                'type' => 'price_mismatch',
                'item_code' => $invoiceItem->item_code,
                'item_description' => $invoiceItem->item_description,
                'po_price' => $poPrice,
                'invoice_price' => $invoicePrice,
                'price_variance' => $variance,
                'variance_percentage' => round($variancePercentage, 2),
                'message' => 'Invoice unit price differs from Purchase Order price',
            ];
        }

        return null;
    }

    /**
     * Calculate overall variances.
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
     * Determine overall matching status based on mismatches.
     */
    protected function determineMatchingStatus(InvoiceMatching $matching): void
    {
        if (empty($matching->mismatches)) {
            $matching->matching_status = InvoiceMatching::STATUS_MATCHED;
            return;
        }

        $hasQuantityMismatch = false;
        $hasPriceMismatch = false;

        foreach ($matching->mismatches as $mismatch) {
            $type = $mismatch['type'] ?? '';
            if (in_array($type, ['quantity_mismatch', 'quantity_exceeds_po'])) {
                $hasQuantityMismatch = true;
            }
            if ($type === 'price_mismatch') {
                $hasPriceMismatch = true;
            }
        }

        // Determine primary status
        if ($hasQuantityMismatch && $hasPriceMismatch) {
            // Both mismatches exist, prioritize based on variance size
            $matching->matching_status = $matching->quantity_variance > $matching->price_variance
                ? InvoiceMatching::STATUS_QUANTITY_MISMATCH
                : InvoiceMatching::STATUS_PRICE_MISMATCH;
        } elseif ($hasQuantityMismatch) {
            $matching->matching_status = InvoiceMatching::STATUS_QUANTITY_MISMATCH;
        } elseif ($hasPriceMismatch) {
            $matching->matching_status = InvoiceMatching::STATUS_PRICE_MISMATCH;
        } else {
            $matching->matching_status = InvoiceMatching::STATUS_NOT_MATCHED;
        }

        // Override to matched if within tolerance
        if ($matching->is_within_tolerance) {
            $matching->matching_status = InvoiceMatching::STATUS_MATCHED;
        }
    }
}
