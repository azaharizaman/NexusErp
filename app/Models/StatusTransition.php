<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StatusTransition extends Model
{
    use HasFactory;

    protected $fillable = [
        'status_from_id',
        'status_to_id',
        'condition',
    ];

    protected $casts = [
        'condition' => 'array',
    ];

    /**
     * Get the status this transition is from.
     */
    public function fromStatus(): BelongsTo
    {
        return $this->belongsTo(ModelStatus::class, 'status_from_id');
    }

    /**
     * Get the status this transition is to.
     */
    public function toStatus(): BelongsTo
    {
        return $this->belongsTo(ModelStatus::class, 'status_to_id');
    }

    /**
     * Get approval workflows for this transition.
     */
    public function approvalWorkflows(): HasMany
    {
        return $this->hasMany(ApprovalWorkflow::class);
    }
}
