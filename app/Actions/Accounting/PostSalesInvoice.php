<?php

namespace App\Actions\Accounting;

use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\SalesInvoice;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class PostSalesInvoice
{
    use AsAction;

    /**
     * Post sales invoice to the General Ledger.
     *
     * This creates a journal entry with:
     * - Debit: Accounts Receivable (asset account)
     * - Credit: Revenue Account (income account from each line item)
     * - Credit: Tax Payable (if applicable)
     *
     * @param  SalesInvoice  $invoice  The sales invoice to post
     * @param  int  $arAccountId  The Accounts Receivable account ID
     * @param  int|null  $taxPayableAccountId  The Tax Payable account ID (optional)
     * @return JournalEntry The created journal entry
     *
     * @throws \InvalidArgumentException If invoice is not in issued status
     * @throws \LogicException If invoice is already posted to GL
     * @throws \RuntimeException If accounting period is not open
     */
    public function handle(
        SalesInvoice $invoice,
        int $arAccountId,
        ?int $taxPayableAccountId = null
    ): JournalEntry {
        // Validate invoice status
        if ($invoice->status !== 'issued' && $invoice->status !== 'partially_paid') {
            throw new \InvalidArgumentException(
                "Cannot post invoice {$invoice->invoice_number} - must be issued or partially paid, current status: {$invoice->status}"
            );
        }

        // Check if already posted
        if ($invoice->is_posted_to_gl) {
            throw new \LogicException(
                "Invoice {$invoice->invoice_number} is already posted to GL (Journal Entry: {$invoice->journal_entry_id})"
            );
        }

        // Validate invoice has items
        if ($invoice->items()->count() === 0) {
            throw new \InvalidArgumentException(
                "Cannot post invoice {$invoice->invoice_number} - no line items found"
            );
        }

        // Validate accounting period is open
        $period = $invoice->accountingPeriod;
        if (!$period || $period->status !== 'open') {
            throw new \RuntimeException(
                "Cannot post invoice - accounting period {$period?->period_name} is not open"
            );
        }

        return DB::transaction(function () use ($invoice, $arAccountId, $taxPayableAccountId) {
            // Create journal entry
            $journalEntry = JournalEntry::create([
                'company_id' => $invoice->company_id,
                'fiscal_year_id' => $invoice->fiscal_year_id,
                'accounting_period_id' => $invoice->accounting_period_id,
                'entry_type' => 'automatic',
                'entry_date' => $invoice->invoice_date,
                'currency_id' => $invoice->currency_id,
                'exchange_rate' => $invoice->exchange_rate,
                'description' => "Sales Invoice {$invoice->invoice_number} - Customer: {$invoice->customer->name}",
                'reference_type' => 'sales_invoice',
                'reference_id' => $invoice->id,
                'reference_number' => $invoice->invoice_number,
                'created_by' => auth()->id(),
            ]);

            // Debit: Accounts Receivable for total amount
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $arAccountId,
                'debit' => $invoice->total_amount,
                'credit' => '0.0000',
                'description' => "AR - Invoice {$invoice->invoice_number}",
                'created_by' => auth()->id(),
            ]);

            // Credit: Revenue for each line item
            foreach ($invoice->items as $item) {
                if ($item->revenue_account_id) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $item->revenue_account_id,
                        'debit' => '0.0000',
                        'credit' => $item->line_total,
                        'description' => "Revenue - {$item->item_description}",
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            // Credit: Tax Payable for total tax amount (if applicable)
            if ($invoice->tax_amount > 0 && $taxPayableAccountId) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $taxPayableAccountId,
                    'debit' => '0.0000',
                    'credit' => $invoice->tax_amount,
                    'description' => "Tax Payable - Invoice {$invoice->invoice_number}",
                    'created_by' => auth()->id(),
                ]);
            }

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

            // Update invoice GL posting status
            $invoice->update([
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
        return 'Post a sales invoice to the General Ledger';
    }
}
