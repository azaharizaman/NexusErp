<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class SalesInvoiceItem extends Model implements Sortable
{
    use HasFactory;
    use SortableTrait;

    public $sortable = [
        'order_column_name' => 'sort_order',
        'sort_when_creating' => true,
    ];

    protected $fillable = [
        'sales_invoice_id',
        'item_code',
        'item_description',
        'specifications',
        'quantity',
        'uom_id',
        'unit_price',
        'discount_percent',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'line_total',
        'revenue_account_id',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:4',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:4',
        'line_total' => 'decimal:4',
    ];

    /**
     * Calculate line total including discounts but excluding tax.
     */
    public function calculateLineTotal(): void
    {
        $subtotal = bcmul($this->quantity, $this->unit_price, 4);
        $discountAmount = $this->discount_percent > 0 
            ? bcmul($subtotal, bcdiv($this->discount_percent, 100, 6), 4)
            : $this->discount_amount;
        
        $this->discount_amount = $discountAmount;
        $this->line_total = bcsub($subtotal, $discountAmount, 4);
    }

    /**
     * Calculate tax amount based on tax rate and line total.
     */
    public function calculateTaxAmount(): void
    {
        if ($this->tax_rate > 0) {
            $this->tax_amount = bcmul($this->line_total, bcdiv($this->tax_rate, 100, 6), 4);
        }
    }

    /**
     * Calculate all amounts (line total and tax).
     */
    public function calculateAmounts(): void
    {
        $this->calculateLineTotal();
        $this->calculateTaxAmount();
        $this->save();
    }

    /**
     * Get the total amount including tax.
     */
    public function getTotalWithTaxAttribute(): float
    {
        return bcadd($this->line_total, $this->tax_amount, 4);
    }

    // Relationships

    public function salesInvoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(\AzahariZaman\Backoffice\Models\UnitOfMeasure::class, 'uom_id');
    }

    public function revenueAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'revenue_account_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
