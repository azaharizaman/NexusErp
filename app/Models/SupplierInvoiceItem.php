<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class SupplierInvoiceItem extends Model implements Sortable
{
    /** @use HasFactory<\Database\Factories\SupplierInvoiceItemFactory> */
    use HasFactory;
    use SortableTrait;

    protected $fillable = [
        'supplier_invoice_id',
        'purchase_order_item_id',
        'goods_received_note_item_id',
        'item_code',
        'item_description',
        'quantity',
        'uom_id',
        'unit_price',
        'line_total',
        'tax_rate',
        'tax_amount',
        'discount_percent',
        'discount_amount',
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
    ];

    public $sortable = [
        'order_column_name' => 'sort_order',
        'sort_when_creating' => true,
    ];

    /**
     * Supplier invoice relationship.
     */
    public function supplierInvoice(): BelongsTo
    {
        return $this->belongsTo(SupplierInvoice::class);
    }

    /**
     * Purchase order item relationship.
     */
    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    /**
     * GRN item relationship.
     */
    public function goodsReceivedNoteItem(): BelongsTo
    {
        return $this->belongsTo(GoodsReceivedNoteItem::class);
    }

    /**
     * UOM relationship.
     */
    public function uom(): BelongsTo
    {
        return $this->belongsTo(\AzahariZaman\Uom\Models\Uom::class);
    }

    /**
     * Calculate totals for this item.
     */
    public function calculateTotals(): void
    {
        $baseTotal = $this->quantity * $this->unit_price;
        $this->discount_amount = $baseTotal * ($this->discount_percent / 100);
        $afterDiscount = $baseTotal - $this->discount_amount;
        $this->tax_amount = $afterDiscount * ($this->tax_rate / 100);
        $this->line_total = $afterDiscount + $this->tax_amount;
        $this->save();
    }
}
