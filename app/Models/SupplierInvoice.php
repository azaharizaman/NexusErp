<?php

namespace App\Models;

use AzahariZaman\ControlledNumber\Traits\HasSerialNumbering;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\ModelStatus\HasStatuses;

class SupplierInvoice extends Model
{
    /** @use HasFactory<\Database\Factories\SupplierInvoiceFactory> */
    use HasFactory;
    use HasSerialNumbering;
    use HasStatuses;
    use SoftDeletes;

    /**
     * The column name for storing serial numbers.
     */
    protected string $serialColumn = 'invoice_number';

    protected $fillable = [
        'invoice_number',
        'company_id',
        'supplier_id',
        'purchase_order_id',
        'goods_received_note_id',
        'currency_id',
        'supplier_invoice_number',
        'invoice_date',
        'due_date',
        'status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'outstanding_amount',
        'description',
        'notes',
        'internal_notes',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'outstanding_amount' => 'decimal:2',
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
     * Supplier relationship.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(BusinessPartner::class, 'supplier_id');
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
     * Currency relationship.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Invoice items relationship.
     */
    public function items(): HasMany
    {
        return $this->hasMany(SupplierInvoiceItem::class)->orderBy('sort_order');
    }

    /**
     * Invoice matching relationship.
     */
    public function invoiceMatching(): HasOne
    {
        return $this->hasOne(InvoiceMatching::class);
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
     * Scope for draft invoices using Spatie ModelStatus.
     */
    public function scopeDraft($query)
    {
        return $query->currentStatus('draft');
    }

    /**
     * Scope for approved invoices using Spatie ModelStatus.
     */
    public function scopeApproved($query)
    {
        return $query->currentStatus('approved');
    }

    /**
     * Scope for paid invoices using Spatie ModelStatus.
     */
    public function scopePaid($query)
    {
        return $query->currentStatus('paid');
    }

    /**
     * Scope for unpaid invoices (with outstanding amount).
     */
    public function scopeUnpaid($query)
    {
        return $query->where('outstanding_amount', '>', 0);
    }

    /**
     * Check if invoice is fully paid.
     */
    public function isFullyPaid(): bool
    {
        return bccomp($this->outstanding_amount, '0', 4) <= 0;
    }

    /**
     * Update payment status based on paid amount.
     */
    public function updatePaymentStatus(): void
    {
        if ($this->isFullyPaid()) {
            $this->setStatus('paid', 'Invoice fully paid');
        } elseif ($this->paid_amount > 0) {
            $this->setStatus('partially_paid', 'Invoice partially paid');
        }
    }

    /**
     * Record a payment against this invoice.
     */
    public function recordPayment(float $amount): void
    {
        $this->paid_amount = bcadd($this->paid_amount, $amount, 4);
        $this->outstanding_amount = bcsub($this->total_amount, $this->paid_amount, 4);
        $this->save();
        $this->updatePaymentStatus();
    }

    /**
     * Payment allocations relationship.
     */
    public function paymentAllocations(): HasMany
    {
        return $this->hasMany(PaymentVoucherAllocation::class);
    }

    /**
     * Calculate totals from items using Action.
     */
    public function calculateTotals(): self
    {
        return \App\Actions\SupplierInvoice\CalculateSupplierInvoiceTotals::run($this);
    }
}
