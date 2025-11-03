<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxRule extends Model
{
    /** @use HasFactory<\Database\Factories\TaxRuleFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'rate',
        'type',
        'description',
        'is_active',
        'is_compound',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'is_active' => 'boolean',
        'is_compound' => 'boolean',
    ];

    /**
     * Scope for active tax rules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Calculate tax amount for a given base amount.
     */
    public function calculateTax(float $baseAmount): float
    {
        if ($this->type === 'percentage') {
            return $baseAmount * ($this->rate / 100);
        }

        return (float) $this->rate;
    }
}
