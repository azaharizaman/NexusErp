<?php

namespace App\Models;

use AzahariZaman\ControlledNumber\Traits\HasSerialNumbering;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliverySchedule extends Model
{
    /** @use HasFactory<\Database\Factories\DeliveryScheduleFactory> */
    use HasFactory;
    use HasSerialNumbering;

    /**
     * The column name for storing serial numbers.
     */
    protected string $serialColumn = 'schedule_number';

    protected $fillable = [
        'purchase_order_id',
        'purchase_order_item_id',
        'schedule_number',
        'scheduled_date',
        'expected_date',
        'actual_delivery_date',
        'scheduled_quantity',
        'delivered_quantity',
        'remaining_quantity',
        'status',
        'delivery_location',
        'tracking_number',
        'notes',
        'delivery_instructions',
        'reminder_days_before',
        'reminder_sent_at',
        'confirmed_by',
        'confirmed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'expected_date' => 'date',
        'actual_delivery_date' => 'date',
        'scheduled_quantity' => 'decimal:3',
        'delivered_quantity' => 'decimal:3',
        'remaining_quantity' => 'decimal:3',
        'reminder_sent_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    /**
     * Purchase order relationship.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Purchase order item relationship.
     */
    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    /**
     * Confirmer relationship.
     */
    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
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
     * Scope for scheduled deliveries.
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope for delivered schedules.
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Scope for overdue deliveries.
     */
    public function scopeOverdue($query)
    {
        return $query->whereIn('status', ['scheduled', 'confirmed', 'in_transit'])
            ->where('scheduled_date', '<', now());
    }

    /**
     * Scope for upcoming deliveries.
     */
    public function scopeUpcoming($query, int $days = 7)
    {
        return $query->whereIn('status', ['scheduled', 'confirmed'])
            ->whereBetween('scheduled_date', [now(), now()->addDays($days)]);
    }

    /**
     * Update remaining quantity.
     */
    public function updateRemainingQuantity(): void
    {
        $this->remaining_quantity = $this->scheduled_quantity - $this->delivered_quantity;
        $this->save();
    }

    /**
     * Check if delivery is overdue.
     */
    public function isOverdue(): bool
    {
        return in_array($this->status, ['scheduled', 'confirmed', 'in_transit'])
            && $this->scheduled_date < now();
    }

    /**
     * Check if fully delivered.
     */
    public function isFullyDelivered(): bool
    {
        return $this->delivered_quantity >= $this->scheduled_quantity;
    }

    /**
     * Check if reminder should be sent.
     */
    public function shouldSendReminder(): bool
    {
        if ($this->reminder_sent_at) {
            return false;
        }

        $reminderDate = $this->scheduled_date->subDays($this->reminder_days_before);
        
        return now()->greaterThanOrEqualTo($reminderDate);
    }
}
