<?php

namespace App\Models;

use AzahariZaman\ControlledNumber\Traits\HasSerialNumbering;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\ModelStatus\HasStatuses;

class CustomerCreditNote extends Model
{
    use HasFactory;
    use HasSerialNumbering;
    use HasStatuses;
    use SoftDeletes;

    /**
     * The column name for storing serial numbers.
     */
    protected string $serialColumn = 'credit_note_number';

    protected $fillable = [
        'credit_note_number',
        'company_id',
        'customer_id',
        'sales_invoice_id',
        'fiscal_year_id',
        'accounting_period_id',
        'credit_note_date',
        'reason',
        'currency_id',
        'exchange_rate',
        'amount',
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
        'credit_note_date' => 'date',
        'approved_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'posted_to_gl_at' => 'datetime',
        'exchange_rate' => 'decimal:6',
        'amount' => 'decimal:4',
        'is_posted_to_gl' => 'boolean',
    ];

    /**
     * Apply credit note to the sales invoice.
     */
    public function applyToInvoice(): void
    {
        if ($this->status !== 'issued') {
            throw new \LogicException('Only issued credit notes can be applied to invoices');
        }

        if (!$this->sales_invoice_id) {
            throw new \LogicException('Credit note must be linked to a sales invoice');
        }

        $invoice = $this->salesInvoice;

        if ($this->amount > $invoice->outstanding_amount) {
            throw new \InvalidArgumentException('Credit note amount exceeds invoice outstanding amount');
        }

        // Reduce invoice outstanding amount
        $invoice->outstanding_amount -= $this->amount;
        $invoice->updatePaymentStatus();

        $this->status = 'applied';
        $this->save();
    }

    /**
     * Check if credit note can be applied.
     */
    public function canBeApplied(): bool
    {
        return $this->status === 'issued' &&
               $this->sales_invoice_id !== null &&
               $this->amount > 0;
    }

    // Relationships

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(\AzahariZaman\Backoffice\Models\BusinessPartner::class, 'customer_id');
    }

    public function salesInvoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function accountingPeriod(): BelongsTo
    {
        return $this->belongsTo(AccountingPeriod::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(\AzahariZaman\Backoffice\Models\Currency::class);
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

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForInvoice($query, int $salesInvoiceId)
    {
        return $query->where('sales_invoice_id', $salesInvoiceId);
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
