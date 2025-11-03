<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class PurchaseOrderItem extends Model implements Sortable
{
    /** @use HasFactory<\Database\Factories\PurchaseOrderItemFactory> */
    use HasFactory;
    use SortableTrait;

    protected $fillable = [
        'purchase_order_id',
        'item_code',
        'item_description',
        'specifications',
        'quantity',
        'uom_id',
        'unit_price',
        'line_total',
        'tax_rate',
        'tax_amount',
        'discount_percent',
        'discount_amount',
        'expected_delivery_date',
        'received_quantity',
        'notes',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'received_quantity' => 'decimal:3',
        'expected_delivery_date' => 'date',
    ];

    /**
     * Sortable configuration.
     */
    public array $sortable = [
        'order_column_name' => 'sort_order',
        'sort_when_creating' => true,
    ];

    /**
     * Purchase order relationship.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Unit of measure relationship.
     */
    public function uom(): BelongsTo
    {
        return $this->belongsTo(\AzahariZaman\UomManagement\Models\Uom::class);
    }

    /**
     * Calculate line total, tax and discount.
     */
    public function calculateTotals(): void
    {
        // Calculate base line total
        $baseTotal = $this->quantity * $this->unit_price;

        // Calculate discount
        if ($this->discount_percent > 0) {
            $this->discount_amount = $baseTotal * ($this->discount_percent / 100);
        }

        // Calculate line total after discount
        $this->line_total = $baseTotal - $this->discount_amount;

        // Calculate tax
        if ($this->tax_rate > 0) {
            $this->tax_amount = $this->line_total * ($this->tax_rate / 100);
        }

        $this->save();
    }

    /**
     * Get remaining quantity to be received.
     */
    public function getRemainingQuantityAttribute(): float
    {
        return $this->quantity - $this->received_quantity;
    }

    /**
     * Check if item is fully received.
     */
    public function isFullyReceived(): bool
    {
        return $this->received_quantity >= $this->quantity;
    }
}
