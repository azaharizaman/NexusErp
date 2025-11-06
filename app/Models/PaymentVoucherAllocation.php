<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentVoucherAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_voucher_id',
        'supplier_invoice_id',
        'allocated_amount',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'allocated_amount' => 'decimal:4',
    ];

    // Relationships

    public function paymentVoucher(): BelongsTo
    {
        return $this->belongsTo(PaymentVoucher::class);
    }

    public function supplierInvoice(): BelongsTo
    {
        return $this->belongsTo(SupplierInvoice::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes

    public function scopeForPayment($query, int $paymentVoucherId)
    {
        return $query->where('payment_voucher_id', $paymentVoucherId);
    }

    public function scopeForInvoice($query, int $supplierInvoiceId)
    {
        return $query->where('supplier_invoice_id', $supplierInvoiceId);
    }
}
