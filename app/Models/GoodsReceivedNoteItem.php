<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class GoodsReceivedNoteItem extends Model implements Sortable
{
    /** @use HasFactory<\Database\Factories\GoodsReceivedNoteItemFactory> */
    use HasFactory;
    use SortableTrait;

    protected $fillable = [
        'goods_received_note_id',
        'purchase_order_item_id',
        'item_code',
        'item_description',
        'ordered_quantity',
        'received_quantity',
        'rejected_quantity',
        'uom_id',
        'batch_number',
        'serial_numbers',
        'expiry_date',
        'notes',
        'sort_order',
    ];

    protected $casts = [
        'ordered_quantity' => 'decimal:3',
        'received_quantity' => 'decimal:3',
        'rejected_quantity' => 'decimal:3',
        'expiry_date' => 'date',
        'serial_numbers' => 'array',
    ];

    public $sortable = [
        'order_column_name' => 'sort_order',
        'sort_when_creating' => true,
    ];

    /**
     * GRN relationship.
     */
    public function goodsReceivedNote(): BelongsTo
    {
        return $this->belongsTo(GoodsReceivedNote::class);
    }

    /**
     * Purchase order item relationship.
     */
    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    /**
     * UOM relationship.
     */
    public function uom(): BelongsTo
    {
        return $this->belongsTo(\AzahariZaman\Uom\Models\Uom::class);
    }
}
