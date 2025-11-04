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
        'description',
        'notes',
        'internal_notes',
        'bank_name',
        'bank_account_number',
        'cheque_number',
        'transaction_id',
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
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'voided_at' => 'datetime',
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
     * Payment schedules relationship.
     */
    public function paymentSchedules(): HasMany
    {
        return $this->hasMany(PaymentSchedule::class);
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
     * Check if voucher can be approved.
     */
    public function canApprove(): bool
    {
        return $this->latestStatus('submitted') !== null;
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
     * Get the current status for display purposes.
     */
    public function getStatusAttribute(): ?string
    {
        return $this->latestStatus();
    }
}
