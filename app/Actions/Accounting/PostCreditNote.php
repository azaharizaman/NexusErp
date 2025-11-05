<?php

namespace App\Actions\Accounting;

use App\Models\CustomerCreditNote;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class PostCreditNote
{
    use AsAction;

    /**
     * Post customer credit note to the General Ledger.
     *
     * This creates a journal entry with:
     * - Debit: Sales Returns/Allowances Account (contra-revenue account)
     * - Credit: Accounts Receivable (asset account)
     *
     * @param  CustomerCreditNote  $creditNote  The credit note to post
     * @param  int  $salesReturnsAccountId  The Sales Returns/Allowances account ID
     * @param  int  $arAccountId  The Accounts Receivable account ID
     * @return JournalEntry The created journal entry
     *
     * @throws \InvalidArgumentException If credit note is not issued
     * @throws \LogicException If credit note is already posted to GL
     * @throws \RuntimeException If accounting period is not open
     */
    public function handle(
        CustomerCreditNote $creditNote,
        int $salesReturnsAccountId,
        int $arAccountId
    ): JournalEntry {
        // Validate credit note status
        if ($creditNote->status !== 'issued') {
            throw new \InvalidArgumentException(
                "Cannot post credit note {$creditNote->credit_note_number} - must be issued, current status: {$creditNote->status}"
            );
        }

        // Check if already posted
        if ($creditNote->is_posted_to_gl) {
            throw new \LogicException(
                "Credit note {$creditNote->credit_note_number} is already posted to GL (Journal Entry: {$creditNote->journal_entry_id})"
            );
        }

        // Validate accounting period is open
        $period = $creditNote->accountingPeriod;
        if (!$period || $period->status !== 'open') {
            throw new \RuntimeException(
                "Cannot post credit note - accounting period {$period?->period_name} is not open"
            );
        }

        return DB::transaction(function () use ($creditNote, $salesReturnsAccountId, $arAccountId) {
            // Create journal entry
            $journalEntry = JournalEntry::create([
                'company_id' => $creditNote->company_id,
                'fiscal_year_id' => $creditNote->fiscal_year_id,
                'accounting_period_id' => $creditNote->accounting_period_id,
                'entry_type' => 'automatic',
                'entry_date' => $creditNote->credit_note_date,
                'currency_id' => $creditNote->currency_id,
                'exchange_rate' => $creditNote->exchange_rate,
                'description' => "Credit Note {$creditNote->credit_note_number} - Customer: {$creditNote->customer->name} - Reason: {$creditNote->reason}",
                'reference_type' => 'customer_credit_note',
                'reference_id' => $creditNote->id,
                'reference_number' => $creditNote->credit_note_number,
                'created_by' => auth()->id(),
            ]);

            // Debit: Sales Returns/Allowances (reduces revenue)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $salesReturnsAccountId,
                'debit' => $creditNote->amount,
                'credit' => '0.0000',
                'description' => "Sales Returns - Credit Note {$creditNote->credit_note_number}",
                'created_by' => auth()->id(),
            ]);

            // Credit: Accounts Receivable (reduces what customer owes)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $arAccountId,
                'debit' => '0.0000',
                'credit' => $creditNote->amount,
                'description' => "AR - Credit Note {$creditNote->credit_note_number}",
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

            // Update credit note GL posting status
            $creditNote->update([
                'journal_entry_id' => $journalEntry->id,
                'is_posted_to_gl' => true,
                'posted_to_gl_at' => now(),
            ]);

            // Apply credit note to invoice if linked
            if ($creditNote->sales_invoice_id && $creditNote->canBeApplied()) {
                $creditNote->applyToInvoice();
            }

            return $journalEntry;
        });
    }

    /**
     * Get the console command description.
     */
    public function getCommandDescription(): string
    {
        return 'Post a customer credit note to the General Ledger';
    }
}
