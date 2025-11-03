<?php

namespace App\Models;

use AzahariZaman\ControlledNumber\Traits\HasSerialNumbering;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\ModelStatus\HasStatuses;

class PurchaseOrder extends Model
{
    /** @use HasFactory<\Database\Factories\PurchaseOrderFactory> */
    use HasFactory;
    use HasSerialNumbering;
    use HasStatuses;
    use SoftDeletes;

    /**
     * The column name for storing serial numbers.
     */
    protected string $serialColumn = 'po_number';

    protected $fillable = [
        'po_number',
        'company_id',
        'supplier_id',
        'purchase_recommendation_id',
        'purchase_contract_id',
        'currency_id',
        'price_list_id',
        'terms_template_id',
        'po_date',
        'expected_delivery_date',
        'delivery_deadline',
        'status',
        'shipping_address',
        'billing_address',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'description',
        'terms_and_conditions',
        'notes',
        'internal_notes',
        'payment_terms',
        'delivery_terms',
        'incoterms',
        'requested_by',
        'approved_by',
        'approved_at',
        'issued_by',
        'issued_at',
        'closed_by',
        'closed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'po_date' => 'date',
        'expected_delivery_date' => 'date',
        'delivery_deadline' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'issued_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * Company relationship.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Supplier relationship.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(BusinessPartner::class, 'supplier_id');
    }

    /**
     * Purchase recommendation relationship.
     */
    public function purchaseRecommendation(): BelongsTo
    {
        return $this->belongsTo(PurchaseRecommendation::class);
    }

    /**
     * Purchase contract relationship.
     */
    public function purchaseContract(): BelongsTo
    {
        return $this->belongsTo(PurchaseContract::class);
    }

    /**
     * Currency relationship.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Price list relationship.
     */
    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    /**
     * Terms template relationship.
     */
    public function termsTemplate(): BelongsTo
    {
        return $this->belongsTo(TermsTemplate::class);
    }

    /**
     * Items relationship.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class)->orderBy('sort_order');
    }

    /**
     * Revisions relationship.
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(PurchaseOrderRevision::class)->orderBy('created_at', 'desc');
    }

    /**
     * Delivery schedules relationship.
     */
    public function deliverySchedules(): HasMany
    {
        return $this->hasMany(DeliverySchedule::class)->orderBy('scheduled_date');
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
     * Issuer relationship.
     */
    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /**
     * Closer relationship.
     */
    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
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
     * Scope for draft POs.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope for approved POs.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for issued POs.
     */
    public function scopeIssued($query)
    {
        return $query->where('status', 'issued');
    }

    /**
     * Scope for closed POs.
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    /**
     * Calculate totals from items.
     */
    public function calculateTotals(): void
    {
        $this->subtotal = $this->items->sum('line_total');
        $this->tax_amount = $this->items->sum('tax_amount');
        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount;
        $this->save();
    }

    /**
     * Check if PO can be edited.
     */
    public function canEdit(): bool
    {
        return in_array($this->status, ['draft']);
    }

    /**
     * Check if PO can be approved.
     */
    public function canApprove(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if PO can be issued.
     */
    public function canIssue(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if PO can be closed.
     */
    public function canClose(): bool
    {
        return in_array($this->status, ['issued']);
    }
}
