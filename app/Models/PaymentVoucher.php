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
        'purchase_order_id',
        'currency_id',
        'payment_date',
        'value_date',
        'payment_method',
        'payment_reference',
        'bank_account',
        'amount',
        'exchange_rate',
        'base_amount',
        'status',
        'description',
        'notes',
        'internal_notes',
        'requested_by',
        'approved_by',
        'approved_at',
        'paid_by',
        'paid_at',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'value_date' => 'date',
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'base_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
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
     * Ledger entries relationship.
     */
    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(PayableLedger::class);
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
     * Paid by relationship.
     */
    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    /**
     * Cancelled by relationship.
     */
    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
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
     * Scope for draft vouchers.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope for pending approval vouchers.
     */
    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    /**
     * Scope for approved vouchers.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for paid vouchers.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope for cancelled vouchers.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Calculate base amount using exchange rate.
     */
    public function calculateBaseAmount(): void
    {
        $this->base_amount = $this->amount * $this->exchange_rate;
        $this->save();
    }

    /**
     * Check if voucher can be edited.
     */
    public function canEdit(): bool
    {
        return in_array($this->status, ['draft']);
    }

    /**
     * Check if voucher can be approved.
     */
    public function canApprove(): bool
    {
        return $this->status === 'pending_approval';
    }

    /**
     * Check if voucher can be paid.
     */
    public function canPay(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if voucher can be cancelled.
     */
    public function canCancel(): bool
    {
        return in_array($this->status, ['draft', 'pending_approval', 'approved']);
    }
}
