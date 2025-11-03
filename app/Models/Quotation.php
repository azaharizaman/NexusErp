<?php

namespace App\Models;

use AzahariZaman\ControlledNumber\Traits\HasSerialNumbering;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\ModelStatus\HasStatuses;

class Quotation extends Model
{
    use HasFactory;
    use HasSerialNumbering;
    use HasStatuses;
    use SoftDeletes;

    /**
     * The column name for storing serial numbers.
     */
    protected string $serialColumn = 'quotation_number';

    protected $fillable = [
        'quotation_number',
        'request_for_quotation_id',
        'business_partner_id',
        'quotation_date',
        'valid_until',
        'currency_id',
        'subtotal',
        'tax_amount',
        'total_amount',
        'status',
        'terms_and_conditions',
        'notes',
        'remarks',
        'delivery_lead_time_days',
        'payment_terms',
        'is_recommended',
        'created_by',
        'updated_by',
        'requested_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'valid_until' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'is_recommended' => 'boolean',
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
     * Supplier relationship.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(BusinessPartner::class, 'business_partner_id');
    }

    /**
     * Currency relationship.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Items relationship.
     */
    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order');
    }

    /**
     * Scope for recommended quotations.
     */
    public function scopeRecommended($query)
    {
        return $query->where('is_recommended', true);
    }

    /**
     * Scope for submitted quotations.
     */
    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    /**
     * Scope for accepted quotations.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Requester relationship.
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Approver relationship.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
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
     * Calculate totals from items.
     */
    public function calculateTotals(): void
    {
        $this->subtotal = $this->items->sum('line_total');
        $this->tax_amount = $this->items->sum('tax_amount');
        $this->total_amount = $this->subtotal + $this->tax_amount;
        $this->save();
    }
}
