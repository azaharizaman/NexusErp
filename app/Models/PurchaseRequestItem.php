<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class PurchaseRequestItem extends Model implements Sortable
{
    /** @use HasFactory<\Database\Factories\PurchaseRequestItemFactory> */
    use HasFactory;

    use SortableTrait;

    protected $fillable = [
        'purchase_request_id',
        'item_description',
        'item_code',
        'quantity',
        'uom_id',
        'estimated_unit_price',
        'estimated_total',
        'specifications',
        'notes',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'estimated_unit_price' => 'decimal:2',
        'estimated_total' => 'decimal:2',
    ];

    public $sortable = [
        'order_column_name' => 'sort_order',
        'sort_when_creating' => true,
    ];

    /**
     * Purchase request relationship.
     */
    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    /**
     * Unit of measure relationship.
     * TODO: Check correct model name from UOM package
     */
    // public function uom(): BelongsTo
    // {
    //     return $this->belongsTo(Uom::class);
    // }

    /**
     * Calculate item total.
     */
    public function calculateTotal(): void
    {
        if ($this->estimated_unit_price && $this->quantity) {
            $this->estimated_total = $this->estimated_unit_price * $this->quantity;
            $this->save();
        }
    }
}
