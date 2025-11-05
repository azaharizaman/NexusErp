<?php

namespace App\Models;

use Spatie\ModelStatus\HasStatuses;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountingPeriod extends Model
{
    use HasFactory, SoftDeletes, HasStatuses;

    protected $fillable = [
        'fiscal_year_id',
        'period_name',
        'period_code',
        'period_type',
        'period_number',
        'start_date',
        'end_date',
        'is_adjusting_period',
        'closed_on',
        'closed_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'closed_on' => 'date',
        'period_number' => 'integer',
        'is_adjusting_period' => 'boolean',
    ];

    /**
     * Get the fiscal year
     */
    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    /**
     * Get the user who closed this period
     */
    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * Get the user who created this record
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this record
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope for open periods
     */
    public function scopeOpen($query)
    {
        return $query->whereHas('statuses', function ($q) {
            $q->where('status', 'open');
        });
    }

    /**
     * Scope for closed periods
     */
    public function scopeClosed($query)
    {
        return $query->whereHas('statuses', function ($q) {
            $q->where('status', 'closed');
        });
    }

    /**
     * Scope for locked periods
     */
    public function scopeLocked($query)
    {
        return $query->whereHas('statuses', function ($q) {
            $q->where('status', 'locked');
        });
    }

    /**
     * Scope for adjusting periods
     */
    public function scopeAdjusting($query)
    {
        return $query->where('is_adjusting_period', true);
    }

    /**
     * Scope for regular periods (non-adjusting)
     */
    public function scopeRegular($query)
    {
        return $query->where('is_adjusting_period', false);
    }

    /**
     * Check if this period is current (today falls within the period)
     */
    public function isCurrent(): bool
    {
        $today = now()->toDateString();

        return $today >= $this->start_date->toDateString() && $today <= $this->end_date->toDateString();
    }
}
