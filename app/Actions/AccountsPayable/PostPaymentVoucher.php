<?php

namespace App\Actions\AccountsPayable;

use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\PaymentVoucher;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class PostPaymentVoucher
{
    use AsAction;

    /**
     * Post payment voucher to the General Ledger.
     *
     * This creates a journal entry with:
     * - Debit: Accounts Payable (liability account)
     * - Credit: Cash/Bank Account (asset account based on payment method)
     *
     * Also updates the supplier invoice outstanding amounts for all allocated payments.
     *
     * @param  PaymentVoucher  $voucher  The payment voucher to post
     * @param  int  $cashAccountId  The Cash/Bank account ID (based on payment method)
     * @param  int  $apAccountId  The Accounts Payable account ID
     * @return JournalEntry The created journal entry
     *
     * @throws \InvalidArgumentException If payment is not in paid status
     * @throws \LogicException If payment is already posted to GL or has no invoice allocation
     * @throws \RuntimeException If fiscal year/period not found or not open
     */
    public function handle(
        PaymentVoucher $voucher,
        int $cashAccountId,
        int $apAccountId
    ): JournalEntry {
        // Validate payment status - must be paid
        if ($voucher->latestStatus() !== 'paid') {
            throw new \InvalidArgumentException(
                "Cannot post payment voucher {$voucher->voucher_number} - voucher must be paid, current status: " . $voucher->latestStatus()
            );
        }

        // Check if already posted
        if ($voucher->is_posted_to_gl) {
            throw new \LogicException(
                "Payment voucher {$voucher->voucher_number} is already posted to GL (Journal Entry: {$voucher->journal_entry_id})"
            );
        }

        // Validate payment has a supplier invoice allocation
        if (!$voucher->supplier_invoice_id) {
            throw new \LogicException(
                "Cannot post payment voucher {$voucher->voucher_number} - no supplier invoice allocated. Payment must be allocated to an invoice before posting."
            );
        }

        // Determine fiscal year and period from payment date
        $fiscalYear = \App\Models\FiscalYear::query()
            ->where('company_id', $voucher->company_id)
            ->where('start_date', '<=', $voucher->payment_date)
            ->where('end_date', '>=', $voucher->payment_date)
            ->first();

        if (!$fiscalYear) {
            throw new \RuntimeException(
                "No fiscal year found for payment date {$voucher->payment_date->format('Y-m-d')}"
            );
        }

        $accountingPeriod = \App\Models\AccountingPeriod::query()
            ->where('fiscal_year_id', $fiscalYear->id)
            ->where('start_date', '<=', $voucher->payment_date)
            ->where('end_date', '>=', $voucher->payment_date)
            ->first();

        if (!$accountingPeriod) {
            throw new \RuntimeException(
                "No accounting period found for payment date {$voucher->payment_date->format('Y-m-d')}"
            );
        }

        // Validate accounting period is open
        if ($accountingPeriod->status !== 'open') {
            throw new \RuntimeException(
                "Cannot post payment - accounting period {$accountingPeriod->period_name} is not open"
            );
        }

        return DB::transaction(function () use ($voucher, $cashAccountId, $apAccountId, $fiscalYear, $accountingPeriod) {
            // Create journal entry
            $journalEntry = JournalEntry::create([
                'company_id' => $voucher->company_id,
                'fiscal_year_id' => $fiscalYear->id,
                'accounting_period_id' => $accountingPeriod->id,
                'entry_type' => 'automatic',
                'entry_date' => $voucher->payment_date,
                'currency_id' => $voucher->currency_id,
                'exchange_rate' => 1, // Payment vouchers are in base currency
                'description' => "Payment Voucher {$voucher->voucher_number} - Supplier: {$voucher->supplier->name} - Method: {$voucher->payment_method}",
                'reference_type' => 'payment_voucher',
                'reference_id' => $voucher->id,
                'reference_number' => $voucher->voucher_number,
                'created_by' => auth()->id(),
            ]);

            // Debit: Accounts Payable
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $apAccountId,
                'debit' => $voucher->amount,
                'credit' => '0.0000',
                'description' => "AP - Payment {$voucher->voucher_number}",
                'created_by' => auth()->id(),
            ]);

            // Credit: Cash/Bank Account
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $cashAccountId,
                'debit' => '0.0000',
                'credit' => $voucher->amount,
                'description' => "Cash/Bank - Payment {$voucher->voucher_number}",
                'created_by' => auth()->id(),
            ]);

            // Update journal entry totals
            $journalEntry->updateTotals();

            // Validate the journal entry is balanced
            if (!$journalEntry->isBalanced()) {
                throw new \RuntimeException(
                    "Journal entry is not balanced: Debit={$journalEntry->total_debit}, Credit={$journalEntry->total_credit}"
                );
            }

            // Post the journal entry to GL
            $journalEntry->post();

            // Update payment voucher GL posting status
            $voucher->update([
                'journal_entry_id' => $journalEntry->id,
                'is_posted_to_gl' => true,
                'posted_to_gl_at' => now(),
            ]);

            // Update supplier invoice outstanding amount
            if ($voucher->supplierInvoice) {
                $invoice = $voucher->supplierInvoice;
                $invoice->paid_amount = bcadd($invoice->paid_amount, $voucher->amount, 4);
                $invoice->outstanding_amount = bcsub($invoice->total_amount, $invoice->paid_amount, 4);
                $invoice->save();

                // Update invoice status based on payment
                if ($invoice->outstanding_amount <= 0) {
                    $invoice->setStatus('paid', 'Invoice fully paid');
                } elseif ($invoice->paid_amount > 0) {
                    $invoice->setStatus('partially_paid', 'Invoice partially paid');
                }
            }

            return $journalEntry;
        });
    }

    /**
     * Get the console command description.
     */
    public function getCommandDescription(): string
    {
        return 'Post a payment voucher to the General Ledger';
    }
}
