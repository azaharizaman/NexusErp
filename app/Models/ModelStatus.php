<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class ModelStatus extends Model implements Sortable
{
    use HasFactory;
    use SortableTrait;

    protected $fillable = [
        'document_model_id',
        'name',
        'color',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public $sortable = [
        'order_column_name' => 'sort_order',
        'sort_when_creating' => true,
    ];

    /**
     * Get the document model that owns this status.
     */
    public function documentModel(): BelongsTo
    {
        return $this->belongsTo(DocumentModel::class);
    }

    /**
     * Get transitions from this status.
     */
    public function transitionsFrom(): HasMany
    {
        return $this->hasMany(StatusTransition::class, 'status_from_id');
    }

    /**
     * Get transitions to this status.
     */
    public function transitionsTo(): HasMany
    {
        return $this->hasMany(StatusTransition::class, 'status_to_id');
    }
}
