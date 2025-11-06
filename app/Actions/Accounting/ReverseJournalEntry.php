<?php

namespace App\Actions\Accounting;

use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class ReverseJournalEntry
{
    use AsAction;

    /**
     * Create a reversal entry for a posted journal entry.
     *
     * This action:
     * 1. Validates the original entry is posted
     * 2. Creates a new journal entry with reversed debits/credits
     * 3. Links the reversal to the original entry
     * 4. Optionally posts the reversal immediately
     *
     * @param  JournalEntry  $originalEntry  The original journal entry to reverse
     * @param  string|null  $description  Custom description for reversal
     * @param  \Carbon\Carbon|null  $date  Date for reversal entry
     * @param  bool  $postImmediately  Whether to post the reversal immediately
     * @return JournalEntry The reversal journal entry
     *
     * @throws \RuntimeException if original entry is not posted or already reversed
     */
    public function handle(
        JournalEntry $originalEntry,
        ?string $description = null,
        ?\Carbon\Carbon $date = null,
        bool $postImmediately = false
    ): JournalEntry {
        // Validate original entry
        if ($originalEntry->status !== 'posted') {
            throw new \RuntimeException(
                'Only posted journal entries can be reversed. ' .
                'Entry ' . $originalEntry->journal_entry_number . ' has status: ' . $originalEntry->status
            );
        }

        if ($originalEntry->is_reversal) {
            throw new \RuntimeException(
                'Reversal entries cannot be reversed. ' .
                'Entry ' . $originalEntry->journal_entry_number . ' is already a reversal'
            );
        }

        if ($originalEntry->reversal_entry_id) {
            throw new \RuntimeException(
                'Journal entry ' . $originalEntry->journal_entry_number . ' already has a reversal entry'
            );
        }

        // Create reversal in transaction
        $reversal = DB::transaction(function () use ($originalEntry, $description, $date, $postImmediately) {
            // Create reversal entry
            $reversal = new JournalEntry();
            $reversal->fill([
                'company_id' => $originalEntry->company_id,
                'fiscal_year_id' => $originalEntry->fiscal_year_id,
                'accounting_period_id' => $originalEntry->accounting_period_id,
                'entry_type' => 'reversing',
                'entry_date' => $date ?? now(),
                'reference_number' => $originalEntry->reference_number,
                'description' => $description ?? 'Reversal of JE ' . $originalEntry->journal_entry_number,
                'notes' => 'Auto-generated reversal of ' . $originalEntry->journal_entry_number,
                'is_reversal' => true,
                'reversed_entry_id' => $originalEntry->id,
                'currency_id' => $originalEntry->currency_id,
                'exchange_rate' => $originalEntry->exchange_rate,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);
            $reversal->save();

            // Create reversed lines (swap debit and credit)
            foreach ($originalEntry->lines as $line) {
                $reversal->lines()->create([
                    'account_id' => $line->account_id,
                    'debit' => $line->credit,  // Swap
                    'credit' => $line->debit,  // Swap
                    'currency_id' => $line->currency_id,
                    'exchange_rate' => $line->exchange_rate,
                    'foreign_debit' => $line->foreign_credit,
                    'foreign_credit' => $line->foreign_debit,
                    'cost_center_id' => $line->cost_center_id,
                    'department_id' => $line->department_id,
                    'project_id' => $line->project_id,
                    'description' => $line->description,
                    'notes' => 'Reversal of line from ' . $originalEntry->journal_entry_number,
                    'sort_order' => $line->sort_order,
                    'created_by' => auth()->id(),
                ]);
            }

            // Update totals
            $reversal->updateTotals();

            // Link reversal to original
            $originalEntry->reversal_entry_id = $reversal->id;
            $originalEntry->save();

            // Post immediately if requested
            if ($postImmediately) {
                PostJournalEntry::run($reversal);
                $reversal->refresh();
            }

            return $reversal;
        });

        return $reversal;
    }

    /**
     * Run as a job.
     */
    public function asJob(
        JournalEntry $originalEntry,
        ?string $description = null,
        ?\Carbon\Carbon $date = null,
        bool $postImmediately = false
    ): void {
        $this->handle($originalEntry, $description, $date, $postImmediately);
    }
}
