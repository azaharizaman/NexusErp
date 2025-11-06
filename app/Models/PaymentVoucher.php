<?php

namespace App\Models;

use AzahariZaman\ControlledNumber\Traits\HasSerialNumbering;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\ModelStatus\HasStatuses;

class PaymentVoucher extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentVoucherFactory> */
    use HasFactory;
    use HasSerialNumbering;
    use HasStatuses;
    use SoftDeletes;

    /**
     * The column name for storing serial numbers.
     */
    protected string $serialColumn = 'voucher_number';

    protected $fillable = [
        'voucher_number',
        'company_id',
        'supplier_id',
        'supplier_invoice_id',
        'currency_id',
        'payment_date',
        'payment_method',
        'reference_number',
        'amount',
        'exchange_rate',
        'allocated_amount',
        'unallocated_amount',
        'description',
        'notes',
        'internal_notes',
        'bank_name',
        'bank_account_number',
        'cheque_number',
        'cheque_date',
        'transaction_id',
        'is_on_hold',
        'journal_entry_id',
        'is_posted_to_gl',
        'posted_to_gl_at',
        'requested_by',
        'approved_by',
        'approved_at',
        'paid_by',
        'paid_at',
        'voided_by',
        'voided_at',
        'void_reason',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'cheque_date' => 'date',
        'amount' => 'decimal:4',
        'exchange_rate' => 'decimal:6',
        'allocated_amount' => 'decimal:4',
        'unallocated_amount' => 'decimal:4',
        'is_on_hold' => 'boolean',
        'is_posted_to_gl' => 'boolean',
        'posted_to_gl_at' => 'datetime',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'voided_at' => 'datetime',
    ];

    /**
     * Allocate payment to a supplier invoice.
     *
     * @throws \InvalidArgumentException
     */
    public function allocateToInvoice(SupplierInvoice $invoice, float $allocationAmount): PaymentVoucherAllocation
    {
        if ($allocationAmount > $this->unallocated_amount) {
            throw new \InvalidArgumentException('Allocation amount exceeds unallocated payment amount');
        }

        if ($allocationAmount > $invoice->outstanding_amount) {
            throw new \InvalidArgumentException('Allocation amount exceeds invoice outstanding amount');
        }

        $allocation = $this->allocations()->create([
            'supplier_invoice_id' => $invoice->id,
            'allocated_amount' => $allocationAmount,
        ]);

        $this->allocated_amount += $allocationAmount;
        $this->unallocated_amount = $this->amount - $this->allocated_amount;
        $this->save();

        // Update invoice paid amount
        // TODO: Consider extracting this to SupplierInvoice::recordPayment() method
        if (method_exists($invoice, 'recordPayment')) {
            $invoice->recordPayment(round($allocationAmount, 2));
        } else {
            $roundedAllocation = round($allocationAmount, 2);
            $invoice->paid_amount = round($invoice->paid_amount + $roundedAllocation, 2);
            $invoice->outstanding_amount = round($invoice->outstanding_amount - $roundedAllocation, 2);
            $invoice->save();
        }

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
     * Supplier invoice relationship.
     */
    public function supplierInvoice(): BelongsTo
    {
        return $this->belongsTo(SupplierInvoice::class);
    }

    /**
     * Currency relationship.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Journal entry relationship.
     */
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    /**
     * Payment voucher allocations relationship.
     */
    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentVoucherAllocation::class);
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
     * Payer relationship.
     */
    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    /**
     * Voider relationship.
     */
    public function voider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
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
     * Holder relationship.
     */
    public function holder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'held_by');
    }

    /**
     * Scope for draft vouchers using Spatie ModelStatus.
     */
    public function scopeDraft($query)
    {
        return $query->currentStatus('draft');
    }

    /**
     * Scope for submitted vouchers using Spatie ModelStatus.
     */
    public function scopeSubmitted($query)
    {
        return $query->currentStatus('submitted');
    }

    /**
     * Scope for approved vouchers using Spatie ModelStatus.
     */
    public function scopeApproved($query)
    {
        return $query->currentStatus('approved');
    }

    /**
     * Scope for paid vouchers using Spatie ModelStatus.
     */
    public function scopePaid($query)
    {
        return $query->currentStatus('paid');
    }

    /**
     * Scope for voided vouchers using Spatie ModelStatus.
     */
    public function scopeVoided($query)
    {
        return $query->currentStatus('voided');
    }

    /**
     * Scope for vouchers posted to GL.
     */
    public function scopePostedToGl($query)
    {
        return $query->where('is_posted_to_gl', true);
    }

    /**
     * Check if voucher can be approved.
     */
    public function canApprove(): bool
    {
        return $this->latestStatus() === 'submitted';
    }

    /**
     * Check if voucher can be paid.
     */
    public function canPay(): bool
    {
        return $this->latestStatus() === 'approved';
    }

    /**
     * Check if voucher can be voided.
     */
    public function canVoid(): bool
    {
        return in_array($this->latestStatus(), ['draft', 'submitted', 'approved']);
    }

    /**
     * Scope for vouchers on hold.
     */
    public function scopeOnHold($query)
    {
        return $query->where('is_on_hold', true);
    }

    /**
     * Scope for vouchers not on hold.
     */
    public function scopeNotOnHold($query)
    {
        return $query->where('is_on_hold', false);
    }

    /**
     * Scope for unallocated vouchers.
     */
    public function scopeUnallocated($query)
    {
        return $query->where('unallocated_amount', '>', 0);
    }
}
