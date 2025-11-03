<?php

namespace App\Models;

use AzahariZaman\ControlledNumber\Traits\HasSerialNumbering;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\ModelStatus\HasStatuses;

class PurchaseRecommendation extends Model
{
    use HasFactory;
    use HasSerialNumbering;
    use HasStatuses;
    use SoftDeletes;

    /**
     * The column name for storing serial numbers.
     */
    protected string $serialColumn = 'recommendation_number';

    protected $fillable = [
        'recommendation_number',
        'request_for_quotation_id',
        'recommended_quotation_id',
        'company_id',
        'recommendation_date',
        'status',
        'justification',
        'comparison_notes',
        'remarks',
        'recommended_total',
        'currency_id',
        'created_by',
        'updated_by',
        'requested_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'recommendation_date' => 'date',
        'recommended_total' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    /**
     * RFQ relationship.
     */
    public function requestForQuotation(): BelongsTo
    {
        return $this->belongsTo(RequestForQuotation::class);
    }

    /**
     * Recommended quotation relationship.
     */
    public function recommendedQuotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'recommended_quotation_id');
    }

    /**
     * Company relationship.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Currency relationship.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Approver relationship.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Requester relationship.
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Creator relationship.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Updater relationship.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope for approved recommendations.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for pending recommendations.
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'submitted']);
    }
}
