<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentReceiptAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_receipt_id',
        'sales_invoice_id',
        'allocated_amount',
    ];

    protected $casts = [
        'allocated_amount' => 'decimal:4',
    ];

    // Relationships

    public function paymentReceipt(): BelongsTo
    {
        return $this->belongsTo(PaymentReceipt::class);
    }

    public function salesInvoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    // Scopes

    public function scopeForPayment($query, int $paymentReceiptId)
    {
        return $query->where('payment_receipt_id', $paymentReceiptId);
    }

    public function scopeForInvoice($query, int $salesInvoiceId)
    {
        return $query->where('sales_invoice_id', $salesInvoiceId);
    }
}
