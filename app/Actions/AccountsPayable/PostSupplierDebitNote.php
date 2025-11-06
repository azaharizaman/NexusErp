<?php

namespace App\Actions\AccountsPayable;

use App\Models\DebitNote;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class PostSupplierDebitNote
{
    use AsAction;

    /**
     * Post supplier debit note to the General Ledger.
     *
     * This creates a journal entry with:
     * - Debit: Accounts Payable (liability account) - reduces what we owe
     * - Credit: Purchase Returns/Allowances Account (contra-expense account)
     *
     * Also updates the supplier invoice outstanding amount if linked.
     *
     * @param  DebitNote  $debitNote  The debit note to post
     * @param  int  $apAccountId  The Accounts Payable account ID
     * @param  int  $purchaseReturnsAccountId  The Purchase Returns/Allowances account ID
     * @return JournalEntry The created journal entry
     *
     * @throws \InvalidArgumentException If debit note is not approved
     * @throws \LogicException If debit note is already posted to GL
     * @throws \RuntimeException If fiscal year/period not found or not open
     */
    public function handle(
        DebitNote $debitNote,
        int $apAccountId,
        int $purchaseReturnsAccountId
    ): JournalEntry {
        // Validate debit note status - must be approved
        if ($debitNote->latestStatus() !== 'approved') {
            throw new \InvalidArgumentException(
                "Cannot post debit note {$debitNote->debit_note_number} - debit note must be approved, current status: " . $debitNote->latestStatus()
            );
        }

        // Check if already posted
        if ($debitNote->is_posted_to_gl) {
            throw new \LogicException(
                "Debit note {$debitNote->debit_note_number} is already posted to GL (Journal Entry: {$debitNote->journal_entry_id})"
            );
        }

        // Determine fiscal year and period from debit note date
        $fiscalYear = \App\Models\FiscalYear::query()
            ->where('company_id', $debitNote->company_id)
            ->where('start_date', '<=', $debitNote->debit_note_date)
            ->where('end_date', '>=', $debitNote->debit_note_date)
            ->first();

        if (!$fiscalYear) {
            throw new \RuntimeException(
                "No fiscal year found for debit note date {$debitNote->debit_note_date->format('Y-m-d')}"
            );
        }

        $accountingPeriod = \App\Models\AccountingPeriod::query()
            ->where('fiscal_year_id', $fiscalYear->id)
            ->where('start_date', '<=', $debitNote->debit_note_date)
            ->where('end_date', '>=', $debitNote->debit_note_date)
            ->first();

        if (!$accountingPeriod) {
            throw new \RuntimeException(
                "No accounting period found for debit note date {$debitNote->debit_note_date->format('Y-m-d')}"
            );
        }

        // Validate accounting period is open
        if ($accountingPeriod->status !== 'open') {
            throw new \RuntimeException(
                "Cannot post debit note - accounting period {$accountingPeriod->period_name} is not open"
            );
        }

        return DB::transaction(function () use ($debitNote, $apAccountId, $purchaseReturnsAccountId, $fiscalYear, $accountingPeriod) {
            // Create journal entry
            $journalEntry = JournalEntry::create([
                'company_id' => $debitNote->company_id,
                'fiscal_year_id' => $fiscalYear->id,
                'accounting_period_id' => $accountingPeriod->id,
                'entry_type' => 'automatic',
                'entry_date' => $debitNote->debit_note_date,
                'currency_id' => $debitNote->currency_id,
                'exchange_rate' => $debitNote->exchange_rate ?? 1,
                'description' => "Supplier Debit Note {$debitNote->debit_note_number} - Supplier: {$debitNote->supplier->name} - Reason: {$debitNote->reason}",
                'reference_type' => 'debit_note',
                'reference_id' => $debitNote->id,
                'reference_number' => $debitNote->debit_note_number,
                'created_by' => auth()->id(),
            ]);

            // Debit: Accounts Payable (reduces liability)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $apAccountId,
                'debit' => $debitNote->amount,
                'credit' => '0.0000',
                'description' => "AP - Debit Note {$debitNote->debit_note_number}",
                'created_by' => auth()->id(),
            ]);

            // Credit: Purchase Returns/Allowances
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $purchaseReturnsAccountId,
                'debit' => '0.0000',
                'credit' => $debitNote->amount,
                'description' => "Purchase Returns - Debit Note {$debitNote->debit_note_number}",
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

            // Update debit note GL posting status
            $debitNote->update([
                'journal_entry_id' => $journalEntry->id,
                'is_posted_to_gl' => true,
                'posted_to_gl_at' => now(),
            ]);

            // Update supplier invoice outstanding amount if linked
            if ($debitNote->supplierInvoice) {
                $invoice = $debitNote->supplierInvoice;
                $invoice->outstanding_amount = bcsub($invoice->outstanding_amount, $debitNote->amount, 4);
                $invoice->save();

                // Update invoice status if fully credited
                if (bccomp($invoice->outstanding_amount, '0', 4) <= 0) {
                    $invoice->setStatus('paid', 'Invoice fully credited via debit note');
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
        return 'Post a supplier debit note to the General Ledger';
    }
}
