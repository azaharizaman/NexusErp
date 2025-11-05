<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecurringJournalTemplate extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'template_name',
        'template_code',
        'description',
        'company_id',
        'entry_type',
        'frequency',
        'start_date',
        'end_date',
        'max_occurrences',
        'occurrences_count',
        'next_generation_date',
        'last_generated_date',
        'template_lines',
        'reference_prefix',
        'notes',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'next_generation_date' => 'date',
        'last_generated_date' => 'date',
        'template_lines' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Calculate the next generation date based on frequency.
     */
    public function calculateNextGenerationDate(): ?\Carbon\Carbon
    {
        $lastDate = $this->last_generated_date ?? $this->start_date;
        
        if (! $lastDate) {
            return null;
        }

        $date = $lastDate->copy();

        return match ($this->frequency) {
            'daily' => $date->addDay(),
            'weekly' => $date->addWeek(),
            'biweekly' => $date->addWeeks(2),
            'monthly' => $date->addMonth(),
            'quarterly' => $date->addMonths(3),
            'half-yearly' => $date->addMonths(6),
            'yearly' => $date->addYear(),
            default => null,
        };
    }

    /**
     * Check if template should generate based on next generation date.
     */
    public function shouldGenerate(): bool
    {
        // Not active
        if (! $this->is_active) {
            return false;
        }

        // Max occurrences reached
        if ($this->max_occurrences && $this->occurrences_count >= $this->max_occurrences) {
            return false;
        }

        // End date passed
        if ($this->end_date && now()->gt($this->end_date)) {
            return false;
        }

        // Next generation date not reached
        if ($this->next_generation_date && now()->lt($this->next_generation_date)) {
            return false;
        }

        return true;
    }

    /**
     * Generate a journal entry from this template.
     */
    public function generate(?FiscalYear $fiscalYear = null, ?AccountingPeriod $period = null): JournalEntry
    {
        if (! $this->shouldGenerate()) {
            throw new \RuntimeException('Template '.$this->template_code.' cannot generate at this time');
        }

        // Determine fiscal year and period
        if (! $fiscalYear) {
            $fiscalYear = FiscalYear::where('company_id', $this->company_id)
                ->where('is_default', true)
                ->first();
        }

        if (! $period) {
            $period = AccountingPeriod::where('fiscal_year_id', $fiscalYear->id)
                ->where('status', 'open')
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->first();
        }

        if (! $fiscalYear || ! $period) {
            throw new \RuntimeException('No active fiscal year or period found for template generation');
        }

        // Create journal entry
        $entry = new JournalEntry();
        $entry->fill([
            'company_id' => $this->company_id,
            'fiscal_year_id' => $fiscalYear->id,
            'accounting_period_id' => $period->id,
            'entry_type' => $this->entry_type,
            'entry_date' => now(),
            'reference_number' => $this->reference_prefix ? $this->reference_prefix.'-'.now()->format('Ymd') : null,
            'description' => $this->description.' (Auto-generated from template '.$this->template_code.')',
            'notes' => $this->notes,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);
        $entry->save();

        // Create lines from template
        foreach ($this->template_lines as $index => $line) {
            $entry->lines()->create([
                'account_id' => $line['account_id'],
                'debit' => $line['debit'] ?? 0,
                'credit' => $line['credit'] ?? 0,
                'description' => $line['description'] ?? null,
                'cost_center_id' => $line['cost_center_id'] ?? null,
                'department_id' => $line['department_id'] ?? null,
                'project_id' => $line['project_id'] ?? null,
                'sort_order' => $index,
                'created_by' => auth()->id(),
            ]);
        }

        // Update totals
        $entry->updateTotals();

        // Update template occurrence tracking
        $this->occurrences_count++;
        $this->last_generated_date = now();
        $this->next_generation_date = $this->calculateNextGenerationDate();
        $this->save();

        return $entry;
    }

    // Relationships

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeDueForGeneration($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('next_generation_date')
                    ->orWhere('next_generation_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('max_occurrences')
                    ->orWhereRaw('occurrences_count < max_occurrences');
            });
    }
}
