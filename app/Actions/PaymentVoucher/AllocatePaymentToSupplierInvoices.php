<?php

namespace App\Actions\PaymentVoucher;

use App\Models\PaymentVoucher;
use App\Models\PaymentVoucherAllocation;
use App\Models\SupplierInvoice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class AllocatePaymentToSupplierInvoices
{
    use AsAction;

    /**
     * Allocate a payment voucher to one or more supplier invoices.
     *
     * This action:
     * - Creates payment_voucher_allocations records
     * - Updates payment allocated_amount and unallocated_amount
     * - Updates invoice paid_amount and outstanding_amount
     * - Updates invoice payment status (draft → partially_paid → paid)
     *
     * @param  PaymentVoucher  $payment  The payment voucher to allocate
     * @param  array  $allocations  Array of ['invoice_id' => amount] pairs
     * @return Collection Collection of PaymentVoucherAllocation models created
     *
     * @throws \InvalidArgumentException If allocations exceed available amounts
     * @throws \LogicException If payment is on hold or not in correct status
     */
    public function handle(PaymentVoucher $payment, array $allocations): Collection
    {
        // Validate payment is not on hold
        if ($payment->is_on_hold) {
            throw new \LogicException(
                "Cannot allocate payment {$payment->voucher_number} - payment is on hold: {$payment->hold_reason}"
            );
        }

        // Validate payment status - should be submitted or approved
        $currentStatus = $payment->latestStatus();
        if (!in_array($currentStatus, ['submitted', 'approved'])) {
            throw new \LogicException(
                "Cannot allocate payment {$payment->voucher_number} - payment must be submitted or approved, current status: {$currentStatus}"
            );
        }

        // Calculate total allocation amount
        $totalAllocation = array_sum($allocations);

        // Validate total doesn't exceed unallocated amount
        if (bccomp($totalAllocation, $payment->unallocated_amount, 4) > 0) {
            throw new \InvalidArgumentException(
                "Total allocation amount {$totalAllocation} exceeds unallocated payment amount {$payment->unallocated_amount}"
            );
        }

        return DB::transaction(function () use ($payment, $allocations) {
            $createdAllocations = collect();

            foreach ($allocations as $invoiceId => $allocationAmount) {
                if ($allocationAmount <= 0) {
                    continue; // Skip zero or negative allocations
                }

                $invoice = SupplierInvoice::findOrFail($invoiceId);

                // Validate invoice belongs to same supplier
                if ($invoice->supplier_id !== $payment->supplier_id) {
                    throw new \InvalidArgumentException(
                        "Invoice {$invoice->invoice_number} does not belong to the same supplier as payment {$payment->voucher_number}"
                    );
                }

                // Validate invoice belongs to same company
                if ($invoice->company_id !== $payment->company_id) {
                    throw new \InvalidArgumentException(
                        "Invoice {$invoice->invoice_number} does not belong to the same company as payment {$payment->voucher_number}"
                    );
                }

                // Validate invoice is not fully paid
                if ($invoice->isFullyPaid()) {
                    throw new \InvalidArgumentException(
                        "Invoice {$invoice->invoice_number} is already fully paid"
                    );
                }

                // Validate invoice status is approved or partially_paid
                $invoiceStatus = $invoice->latestStatus();
                if (!in_array($invoiceStatus, ['approved', 'partially_paid'])) {
                    throw new \InvalidArgumentException(
                        "Invoice {$invoice->invoice_number} must be approved before payment can be allocated, current status: {$invoiceStatus}"
                    );
                }

                // Validate allocation doesn't exceed invoice outstanding amount
                if (bccomp($allocationAmount, $invoice->outstanding_amount, 4) > 0) {
                    throw new \InvalidArgumentException(
                        "Allocation amount {$allocationAmount} exceeds invoice {$invoice->invoice_number} outstanding amount {$invoice->outstanding_amount}"
                    );
                }

                // Create allocation record
                $allocation = PaymentVoucherAllocation::create([
                    'payment_voucher_id' => $payment->id,
                    'supplier_invoice_id' => $invoice->id,
                    'allocated_amount' => $allocationAmount,
                    'created_by' => Auth::id(),
                ]);

                $createdAllocations->push($allocation);

                // Update invoice payment tracking
                $invoice->recordPayment($allocationAmount);
            }

            // Update payment allocation tracking
            $payment->recalculateAllocations();

            return $createdAllocations;
        });
    }

    /**
     * Allocate payment automatically to oldest unpaid invoices (FIFO).
     *
     * This is a convenience method that allocates the payment to the supplier's
     * oldest unpaid invoices first.
     *
     * @param  PaymentVoucher  $payment  The payment voucher to allocate
     * @return Collection Collection of PaymentVoucherAllocation models created
     */
    public function handleAutomatic(PaymentVoucher $payment): Collection
    {
        // Get unpaid invoices for the supplier, ordered by invoice date (oldest first)
        $unpaidInvoices = SupplierInvoice::query()
            ->where('supplier_id', $payment->supplier_id)
            ->where('company_id', $payment->company_id)
            ->unpaid()
            ->whereIn('id', function ($query) {
                $query->select('model_id')
                    ->from('statuses')
                    ->where('model_type', SupplierInvoice::class)
                    ->whereIn('name', ['approved', 'partially_paid']);
            })
            ->orderBy('invoice_date')
            ->orderBy('invoice_number')
            ->get();

        $remainingAmount = $payment->unallocated_amount;
        $allocations = [];

        foreach ($unpaidInvoices as $invoice) {
            if (bccomp($remainingAmount, '0', 4) <= 0) {
                break; // No more payment to allocate
            }

            // Allocate the lesser of remaining payment amount or invoice outstanding amount
            $allocationAmount = bccomp($remainingAmount, $invoice->outstanding_amount, 4) > 0
                ? $invoice->outstanding_amount
                : $remainingAmount;

            $allocations[$invoice->id] = $allocationAmount;
            $remainingAmount = bcsub($remainingAmount, $allocationAmount, 4);
        }

        return $this->handle($payment, $allocations);
    }

    /**
     * Get the console command description.
     */
    public function getCommandDescription(): string
    {
        return 'Allocate a payment voucher to supplier invoices';
    }
}
