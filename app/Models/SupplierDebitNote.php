<?php

namespace App\Models;

use AzahariZaman\ControlledNumber\Traits\HasSerialNumbering;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierDebitNote extends Model
{
    use HasFactory;
    use HasSerialNumbering;
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
        'debit_note_date',
        'reason',
        'currency_id',
        'exchange_rate',
        'debit_amount',
        'journal_entry_id',
        'is_posted_to_gl',
        'posted_to_gl_at',
        'status',
        'description',
        'notes',
        'created_by',
        'updated_by',
        'approved_by',
        'approved_at',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'debit_note_date' => 'date',
        'approved_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'posted_to_gl_at' => 'datetime',
        'exchange_rate' => 'decimal:6',
        'debit_amount' => 'decimal:4',
        'is_posted_to_gl' => 'boolean',
    ];

    /**
     * Apply debit note to the supplier invoice.
     *
     * @throws \LogicException if debit note is not in issued status
     * @throws \LogicException if debit note is not linked to a supplier invoice
     * @throws \InvalidArgumentException if debit amount exceeds invoice outstanding amount
     */
    public function applyToInvoice(): void
    {
        if ($this->status !== 'issued') {
            throw new \LogicException('Only issued debit notes can be applied to invoices');
        }

        if (!$this->supplier_invoice_id) {
            throw new \LogicException('Debit note must be linked to a supplier invoice');
        }

        $invoice = $this->supplierInvoice;

        if ($this->debit_amount > $invoice->outstanding_amount) {
            throw new \InvalidArgumentException('Debit note amount exceeds invoice outstanding amount');
        }

        // Reduce invoice outstanding amount
        $invoice->outstanding_amount -= $this->debit_amount;
        $invoice->save();

        // Update the invoice payment status if it has an updatePaymentStatus method
        if (method_exists($invoice, 'updatePaymentStatus')) {
            $invoice->updatePaymentStatus();
        }

        $this->status = 'applied';
        $this->save();
    }

    /**
     * Check if debit note can be applied.
     */
    public function canBeApplied(): bool
    {
        return $this->status === 'issued' &&
               $this->supplier_invoice_id !== null &&
               $this->debit_amount > 0;
    }

    // Relationships

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(BusinessPartner::class, 'supplier_id');
    }

    public function supplierInvoice(): BelongsTo
    {
        return $this->belongsTo(SupplierInvoice::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    // Audit relationships

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    // Scopes

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeIssued($query)
    {
        return $query->where('status', 'issued');
    }

    public function scopeApplied($query)
    {
        return $query->where('status', 'applied');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeForSupplier($query, int $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForInvoice($query, int $supplierInvoiceId)
    {
        return $query->where('supplier_invoice_id', $supplierInvoiceId);
    }

    public function scopePostedToGl($query)
    {
        return $query->where('is_posted_to_gl', true);
    }

    public function scopeByReason($query, string $reason)
    {
        return $query->where('reason', $reason);
    }
}
