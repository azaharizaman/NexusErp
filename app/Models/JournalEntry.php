<?php

namespace App\Models;

use AzahariZaman\ControlledNumber\Traits\HasSerialNumbering;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\ModelStatus\HasStatuses;

class JournalEntry extends Model
{
    use HasFactory;
    use HasSerialNumbering;
    use HasStatuses;
    use SoftDeletes;

    /**
     * The column name for storing serial numbers.
     */
    protected string $serialColumn = 'journal_entry_number';

    protected $fillable = [
        'journal_entry_number',
        'company_id',
        'fiscal_year_id',
        'accounting_period_id',
        'entry_type',
        'entry_date',
        'posting_date',
        'reference_number',
        'description',
        'notes',
        'status',
        'is_reversal',
        'reversed_entry_id',
        'reversal_entry_id',
        'is_intercompany',
        'related_company_id',
        'reciprocal_entry_id',
        'source_type',
        'source_id',
        'currency_id',
        'exchange_rate',
        'total_debit',
        'total_credit',
        'created_by',
        'updated_by',
        'submitted_by',
        'submitted_at',
        'posted_by',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'posting_date' => 'date',
        'submitted_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'is_reversal' => 'boolean',
        'is_intercompany' => 'boolean',
        'exchange_rate' => 'decimal:6',
        'total_debit' => 'decimal:4',
        'total_credit' => 'decimal:4',
    ];

    /**
     * Check if journal entry is balanced (debits = credits).
     */
    public function isBalanced(): bool
    {
        return bccomp($this->total_debit, $this->total_credit, 4) === 0;
    }

    /**
     * Calculate and update totals from lines.
     */
    public function updateTotals(): void
    {
        $this->total_debit = $this->lines()->sum('debit');
        $this->total_credit = $this->lines()->sum('credit');
        $this->save();
    }

    /**
     * Post the journal entry to GL and update account balances.
     */
    public function post(): bool
    {
        if ($this->status === 'posted') {
            throw new \RuntimeException('Journal entry '.$this->journal_entry_number.' is already posted');
        }

        if (! $this->isBalanced()) {
            throw new \InvalidArgumentException(
                'Journal entry '.$this->journal_entry_number.' is not balanced. '.
                'Debits: '.$this->total_debit.', Credits: '.$this->total_credit
            );
        }

        // Update status
        $this->status = 'posted';
        $this->posting_date = now();
        $this->posted_by = auth()->id();
        $this->save();

        // Update account balances
        foreach ($this->lines as $line) {
            $account = $line->account;
            
            // Determine if this increases or decreases the balance based on account type
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

        return true;
    }

    /**
     * Create a reversal entry for this journal entry.
     */
    public function reverse(string $description = null, ?\Carbon\Carbon $date = null): self
    {
        if ($this->status !== 'posted') {
            throw new \RuntimeException('Only posted journal entries can be reversed');
        }

        if ($this->is_reversal) {
            throw new \RuntimeException('Reversal entries cannot be reversed');
        }

        if ($this->reversal_entry_id) {
            throw new \RuntimeException('Journal entry already has a reversal entry');
        }

        // Create reversal entry
        $reversal = new self();
        $reversal->fill([
            'company_id' => $this->company_id,
            'fiscal_year_id' => $this->fiscal_year_id,
            'accounting_period_id' => $this->accounting_period_id,
            'entry_type' => 'reversing',
            'entry_date' => $date ?? now(),
            'reference_number' => $this->reference_number,
            'description' => $description ?? 'Reversal of JE '.$this->journal_entry_number,
            'is_reversal' => true,
            'reversed_entry_id' => $this->id,
            'currency_id' => $this->currency_id,
            'exchange_rate' => $this->exchange_rate,
            'status' => 'draft',
        ]);
        $reversal->save();

        // Create reversed lines (swap debit and credit)
        foreach ($this->lines as $line) {
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
                'sort_order' => $line->sort_order,
                'created_by' => auth()->id(),
            ]);
        }

        $reversal->updateTotals();

        // Link reversal to original
        $this->reversal_entry_id = $reversal->id;
        $this->save();

        return $reversal;
    }

    // Relationships

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function accountingPeriod(): BelongsTo
    {
        return $this->belongsTo(AccountingPeriod::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(\AzahariZaman\Backoffice\Models\Currency::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class)->orderBy('sort_order');
    }

    public function reversedEntry(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversed_entry_id');
    }

    public function reversalEntry(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversal_entry_id');
    }

    public function relatedCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'related_company_id');
    }

    public function reciprocalEntry(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reciprocal_entry_id');
    }

    // Audit relationships

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    // Scopes

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('entry_type', $type);
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForPeriod($query, int $periodId)
    {
        return $query->where('accounting_period_id', $periodId);
    }

    public function scopeReversals($query)
    {
        return $query->where('is_reversal', true);
    }

    public function scopeIntercompany($query)
    {
        return $query->where('is_intercompany', true);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('entry_date', [$startDate, $endDate]);
    }
}
