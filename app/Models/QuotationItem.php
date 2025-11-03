<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class QuotationItem extends Model implements Sortable
{
    use HasFactory;
    use SortableTrait;

    protected $fillable = [
        'quotation_id',
        'item_description',
        'item_code',
        'quantity',
        'uom_id',
        'unit_price',
        'line_total',
        'tax_rate',
        'tax_amount',
        'specifications',
        'notes',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
    ];

    public $sortable = [
        'order_column_name' => 'sort_order',
        'sort_when_creating' => true,
    ];

    /**
     * Quotation relationship.
     */
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    /**
     * Calculate line total and tax.
     */
    public function calculateTotals(): void
    {
        if ($this->unit_price && $this->quantity) {
            $this->line_total = $this->unit_price * $this->quantity;
            $this->tax_amount = $this->line_total * ($this->tax_rate / 100);
            $this->save();
        }
    }
}
