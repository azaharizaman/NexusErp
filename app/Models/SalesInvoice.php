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

class SalesInvoice extends Model
{
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
        'customer_id',
        'sales_order_id',
        'fiscal_year_id',
        'accounting_period_id',
        'invoice_date',
        'due_date',
        'payment_terms',
        'credit_days',
        'currency_id',
        'exchange_rate',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'outstanding_amount',
        'status',
        'journal_entry_id',
        'is_posted_to_gl',
        'posted_to_gl_at',
        'description',
        'notes',
        'terms_and_conditions',
        'billing_address',
        'shipping_address',
        'created_by',
        'updated_by',
        'issued_by',
        'issued_at',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'issued_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'posted_to_gl_at' => 'datetime',
        'exchange_rate' => 'decimal:6',
        'subtotal' => 'decimal:4',
        'discount_amount' => 'decimal:4',
        'tax_amount' => 'decimal:4',
        'total_amount' => 'decimal:4',
        'paid_amount' => 'decimal:4',
        'outstanding_amount' => 'decimal:4',
        'is_posted_to_gl' => 'boolean',
    ];

    /**
     * Calculate totals from line items.
     */
    public function calculateTotals(): void
    {
        $this->subtotal = $this->items()->sum('line_total');
        $this->tax_amount = $this->items()->sum('tax_amount');
        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount;
        $this->outstanding_amount = $this->total_amount - $this->paid_amount;
        $this->save();
    }

    /**
     * Check if invoice is fully paid.
     */
    public function isFullyPaid(): bool
    {
        return bccomp($this->paid_amount, $this->total_amount, 4) >= 0;
    }

    /**
     * Check if invoice is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'paid' &&
               $this->status !== 'cancelled' &&
               now()->gt($this->due_date);
    }

    /**
     * Update payment status based on paid amount.
     */
    public function updatePaymentStatus(): void
    {
        if ($this->isFullyPaid()) {
            $this->status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partially_paid';
        } elseif ($this->isOverdue()) {
            $this->status = 'overdue';
        }
        $this->save();
    }

    /**
     * Record a payment against this invoice.
     */
    public function recordPayment(float $amount): void
    {
        $this->paid_amount += $amount;
        $this->outstanding_amount = $this->total_amount - $this->paid_amount;
        $this->updatePaymentStatus();
    }

    // Relationships

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(\AzahariZaman\Backoffice\Models\BusinessPartner::class, 'customer_id');
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

    public function items(): HasMany
    {
        return $this->hasMany(SalesInvoiceItem::class)->orderBy('sort_order');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function paymentAllocations(): HasMany
    {
        return $this->hasMany(PaymentReceiptAllocation::class);
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(CustomerCreditNote::class);
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

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
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

    public function scopeIssued($query)
    {
        return $query->where('status', 'issued');
    }

    public function scopePartiallyPaid($query)
    {
        return $query->where('status', 'partially_paid');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->whereIn('status', ['issued', 'partially_paid'])
                    ->where('due_date', '<', now());
            });
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['issued', 'partially_paid', 'overdue']);
    }

    public function scopePostedToGl($query)
    {
        return $query->where('is_posted_to_gl', true);
    }
}
