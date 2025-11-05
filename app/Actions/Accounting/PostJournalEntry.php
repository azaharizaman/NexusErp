<?php

namespace App\Actions\Accounting;

use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class PostJournalEntry
{
    use AsAction;

    /**
     * Post a journal entry to the General Ledger.
     *
     * This action:
     * 1. Validates the journal entry is balanced
     * 2. Validates the accounting period is open
     * 3. Updates the status to 'posted'
     * 4. Updates all affected account balances
     * 5. Records posting metadata (date, user)
     *
     * @param  JournalEntry  $journalEntry  The journal entry to post
     * @return JournalEntry The posted journal entry
     *
     * @throws \RuntimeException if entry is already posted
     * @throws \InvalidArgumentException if entry is not balanced
     * @throws \LogicException if accounting period is not open
     */
    public function handle(JournalEntry $journalEntry): JournalEntry
    {
        // Validate status
        if ($journalEntry->status === 'posted') {
            throw new \RuntimeException(
                'Journal entry '.$journalEntry->journal_entry_number.' is already posted'
            );
        }

        if ($journalEntry->status === 'cancelled') {
            throw new \RuntimeException(
                'Cannot post cancelled journal entry '.$journalEntry->journal_entry_number
            );
        }

        // Validate balanced
        if (! $journalEntry->isBalanced()) {
            throw new \InvalidArgumentException(
                'Journal entry '.$journalEntry->journal_entry_number.' is not balanced. '.
                'Debits: '.$journalEntry->total_debit.', Credits: '.$journalEntry->total_credit
            );
        }

        // Validate accounting period is open
        $period = $journalEntry->accountingPeriod;
        if (! $period || $period->status !== 'open') {
            throw new \LogicException(
                'Cannot post to closed or locked accounting period. '.
                'Period: '.($period?->period_name ?? 'Unknown').' Status: '.($period?->status ?? 'Unknown')
            );
        }

        // Perform posting in a transaction
        DB::transaction(function () use ($journalEntry) {
            // Update journal entry status
            $journalEntry->status = 'posted';
            $journalEntry->posting_date = now();
            $journalEntry->posted_by = auth()->id();
            $journalEntry->save();

            // Update account balances
            foreach ($journalEntry->lines as $line) {
                $account = $line->account;

                // Determine if this is a debit account type (increases with debits)
                $isDebitAccount = in_array($account->account_type, ['Asset', 'Expense']);

                if ($line->debit > 0) {
                    if ($isDebitAccount) {
                        $account->current_balance += $line->debit;
                    } else {
                        $account->current_balance -= $line->debit;
                    }
                }

                if ($line->credit > 0) {
                    if ($isDebitAccount) {
                        $account->current_balance -= $line->credit;
                    } else {
                        $account->current_balance += $line->credit;
                    }
                }

                $account->save();
            }
        });

        return $journalEntry->fresh();
    }

    /**
     * Get rules for command validation.
     */
    public function rules(): array
    {
        return [
            'journalEntry' => ['required'],
        ];
    }

    /**
     * Run as a job.
     */
    public function asJob(JournalEntry $journalEntry): void
    {
        $this->handle($journalEntry);
    }

    /**
     * Run as a command.
     */
    public function asCommand($command): void
    {
        $journalEntryId = $command->argument('journal_entry_id');
        $journalEntry = JournalEntry::findOrFail($journalEntryId);

        $posted = $this->handle($journalEntry);

        $command->info('Journal entry '.$posted->journal_entry_number.' posted successfully');
    }
}
