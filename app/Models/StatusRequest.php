<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StatusRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_model_id',
        'model_type',
        'model_id',
        'current_status_id',
        'requested_status_id',
        'approvers',
        'is_approved',
        'requested_by',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'approvers' => 'array',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the document model for this request.
     */
    public function documentModel(): BelongsTo
    {
        return $this->belongsTo(DocumentModel::class);
    }

    /**
     * Get the model that this status request is for.
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the current status.
     */
    public function currentStatus(): BelongsTo
    {
        return $this->belongsTo(ModelStatus::class, 'current_status_id');
    }

    /**
     * Get the requested status.
     */
    public function requestedStatus(): BelongsTo
    {
        return $this->belongsTo(ModelStatus::class, 'requested_status_id');
    }

    /**
     * Get the user who requested this status change.
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the user who approved/rejected this status change.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
