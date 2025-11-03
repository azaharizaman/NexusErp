<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\ModelStatus\HasStatuses;

class RequestForQuotation extends Model
{
    use HasFactory;
    use HasStatuses;
    use SoftDeletes;

    protected $fillable = [
        'rfq_number',
        'company_id',
        'rfq_date',
        'expiry_date',
        'status',
        'description',
        'terms_and_conditions',
        'currency_id',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'rfq_date' => 'date',
        'expiry_date' => 'date',
    ];

    /**
     * Boot method to generate RFQ number.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->rfq_number)) {
                $model->rfq_number = 'RFQ-' . str_pad(
                    self::max('id') + 1,
                    6,
                    '0',
                    STR_PAD_LEFT
                );
            }
        });
    }

    /**
     * Company relationship.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Currency relationship.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Purchase requests relationship (many-to-many).
     */
    public function purchaseRequests(): BelongsToMany
    {
        return $this->belongsToMany(PurchaseRequest::class, 'purchase_request_rfq')
            ->withTimestamps();
    }

    /**
     * Invited suppliers relationship.
     */
    public function invitedSuppliers(): BelongsToMany
    {
        return $this->belongsToMany(BusinessPartner::class, 'rfq_suppliers')
            ->withPivot(['status', 'invited_at', 'responded_at', 'notes'])
            ->withTimestamps();
    }

    /**
     * Quotations received for this RFQ.
     */
    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    /**
     * Purchase recommendations for this RFQ.
     */
    public function purchaseRecommendations(): HasMany
    {
        return $this->hasMany(PurchaseRecommendation::class);
    }

    /**
     * Scope for draft RFQs.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope for sent RFQs.
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope for received RFQs.
     */
    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    /**
     * Scope for evaluated RFQs.
     */
    public function scopeEvaluated($query)
    {
        return $query->where('status', 'evaluated');
    }
}
