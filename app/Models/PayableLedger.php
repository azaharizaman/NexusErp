<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayableLedger extends Model
{
    /** @use HasFactory<\Database\Factories\PayableLedgerFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'supplier_id',
        'supplier_invoice_id',
        'payment_voucher_id',
        'base_currency_id',
        'foreign_currency_id',
        'transaction_date',
        'transaction_type',
        'debit_amount_base',
        'credit_amount_base',
        'debit_amount_foreign',
        'credit_amount_foreign',
        'exchange_rate',
        'exchange_rate_date',
        'balance_base',
        'balance_foreign',
        'reference_number',
        'description',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'debit_amount_base' => 'decimal:2',
        'credit_amount_base' => 'decimal:2',
        'debit_amount_foreign' => 'decimal:2',
        'credit_amount_foreign' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'exchange_rate_date' => 'date',
        'balance_base' => 'decimal:2',
        'balance_foreign' => 'decimal:2',
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
     * Payment voucher relationship.
     */
    public function paymentVoucher(): BelongsTo
    {
        return $this->belongsTo(PaymentVoucher::class);
    }

    /**
     * Base currency relationship.
     */
    public function baseCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'base_currency_id');
    }

    /**
     * Foreign currency relationship.
     */
    public function foreignCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'foreign_currency_id');
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
     * Scope for outstanding payables (debit entries with positive balance).
     */
    public function scopeOutstanding($query)
    {
        return $query->where('balance_base', '>', 0);
    }

    /**
     * Scope for paid payables (credit entries or zero balance).
     */
    public function scopePaid($query)
    {
        return $query->where('balance_base', '<=', 0);
    }

    /**
     * Scope for overdue payables.
     */
    public function scopeOverdue($query)
    {
        return $query->where('balance_base', '>', 0)
            ->whereHas('supplierInvoice', function ($q) {
                $q->where('due_date', '<', now());
            });
    }

    /**
     * Scope for ledger entries by supplier.
     */
    public function scopeBySupplier($query, int $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    /**
     * Scope for ledger entries by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Convert foreign amount to base currency.
     */
    public function convertToBase(float $foreignAmount): float
    {
        if ($this->exchange_rate) {
            return round($foreignAmount * $this->exchange_rate, 2);
        }

        return $foreignAmount;
    }

    /**
     * Convert base amount to foreign currency.
     */
    public function convertToForeign(float $baseAmount): float
    {
        if ($this->exchange_rate && $this->exchange_rate > 0) {
            return round($baseAmount / $this->exchange_rate, 2);
        }

        return $baseAmount;
    }

    /**
     * Calculate running balance for supplier.
     */
    public static function calculateBalance(int $supplierId, ?string $date = null): float
    {
        $query = static::where('supplier_id', $supplierId);

        if ($date) {
            $query->where('transaction_date', '<=', $date);
        }

        $debits = $query->sum('debit_amount_base');
        $credits = $query->sum('credit_amount_base');

        return $debits - $credits;
    }
}
