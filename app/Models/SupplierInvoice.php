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
        'payment_status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'outstanding_amount',
        'description',
        'notes',
        'internal_notes',
        'journal_entry_id',
        'is_posted_to_gl',
        'posted_to_gl_at',
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
        'is_posted_to_gl' => 'boolean',
        'posted_to_gl_at' => 'datetime',
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
     * Journal entry relationship.
     */
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
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
     * Calculate totals from items using Action.
     */
    public function calculateTotals(): self
    {
        return \App\Actions\SupplierInvoice\CalculateSupplierInvoiceTotals::run($this);
    }

    /**
     * Calculate outstanding amount.
     */
    public function calculateOutstanding(): void
    {
        $this->outstanding_amount = $this->total_amount - $this->paid_amount;
        $this->save();
    }

    /**
     * Update payment status based on paid amount and due date.
     */
    public function updatePaymentStatus(): void
    {
        if ($this->isFullyPaid()) {
            $this->payment_status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->payment_status = 'partially_paid';
        } elseif ($this->isOverdue()) {
            $this->payment_status = 'overdue';
        } else {
            $this->payment_status = 'unpaid';
        }
        $this->save();
    }

    /**
     * Check if invoice is fully paid.
     */
    public function isFullyPaid(): bool
    {
        return bccomp($this->paid_amount, $this->total_amount, 2) >= 0;
    }

    /**
     * Check if invoice is overdue.
     */
    public function isOverdue(): bool
    {
        // An invoice is overdue if it's not paid and past the due date
        return ! $this->isFullyPaid() &&
               $this->due_date !== null &&
               now()->gt($this->due_date);
    }

    /**
     * Record a payment against this invoice.
     */
    public function recordPayment(float $amount): void
    {
        $this->paid_amount += $amount;
        $this->calculateOutstanding();
        $this->updatePaymentStatus();
    }

    /**
     * Scope for unpaid invoices.
     */
    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    /**
     * Scope for partially paid invoices.
     */
    public function scopePartiallyPaid($query)
    {
        return $query->where('payment_status', 'partially_paid');
    }

    /**
     * Scope for overdue invoices.
     */
    public function scopeOverdue($query)
    {
        return $query->where('payment_status', 'overdue')
            ->orWhere(function ($q) {
                $q->whereIn('payment_status', ['unpaid', 'partially_paid'])
                    ->where('due_date', '<', now());
            });
    }

    /**
     * Scope for posted to GL invoices.
     */
    public function scopePostedToGl($query)
    {
        return $query->where('is_posted_to_gl', true);
    }
}
