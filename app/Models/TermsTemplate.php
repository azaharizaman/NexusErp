<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TermsTemplate extends Model
{
    /** @use HasFactory<\Database\Factories\TermsTemplateFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'content',
        'category',
        'is_active',
        'is_default',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Scope for active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for default templates.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope by category.
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
