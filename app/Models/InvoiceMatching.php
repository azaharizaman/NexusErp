<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceMatching extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceMatchingFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'supplier_invoice_id',
        'purchase_order_id',
        'goods_received_note_id',
        'matching_status',
        'po_total',
        'grn_total',
        'invoice_total',
        'quantity_variance',
        'price_variance',
        'total_variance',
        'variance_percentage',
        'is_within_tolerance',
        'tolerance_percentage',
        'mismatches',
        'notes',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'po_total' => 'decimal:2',
        'grn_total' => 'decimal:2',
        'invoice_total' => 'decimal:2',
        'quantity_variance' => 'decimal:3',
        'price_variance' => 'decimal:2',
        'total_variance' => 'decimal:2',
        'variance_percentage' => 'decimal:2',
        'tolerance_percentage' => 'decimal:2',
        'is_within_tolerance' => 'boolean',
        'mismatches' => 'array',
        'approved_at' => 'datetime',
    ];

    /**
     * Company relationship.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Supplier invoice relationship.
     */
    public function supplierInvoice(): BelongsTo
    {
        return $this->belongsTo(SupplierInvoice::class);
    }

    /**
     * Purchase order relationship.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Goods received note relationship.
     */
    public function goodsReceivedNote(): BelongsTo
    {
        return $this->belongsTo(GoodsReceivedNote::class);
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
     * Scope for matched records.
     */
    public function scopeMatched($query)
    {
        return $query->where('matching_status', 'matched');
    }

    /**
     * Scope for mismatched records.
     */
    public function scopeMismatched($query)
    {
        return $query->where('matching_status', 'mismatched');
    }

    /**
     * Perform three-way matching validation using Action.
     */
    public function performMatching(): self
    {
        return \App\Actions\InvoiceMatching\PerformThreeWayMatching::run($this);
    }
}
