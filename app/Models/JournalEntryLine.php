<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class JournalEntryLine extends Model implements Sortable
{
    use HasFactory;
    use SortableTrait;

    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'debit',
        'credit',
        'currency_id',
        'exchange_rate',
        'foreign_debit',
        'foreign_credit',
        'cost_center_id',
        'department_id',
        'project_id',
        'description',
        'notes',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'debit' => 'decimal:4',
        'credit' => 'decimal:4',
        'exchange_rate' => 'decimal:6',
        'foreign_debit' => 'decimal:4',
        'foreign_credit' => 'decimal:4',
    ];

    public $sortable = [
        'order_column_name' => 'sort_order',
        'sort_when_creating' => true,
    ];

    /**
     * Get the net amount (debit - credit).
     */
    public function getNetAmountAttribute(): float
    {
        return $this->debit - $this->credit;
    }

    /**
     * Check if this is a debit line.
     */
    public function isDebit(): bool
    {
        return $this->debit > 0;
    }

    /**
     * Check if this is a credit line.
     */
    public function isCredit(): bool
    {
        return $this->credit > 0;
    }

    // Relationships

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(\AzahariZaman\Backoffice\Models\Currency::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    // Department and Project relationships to be enabled when models exist
    // public function department(): BelongsTo
    // {
    //     return $this->belongsTo(Department::class);
    // }

    // public function project(): BelongsTo
    // {
    //     return $this->belongsTo(Project::class);
    // }

    // Audit relationships

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
