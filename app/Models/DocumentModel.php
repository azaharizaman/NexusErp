<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'model_class',
        'description',
    ];

    /**
     * Get all statuses for this document model.
     */
    public function statuses(): HasMany
    {
        return $this->hasMany(ModelStatus::class)->orderBy('sort_order');
    }

    /**
     * Get all status requests for this document model.
     */
    public function statusRequests(): HasMany
    {
        return $this->hasMany(StatusRequest::class);
    }
}
