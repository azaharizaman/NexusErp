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
        'currency_id',
        'base_currency_id',
        'purchase_order_id',
        'payment_voucher_id',
        'transaction_date',
        'transaction_type',
        'reference_number',
        'debit_amount',
        'credit_amount',
        'balance',
        'exchange_rate',
        'base_debit_amount',
        'base_credit_amount',
        'base_balance',
        'description',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'base_debit_amount' => 'decimal:2',
        'base_credit_amount' => 'decimal:2',
        'base_balance' => 'decimal:2',
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
     * Currency relationship.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Base currency relationship.
     */
    public function baseCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'base_currency_id');
    }

    /**
     * Purchase order relationship.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Payment voucher relationship.
     */
    public function paymentVoucher(): BelongsTo
    {
        return $this->belongsTo(PaymentVoucher::class);
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
     * Scope for debit entries.
     */
    public function scopeDebits($query)
    {
        return $query->where('debit_amount', '>', 0);
    }

    /**
     * Scope for credit entries.
     */
    public function scopeCredits($query)
    {
        return $query->where('credit_amount', '>', 0);
    }

    /**
     * Scope for invoice entries.
     */
    public function scopeInvoices($query)
    {
        return $query->where('transaction_type', 'invoice');
    }

    /**
     * Scope for payment entries.
     */
    public function scopePayments($query)
    {
        return $query->where('transaction_type', 'payment');
    }

    /**
     * Calculate balance based on debit and credit.
     */
    public function calculateBalance(): void
    {
        $this->balance = $this->debit_amount - $this->credit_amount;
        $this->base_balance = $this->base_debit_amount - $this->base_credit_amount;
        $this->save();
    }

    /**
     * Calculate base currency amounts using exchange rate.
     */
    public function calculateBaseAmounts(): void
    {
        $this->base_debit_amount = $this->debit_amount * $this->exchange_rate;
        $this->base_credit_amount = $this->credit_amount * $this->exchange_rate;
        $this->base_balance = $this->balance * $this->exchange_rate;
        $this->save();
    }
}
