<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExchangeRate extends Model
{
    /** @use HasFactory<\Database\Factories\ExchangeRateFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'from_currency_id',
        'to_currency_id',
        'rate',
        'effective_date',
        'expiry_date',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'rate' => 'decimal:10',
        'effective_date' => 'date',
        'expiry_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Source currency relationship.
     */
    public function fromCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'from_currency_id');
    }

    /**
     * Target currency relationship.
     */
    public function toCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'to_currency_id');
    }

    /**
     * Scope for active exchange rates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for current exchange rates (effective and not expired).
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_active', true)
            ->where('effective_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>=', now());
            });
    }
}
