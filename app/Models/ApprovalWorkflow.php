<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalWorkflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'status_transition_id',
        'required_roles',
        'staff_ids',
        'approval_type',
    ];

    protected $casts = [
        'required_roles' => 'array',
        'staff_ids' => 'array',
    ];

    /**
     * Get the status transition for this workflow.
     */
    public function statusTransition(): BelongsTo
    {
        return $this->belongsTo(StatusTransition::class);
    }
}
