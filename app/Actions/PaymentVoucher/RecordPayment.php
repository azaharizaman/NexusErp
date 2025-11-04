<?php

namespace App\Actions\PaymentVoucher;

use App\Actions\PayableLedger\CreateLedgerEntry;
use App\Models\PaymentVoucher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class RecordPayment
{
    use AsAction;

    /**
     * Record payment for a voucher and update related schedules and ledger.
     *
     * @param  PaymentVoucher  $voucher
     * @return PaymentVoucher
     */
    public function handle(PaymentVoucher $voucher): PaymentVoucher
    {
        if (! $voucher->canPay()) {
            throw new \Exception('Payment voucher cannot be paid in its current state.');
        }

        return DB::transaction(function () use ($voucher) {
            // Mark voucher as paid
            $voucher->paid_by = Auth::id();
            $voucher->paid_at = now();
            $voucher->save();

            $voucher->setStatus('paid', 'Payment recorded');

            // Create ledger entry for payment (credit)
            if ($voucher->supplier_invoice_id) {
                CreateLedgerEntry::run([
                    'company_id' => $voucher->company_id,
                    'supplier_id' => $voucher->supplier_id,
                    'supplier_invoice_id' => $voucher->supplier_invoice_id,
                    'payment_voucher_id' => $voucher->id,
                    'base_currency_id' => $voucher->currency_id,
                    'transaction_date' => $voucher->payment_date,
                    'transaction_type' => 'payment',
                    'credit_amount_base' => $voucher->amount,
                    'reference_number' => $voucher->voucher_number,
                    'description' => 'Payment recorded via voucher ' . $voucher->voucher_number,
                ]);
            }

            // Update related payment schedules
            $voucher->paymentSchedules()->pending()->each(function ($schedule) use ($voucher) {
                $schedule->paid_amount += $voucher->amount;
                $schedule->updateOutstanding();

                if ($schedule->outstanding_amount <= 0) {
                    $schedule->completed_at = now();
                    $schedule->setStatus('completed', 'Payment schedule completed');
                }

                $schedule->save();
            });

            // Update supplier invoice if linked
            if ($voucher->supplierInvoice) {
                $invoice = $voucher->supplierInvoice;
                $invoice->paid_amount += $voucher->amount;
                $invoice->outstanding_amount = $invoice->total_amount - $invoice->paid_amount;

                if ($invoice->outstanding_amount <= 0) {
                    $invoice->setStatus('paid', 'Invoice fully paid');
                }

                $invoice->save();
            }

            return $voucher->fresh();
        });
    }
}
