<?php

namespace App\Models;

use AzahariZaman\ControlledNumber\Traits\HasSerialNumbering;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\ModelStatus\HasStatuses;

class GoodsReceivedNote extends Model
{
    /** @use HasFactory<\Database\Factories\GoodsReceivedNoteFactory> */
    use HasFactory;
    use HasSerialNumbering;
    use HasStatuses;
    use SoftDeletes;

    /**
     * The column name for storing serial numbers.
     */
    protected string $serialColumn = 'grn_number';

    protected $fillable = [
        'grn_number',
        'company_id',
        'purchase_order_id',
        'supplier_id',
        'warehouse_location',
        'received_date',
        'received_by',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'received_date' => 'date',
    ];

    /**
     * Company relationship.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Purchase order relationship.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Supplier relationship.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(BusinessPartner::class, 'supplier_id');
    }

    /**
     * GRN items relationship.
     */
    public function items(): HasMany
    {
        return $this->hasMany(GoodsReceivedNoteItem::class)->orderBy('sort_order');
    }

    /**
     * Receiver relationship.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
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
     * Scope for draft GRNs using Spatie ModelStatus.
     */
    public function scopeDraft($query)
    {
        return $query->currentStatus('draft');
    }

    /**
     * Scope for completed GRNs using Spatie ModelStatus.
     */
    public function scopeCompleted($query)
    {
        return $query->currentStatus('completed');
    }
}
