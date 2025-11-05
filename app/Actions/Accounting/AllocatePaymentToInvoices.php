<?php

namespace App\Actions\Accounting;

use App\Models\PaymentReceipt;
use App\Models\PaymentReceiptAllocation;
use App\Models\SalesInvoice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class AllocatePaymentToInvoices
{
    use AsAction;

    /**
     * Allocate a payment receipt to one or more sales invoices.
     *
     * This action:
     * - Creates payment_receipt_allocations records
     * - Updates payment allocated_amount and unallocated_amount
     * - Updates invoice paid_amount and outstanding_amount
     * - Updates invoice payment status (issued → partially_paid → paid)
     *
     * @param  PaymentReceipt  $payment  The payment receipt to allocate
     * @param  array  $allocations  Array of ['invoice_id' => amount] pairs
     * @return Collection Collection of PaymentReceiptAllocation models created
     *
     * @throws \InvalidArgumentException If allocations exceed available amounts
     * @throws \LogicException If payment is not in cleared status
     */
    public function handle(PaymentReceipt $payment, array $allocations): Collection
    {
        // Validate payment status
        if ($payment->status !== 'cleared') {
            throw new \LogicException(
                "Cannot allocate payment {$payment->receipt_number} - payment must be cleared, current status: {$payment->status}"
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

                $invoice = SalesInvoice::findOrFail($invoiceId);

                // Validate invoice belongs to same customer
                if ($invoice->customer_id !== $payment->customer_id) {
                    throw new \InvalidArgumentException(
                        "Invoice {$invoice->invoice_number} does not belong to the same customer as payment {$payment->receipt_number}"
                    );
                }

                // Validate invoice is not fully paid or cancelled
                if ($invoice->status === 'paid') {
                    throw new \InvalidArgumentException(
                        "Invoice {$invoice->invoice_number} is already fully paid"
                    );
                }

                if ($invoice->status === 'cancelled') {
                    throw new \InvalidArgumentException(
                        "Cannot allocate payment to cancelled invoice {$invoice->invoice_number}"
                    );
                }

                // Validate allocation doesn't exceed invoice outstanding amount
                if (bccomp($allocationAmount, $invoice->outstanding_amount, 4) > 0) {
                    throw new \InvalidArgumentException(
                        "Allocation amount {$allocationAmount} exceeds invoice {$invoice->invoice_number} outstanding amount {$invoice->outstanding_amount}"
                    );
                }

                // Create allocation record
                $allocation = PaymentReceiptAllocation::create([
                    'payment_receipt_id' => $payment->id,
                    'sales_invoice_id' => $invoice->id,
                    'allocated_amount' => $allocationAmount,
                ]);

                $createdAllocations->push($allocation);

                // Update invoice payment tracking
                $invoice->paid_amount = bcadd($invoice->paid_amount, $allocationAmount, 4);
                $invoice->outstanding_amount = bcsub($invoice->outstanding_amount, $allocationAmount, 4);
                $invoice->updatePaymentStatus();
            }

            // Update payment allocation tracking
            $payment->recalculateAllocations();

            return $createdAllocations;
        });
    }

    /**
     * Allocate payment automatically to oldest unpaid invoices.
     *
     * This is a convenience method that allocates the payment to the customer's
     * oldest unpaid invoices first (FIFO).
     *
     * @param  PaymentReceipt  $payment  The payment receipt to allocate
     * @return Collection Collection of PaymentReceiptAllocation models created
     */
    public function handleAutomatic(PaymentReceipt $payment): Collection
    {
        // Get unpaid invoices for the customer, ordered by invoice date (oldest first)
        $unpaidInvoices = SalesInvoice::query()
            ->where('customer_id', $payment->customer_id)
            ->where('company_id', $payment->company_id)
            ->unpaid()
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
        return 'Allocate a payment receipt to sales invoices';
    }
}
