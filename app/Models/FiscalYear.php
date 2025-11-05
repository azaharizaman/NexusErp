<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\ModelStatus\HasStatuses;

class FiscalYear extends Model
{
    use HasFactory, SoftDeletes, HasStatuses;

    protected $fillable = [
        'name',
        'code',
        'start_date',
        'end_date',
        'company_id',
        'is_default',
        'is_locked',
        'closed_on',
        'closed_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'closed_on' => 'date',
        'is_default' => 'boolean',
        'is_locked' => 'boolean',
    ];

    /**
     * Get the company
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get accounting periods for this fiscal year
     */
    public function accountingPeriods(): HasMany
    {
        return $this->hasMany(AccountingPeriod::class)->orderBy('period_number');
    }

    /**
     * Get the user who closed this fiscal year
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
     * Scope for draft fiscal years using HasStatuses
     */
    public function scopeDraft($query)
    {
        return $query->whereHas('statuses', function ($q) {
            $q->where('name', 'draft');
        });
    }

    /**
     * Scope for active fiscal years using HasStatuses
     */
    public function scopeActive($query)
    {
        return $query->whereHas('statuses', function ($q) {
            $q->where('name', 'active');
        });
    }

    /**
     * Scope for closed fiscal years using HasStatuses
     */
    public function scopeClosed($query)
    {
        return $query->whereHas('statuses', function ($q) {
            $q->where('name', 'closed');
        });
    }

    /**
     * Scope for default fiscal year
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope for a specific company
     */
    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
