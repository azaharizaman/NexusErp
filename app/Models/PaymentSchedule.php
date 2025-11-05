<?php

namespace App\Models;

use AzahariZaman\ControlledNumber\Traits\HasSerialNumbering;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\ModelStatus\HasStatuses;

class PaymentSchedule extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentScheduleFactory> */
    use HasFactory;
    use HasSerialNumbering;
    use HasStatuses;
    use SoftDeletes;

    /**
     * The column name for storing serial numbers.
     */
    protected string $serialColumn = 'schedule_number';

    protected $fillable = [
        'schedule_number',
        'company_id',
        'supplier_id',
        'purchase_order_id',
        'supplier_invoice_id',
        'payment_voucher_id',
        'currency_id',
        'due_date',
        'amount',
        'paid_amount',
        'outstanding_amount',
        'milestone',
        'description',
        'notes',
        'reminder_sent_at',
        'completed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'outstanding_amount' => 'decimal:2',
        'reminder_sent_at' => 'datetime',
        'completed_at' => 'datetime',
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
     * Supplier invoice relationship.
     */
    public function supplierInvoice(): BelongsTo
    {
        return $this->belongsTo(SupplierInvoice::class);
    }

    /**
     * Payment voucher relationship.
     */
    public function paymentVoucher(): BelongsTo
    {
        return $this->belongsTo(PaymentVoucher::class);
    }

    /**
     * Currency relationship.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
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
     * Scope for pending schedules using Spatie ModelStatus.
     */
    public function scopePending($query)
    {
        return $query->currentStatus('pending');
    }

    /**
     * Scope for partial schedules using Spatie ModelStatus.
     */
    public function scopePartial($query)
    {
        return $query->currentStatus('partial');
    }

    /**
     * Scope for completed schedules using Spatie ModelStatus.
     */
    public function scopeCompleted($query)
    {
        return $query->currentStatus('completed');
    }

    /**
     * Scope for overdue schedules.
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereHas('statuses', function ($q) {
                $q->where('name', 'pending')
                    ->orWhere('name', 'partial');
            });
    }

    /**
     * Scope for upcoming schedules (due within next N days).
     */
    public function scopeUpcoming($query, int $days = 7)
    {
        return $query->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addDays($days))
            ->whereHas('statuses', function ($q) {
                $q->where('name', 'pending')
                    ->orWhere('name', 'partial');
            });
    }

    /**
     * Check if schedule is overdue.
     */
    public function isOverdue(): bool
    {
        $currentStatus = $this->latestStatus();
        return $this->due_date < now() &&
               $currentStatus &&
               in_array($currentStatus, ['pending', 'partial']);
    }

    /**
     * Update outstanding amount based on paid amount.
     */
    public function updateOutstanding(): void
    {
        $this->outstanding_amount = $this->amount - $this->paid_amount;
        $this->save();
    }
}
