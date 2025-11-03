<?php

namespace App\Models;

use AzahariZaman\BackOffice\Traits\HasHierarchy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BusinessPartnerContact;

class BusinessPartner extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasHierarchy;

    protected $fillable = [
        'name',
        'code',
        'is_supplier',
        'is_customer',
        'email',
        'phone',
        'website',
        'notes',
        'parent_business_partner_id',
    ];

    protected $casts = [
        'is_supplier' => 'boolean',
        'is_customer' => 'boolean',
    ];

    protected $table = 'business_partners';

    /**
     * Override parent key name for HasHierarchy trait.
     */
    protected function getParentKeyName(): string
    {
        return 'parent_business_partner_id';
    }

    /**
     * Parent partner relationship.
     */
    public function parentPartner(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_business_partner_id');
    }

    /**
     * Child partners relationship.
     */
    public function childPartners(): HasMany
    {
        return $this->hasMany(self::class, 'parent_business_partner_id');
    }

    /**
     * Contacts relationship.
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(BusinessPartnerContact::class);
    }

    /**
     * Scope suppliers.
     */
    public function scopeSuppliers($query)
    {
        return $query->where('is_supplier', true);
    }

    /**
     * Scope customers.
     */
    public function scopeCustomers($query)
    {
        return $query->where('is_customer', true);
    }

    /**
     * Accessor for human readable partner type labels.
     *
     * @return array<int, string>
     */
    public function getTypeLabelsAttribute(): array
    {
        $labels = [];

        if ($this->is_supplier) {
            $labels[] = 'Supplier';
        }

        if ($this->is_customer) {
            $labels[] = 'Customer';
        }

        if (empty($labels)) {
            $labels[] = 'General';
        }

        return $labels;
    }
}
