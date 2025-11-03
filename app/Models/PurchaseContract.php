<?php

namespace App\Models;

use AzahariZaman\ControlledNumber\Traits\HasSerialNumbering;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\ModelStatus\HasStatuses;

class PurchaseContract extends Model
{
    /** @use HasFactory<\Database\Factories\PurchaseContractFactory> */
    use HasFactory;
    use HasSerialNumbering;
    use HasStatuses;
    use SoftDeletes;

    /**
     * The column name for storing serial numbers.
     */
    protected string $serialColumn = 'contract_number';

    protected $fillable = [
        'contract_number',
        'company_id',
        'supplier_id',
        'currency_id',
        'contract_type',
        'contract_name',
        'description',
        'start_date',
        'end_date',
        'renewal_date',
        'contract_value',
        'utilized_value',
        'remaining_value',
        'status',
        'terms_and_conditions',
        'notes',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'renewal_date' => 'date',
        'contract_value' => 'decimal:2',
        'utilized_value' => 'decimal:2',
        'remaining_value' => 'decimal:2',
        'approved_at' => 'datetime',
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
     * Currency relationship.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Purchase orders relationship.
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Approver relationship.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
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
     * Scope for active contracts.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    /**
     * Scope for expired contracts.
     */
    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', now());
    }

    /**
     * Scope for expiring soon.
     */
    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->where('status', 'active')
            ->whereBetween('end_date', [now(), now()->addDays($days)]);
    }

    /**
     * Update utilized and remaining values.
     */
    public function updateUtilization(): void
    {
        $this->utilized_value = $this->purchaseOrders()
            ->whereIn('status', ['approved', 'issued', 'closed'])
            ->sum('total_amount');
        
        if ($this->contract_value) {
            $this->remaining_value = $this->contract_value - $this->utilized_value;
        }
        
        $this->save();
    }

    /**
     * Check if contract is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' 
            && $this->start_date <= now()
            && $this->end_date >= now();
    }

    /**
     * Check if contract has available budget.
     */
    public function hasAvailableBudget(float $amount = 0): bool
    {
        if (!$this->contract_value) {
            return true; // No limit
        }
        
        return ($this->remaining_value ?? $this->contract_value) >= $amount;
    }
}
