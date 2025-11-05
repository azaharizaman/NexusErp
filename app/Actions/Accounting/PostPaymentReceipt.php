<?php

namespace App\Actions\Accounting;

use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\PaymentReceipt;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class PostPaymentReceipt
{
    use AsAction;

    /**
     * Post payment receipt to the General Ledger.
     *
     * This creates a journal entry with:
     * - Debit: Cash/Bank Account (asset account based on payment method)
     * - Credit: Accounts Receivable (asset account)
     *
     * @param  PaymentReceipt  $payment  The payment receipt to post
     * @param  int  $cashAccountId  The Cash/Bank account ID (based on payment method)
     * @param  int  $arAccountId  The Accounts Receivable account ID
     * @return JournalEntry The created journal entry
     *
     * @throws \InvalidArgumentException If payment is not cleared
     * @throws \LogicException If payment is already posted to GL
     * @throws \RuntimeException If fiscal year/period not found
     */
    public function handle(
        PaymentReceipt $payment,
        int $cashAccountId,
        int $arAccountId
    ): JournalEntry {
        // Validate payment status
        if ($payment->status !== 'cleared') {
            throw new \InvalidArgumentException(
                "Cannot post payment {$payment->receipt_number} - payment must be cleared, current status: {$payment->status}"
            );
        }

        // Check if already posted
        if ($payment->is_posted_to_gl) {
            throw new \LogicException(
                "Payment {$payment->receipt_number} is already posted to GL (Journal Entry: {$payment->journal_entry_id})"
            );
        }

        // Determine fiscal year and period from payment date
        $fiscalYear = \App\Models\FiscalYear::query()
            ->where('company_id', $payment->company_id)
            ->where('start_date', '<=', $payment->payment_date)
            ->where('end_date', '>=', $payment->payment_date)
            ->first();

        if (!$fiscalYear) {
            throw new \RuntimeException(
                "No fiscal year found for payment date {$payment->payment_date->format('Y-m-d')}"
            );
        }

        $accountingPeriod = \App\Models\AccountingPeriod::query()
            ->where('fiscal_year_id', $fiscalYear->id)
            ->where('start_date', '<=', $payment->payment_date)
            ->where('end_date', '>=', $payment->payment_date)
            ->first();

        if (!$accountingPeriod) {
            throw new \RuntimeException(
                "No accounting period found for payment date {$payment->payment_date->format('Y-m-d')}"
            );
        }

        // Validate accounting period is open
        if ($accountingPeriod->status !== 'open') {
            throw new \RuntimeException(
                "Cannot post payment - accounting period {$accountingPeriod->period_name} is not open"
            );
        }

        return DB::transaction(function () use ($payment, $cashAccountId, $arAccountId, $fiscalYear, $accountingPeriod) {
            // Create journal entry
            $journalEntry = JournalEntry::create([
                'company_id' => $payment->company_id,
                'fiscal_year_id' => $fiscalYear->id,
                'accounting_period_id' => $accountingPeriod->id,
                'entry_type' => 'automatic',
                'entry_date' => $payment->payment_date,
                'currency_id' => $payment->currency_id,
                'exchange_rate' => $payment->exchange_rate,
                'description' => "Payment Receipt {$payment->receipt_number} - Customer: {$payment->customer->name} - Method: {$payment->payment_method}",
                'reference_type' => 'payment_receipt',
                'reference_id' => $payment->id,
                'reference_number' => $payment->receipt_number,
                'created_by' => auth()->id(),
            ]);

            // Debit: Cash/Bank Account
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $cashAccountId,
                'debit' => $payment->amount,
                'credit' => '0.0000',
                'description' => "Cash/Bank - Payment {$payment->receipt_number}",
                'created_by' => auth()->id(),
            ]);

            // Credit: Accounts Receivable
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $arAccountId,
                'debit' => '0.0000',
                'credit' => $payment->amount,
                'description' => "AR - Payment {$payment->receipt_number}",
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

            // Update payment GL posting status
            $payment->update([
                'journal_entry_id' => $journalEntry->id,
                'is_posted_to_gl' => true,
                'posted_to_gl_at' => now(),
            ]);

            return $journalEntry;
        });
    }

    /**
     * Get the console command description.
     */
    public function getCommandDescription(): string
    {
        return 'Post a payment receipt to the General Ledger';
    }
}
