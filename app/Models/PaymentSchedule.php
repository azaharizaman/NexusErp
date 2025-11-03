<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\ModelStatus\HasStatuses;

class PaymentSchedule extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentScheduleFactory> */
    use HasFactory;

    use HasStatuses;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'supplier_id',
        'purchase_order_id',
        'currency_id',
        'milestone_description',
        'due_date',
        'scheduled_amount',
        'paid_amount',
        'remaining_amount',
        'status',
        'reminder_days_before',
        'reminder_sent',
        'reminder_sent_at',
        'notes',
        'payment_voucher_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'scheduled_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'reminder_sent' => 'boolean',
        'reminder_sent_at' => 'datetime',
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
     * Payment voucher relationship.
     */
    public function paymentVoucher(): BelongsTo
    {
        return $this->belongsTo(PaymentVoucher::class);
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
     * Scope for scheduled payments.
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope for overdue payments.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    /**
     * Scope for partially paid payments.
     */
    public function scopePartiallyPaid($query)
    {
        return $query->where('status', 'partially_paid');
    }

    /**
     * Scope for paid payments.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope for upcoming payments (within reminder window).
     */
    public function scopeUpcoming($query)
    {
        return $query->where('status', 'scheduled')
            ->whereDate('due_date', '<=', now()->addDays(30))
            ->whereDate('due_date', '>=', now());
    }

    /**
     * Check if payment is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date->isPast() && $this->status !== 'paid';
    }

    /**
     * Update remaining amount after payment.
     */
    public function updateRemainingAmount(): void
    {
        $this->remaining_amount = $this->scheduled_amount - $this->paid_amount;
        
        if ($this->remaining_amount <= 0) {
            $this->status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partially_paid';
        } elseif ($this->isOverdue()) {
            $this->status = 'overdue';
        }
        
        $this->save();
    }
}
