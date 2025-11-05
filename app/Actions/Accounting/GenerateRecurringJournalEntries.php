<?php

namespace App\Actions\Accounting;

use App\Models\AccountingPeriod;
use App\Models\FiscalYear;
use App\Models\RecurringJournalTemplate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class GenerateRecurringJournalEntries
{
    use AsAction;

    /**
     * Generate journal entries from recurring templates that are due.
     *
     * This action:
     * 1. Finds all active templates due for generation
     * 2. For each template, creates a new journal entry
     * 3. Updates template occurrence tracking
     * 4. Optionally posts entries immediately
     *
     * @param  int|null  $companyId  Limit to specific company
     * @param  bool  $postImmediately  Whether to post generated entries immediately
     * @param  bool  $dryRun  Preview without actually generating
     * @return Collection Collection of generated journal entries
     */
    public function handle(
        ?int $companyId = null,
        bool $postImmediately = false,
        bool $dryRun = false
    ): Collection {
        // Find templates due for generation
        $templates = RecurringJournalTemplate::dueForGeneration();

        if ($companyId) {
            $templates->forCompany($companyId);
        }

        $templates = $templates->get();

        if ($dryRun) {
            return $templates->map(function ($template) {
                return [
                    'template_code' => $template->template_code,
                    'template_name' => $template->template_name,
                    'next_generation_date' => $template->next_generation_date,
                    'occurrences_count' => $template->occurrences_count,
                    'max_occurrences' => $template->max_occurrences,
                ];
            });
        }

        // Generate entries
        $generated = collect();

        foreach ($templates as $template) {
            try {
                DB::transaction(function () use ($template, $postImmediately, &$generated) {
                    // Determine fiscal year and period
                    $fiscalYear = FiscalYear::where('company_id', $template->company_id)
                        ->where('is_default', true)
                        ->first();

                    if (! $fiscalYear) {
                        throw new \RuntimeException(
                            'No default fiscal year found for company '.$template->company_id
                        );
                    }

                    $period = AccountingPeriod::where('fiscal_year_id', $fiscalYear->id)
                        ->where('status', 'open')
                        ->where('start_date', '<=', now())
                        ->where('end_date', '>=', now())
                        ->first();

                    if (! $period) {
                        throw new \RuntimeException(
                            'No open accounting period found for fiscal year '.$fiscalYear->name
                        );
                    }

                    // Generate entry
                    $entry = $template->generate($fiscalYear, $period);

                    // Post immediately if requested
                    if ($postImmediately) {
                        PostJournalEntry::run($entry);
                        $entry->refresh();
                    }

                    $generated->push($entry);
                });
            } catch (\Exception $e) {
                // Log error but continue with other templates
                logger()->error('Failed to generate recurring entry from template '.$template->template_code, [
                    'error' => $e->getMessage(),
                    'template_id' => $template->id,
                ]);
            }
        }

        return $generated;
    }

    /**
     * Run as a scheduled command.
     */
    public function asCommand($command): void
    {
        $companyId = $command->option('company');
        $postImmediately = $command->option('post', false);
        $dryRun = $command->option('dry-run', false);

        if ($dryRun) {
            $command->info('Running in dry-run mode...');
            $preview = $this->handle($companyId, $postImmediately, true);

            if ($preview->isEmpty()) {
                $command->info('No recurring templates due for generation');
            } else {
                $command->table(
                    ['Template Code', 'Template Name', 'Next Generation', 'Occurrences', 'Max'],
                    $preview->map(fn ($t) => [
                        $t['template_code'],
                        $t['template_name'],
                        $t['next_generation_date'],
                        $t['occurrences_count'],
                        $t['max_occurrences'] ?? 'Unlimited',
                    ])
                );
            }

            return;
        }

        $generated = $this->handle($companyId, $postImmediately);

        if ($generated->isEmpty()) {
            $command->info('No recurring journal entries generated');
        } else {
            $command->info('Generated '.$generated->count().' journal entries');

            $command->table(
                ['JE Number', 'Template', 'Status', 'Total Debit', 'Total Credit'],
                $generated->map(fn ($je) => [
                    $je->journal_entry_number,
                    $je->reference_number ?? '-',
                    $je->status,
                    number_format($je->total_debit, 2),
                    number_format($je->total_credit, 2),
                ])
            );
        }
    }

    /**
     * Run as a scheduled job.
     */
    public function asJob(?int $companyId = null, bool $postImmediately = false): void
    {
        $this->handle($companyId, $postImmediately);
    }
}
