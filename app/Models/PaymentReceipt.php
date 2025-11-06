<?php

namespace App\Models;

use AzahariZaman\ControlledNumber\Traits\HasSerialNumbering;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\ModelStatus\HasStatuses;

class PaymentReceipt extends Model
{
    use HasFactory;
    use HasSerialNumbering;
    use HasStatuses;
    use SoftDeletes;

    /**
     * The column name for storing serial numbers.
     */
    protected string $serialColumn = 'receipt_number';

    protected $fillable = [
        'receipt_number',
        'company_id',
        'customer_id',
        'payment_date',
        'payment_method',
        'reference_number',
        'bank_name',
        'bank_account_number',
        'cheque_number',
        'cheque_date',
        'transaction_id',
        'currency_id',
        'exchange_rate',
        'amount',
        'allocated_amount',
        'unallocated_amount',
        'journal_entry_id',
        'is_posted_to_gl',
        'posted_to_gl_at',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'cheque_date' => 'date',
        'posted_to_gl_at' => 'datetime',
        'exchange_rate' => 'decimal:6',
        'amount' => 'decimal:4',
        'allocated_amount' => 'decimal:4',
        'unallocated_amount' => 'decimal:4',
        'is_posted_to_gl' => 'boolean',
    ];

    /**
     * Allocate payment to an invoice.
     */
    public function allocateToInvoice(SalesInvoice $invoice, float $allocationAmount): PaymentReceiptAllocation
    {
        if ($allocationAmount > $this->unallocated_amount) {
            throw new \InvalidArgumentException('Allocation amount exceeds unallocated payment amount');
        }

        if ($allocationAmount > $invoice->outstanding_amount) {
            throw new \InvalidArgumentException('Allocation amount exceeds invoice outstanding amount');
        }

        $allocation = $this->allocations()->create([
            'sales_invoice_id' => $invoice->id,
            'allocated_amount' => $allocationAmount,
        ]);

        $this->allocated_amount += $allocationAmount;
        $this->unallocated_amount = $this->amount - $this->allocated_amount;
        $this->save();

        $invoice->recordPayment($allocationAmount);

        return $allocation;
    }

    /**
     * Check if payment is fully allocated.
     */
    public function isFullyAllocated(): bool
    {
        return bccomp($this->unallocated_amount, '0', 4) <= 0;
    }

    /**
     * Calculate total allocated amount from allocations.
     */
    public function recalculateAllocations(): void
    {
        $this->allocated_amount = $this->allocations()->sum('allocated_amount');
        $this->unallocated_amount = $this->amount - $this->allocated_amount;
        $this->save();
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

    public function currency(): BelongsTo
    {
        return $this->belongsTo(\AzahariZaman\Backoffice\Models\Currency::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentReceiptAllocation::class);
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

    // Scopes

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeCleared($query)
    {
        return $query->where('status', 'cleared');
    }

    public function scopeBounced($query)
    {
        return $query->where('status', 'bounced');
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeUnallocated($query)
    {
        return $query->where('unallocated_amount', '>', 0);
    }

    public function scopePostedToGl($query)
    {
        return $query->where('is_posted_to_gl', true);
    }

    public function scopeByPaymentMethod($query, string $paymentMethod)
    {
        return $query->where('payment_method', $paymentMethod);
    }
}
