<?php

namespace App\Models;

use AzahariZaman\ControlledNumber\Traits\HasSerialNumbering;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\ModelStatus\HasStatuses;

class DebitNote extends Model
{
    /** @use HasFactory<\Database\Factories\DebitNoteFactory> */
    use HasFactory;
    use HasSerialNumbering;
    use HasStatuses;
    use SoftDeletes;

    /**
     * The column name for storing serial numbers.
     */
    protected string $serialColumn = 'debit_note_number';

    protected $fillable = [
        'debit_note_number',
        'company_id',
        'supplier_id',
        'supplier_invoice_id',
        'currency_id',
        'debit_note_date',
        'reason',
        'amount',
        'status',
        'description',
        'notes',
        'journal_entry_id',
        'is_posted_to_gl',
        'posted_to_gl_at',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'debit_note_date' => 'date',
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'is_posted_to_gl' => 'boolean',
        'posted_to_gl_at' => 'datetime',
    ];

    /**
     * Company relationship.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Supplier relationship.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(BusinessPartner::class, 'supplier_id');
    }

    /**
     * Supplier invoice relationship.
     */
    public function supplierInvoice(): BelongsTo
    {
        return $this->belongsTo(SupplierInvoice::class);
    }

    /**
     * Currency relationship.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Journal entry relationship.
     */
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    /**
     * Approver relationship.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Creator relationship.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Updater relationship.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope for draft debit notes.
     */
    /**
     * Scope for draft debit notes using Spatie ModelStatus.
     */
    public function scopeDraft($query)
    {
        return $query->withStatus('draft');
    }

    /**
     * Scope for approved debit notes.
     */
    /**
     * Scope for approved debit notes using Spatie ModelStatus.
     */
    public function scopeApproved($query)
    {
        return $query->withStatus('approved');
    }

    /**
     * Scope for debit notes posted to GL.
     */
    public function scopePostedToGl($query)
    {
        return $query->where('is_posted_to_gl', true);
    }
}
