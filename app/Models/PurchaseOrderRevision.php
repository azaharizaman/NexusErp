<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderRevision extends Model
{
    /** @use HasFactory<\Database\Factories\PurchaseOrderRevisionFactory> */
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'revision_number',
        'revision_type',
        'reason',
        'description',
        'changes',
        'revised_by',
        'revised_at',
        'approved_by',
        'approved_at',
        'status',
    ];

    protected $casts = [
        'changes' => 'array',
        'revised_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * Purchase order relationship.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Reviser relationship.
     */
    public function reviser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revised_by');
    }

    /**
     * Approver relationship.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Track changes between old and new values.
     */
    public static function trackChanges(PurchaseOrder $purchaseOrder, array $oldValues, array $newValues): array
    {
        $changes = [];

        foreach ($newValues as $key => $newValue) {
            $oldValue = $oldValues[$key] ?? null;

            if ($oldValue != $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
    }

    /**
     * Scope for approved revisions.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for draft revisions.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }
}
