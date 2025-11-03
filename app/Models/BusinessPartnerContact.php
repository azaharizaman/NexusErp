<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessPartnerContact extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'business_partner_id',
        'name',
        'job_title',
        'email',
        'phone',
        'mobile',
        'notes',
    ];

    protected $table = 'business_partner_contacts';

    /**
     * Owning partner relationship.
     */
    public function businessPartner(): BelongsTo
    {
        return $this->belongsTo(BusinessPartner::class);
    }
}
