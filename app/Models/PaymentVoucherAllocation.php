<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentVoucherAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_voucher_id',
        'supplier_invoice_id',
        'allocated_amount',
    ];

    protected $casts = [
        'allocated_amount' => 'decimal:4',
    ];

    /**
     * Payment voucher relationship.
     */
    public function paymentVoucher(): BelongsTo
    {
        return $this->belongsTo(PaymentVoucher::class);
    }

    /**
     * Supplier invoice relationship.
     */
    public function supplierInvoice(): BelongsTo
    {
        return $this->belongsTo(SupplierInvoice::class);
    }

    /**
     * Scope to filter allocations for a specific payment voucher.
     */
    public function scopeForPayment($query, int $paymentVoucherId)
    {
        return $query->where('payment_voucher_id', $paymentVoucherId);
    }

    /**
     * Scope to filter allocations for a specific supplier invoice.
     */
    public function scopeForInvoice($query, int $supplierInvoiceId)
    {
        return $query->where('supplier_invoice_id', $supplierInvoiceId);
    }

    /**
     * Allocate payment to invoice with validation.
     *
     * @throws \InvalidArgumentException
     */
    public static function allocateToInvoice(
        PaymentVoucher $paymentVoucher,
        SupplierInvoice $supplierInvoice,
        float $amount
    ): self {
        // Validate amount is positive
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Allocation amount must be greater than zero');
        }

        // Validate currencies match
        if ($paymentVoucher->currency_id !== $supplierInvoice->currency_id) {
            throw new \InvalidArgumentException(
                'Payment voucher and supplier invoice must have the same currency. '.
                'Payment voucher currency: '.$paymentVoucher->currency_id.
                ', Invoice currency: '.$supplierInvoice->currency_id
            );
        }

        // Get existing allocations for this payment
        $existingAllocations = static::forPayment($paymentVoucher->id)->sum('allocated_amount');
        
        // Check if total allocations would exceed payment amount
        $totalAllocated = bcadd((string) $existingAllocations, (string) $amount, 4);
        if (bccomp($totalAllocated, (string) $paymentVoucher->amount, 4) > 0) {
            throw new \InvalidArgumentException(
                'Total allocated amount ('.number_format($totalAllocated, 2).
                ') cannot exceed payment voucher amount ('.number_format($paymentVoucher->amount, 2).')'
            );
        }

        // Check if allocation would exceed invoice outstanding amount
        if (bccomp((string) $amount, (string) $supplierInvoice->outstanding_amount, 4) > 0) {
            throw new \InvalidArgumentException(
                'Allocation amount ('.number_format($amount, 2).
                ') cannot exceed invoice outstanding amount ('.
                number_format($supplierInvoice->outstanding_amount, 2).')'
            );
        }

        // Create or update allocation
        $allocation = static::updateOrCreate(
            [
                'payment_voucher_id' => $paymentVoucher->id,
                'supplier_invoice_id' => $supplierInvoice->id,
            ],
            [
                'allocated_amount' => $amount,
            ]
        );

        // Update invoice paid and outstanding amounts
        static::recalculateAllocations($supplierInvoice);

        return $allocation;
    }

    /**
     * Recalculate allocations for a supplier invoice.
     * Updates the paid_amount and outstanding_amount based on all allocations.
     */
    public static function recalculateAllocations(SupplierInvoice $supplierInvoice): void
    {
        // Sum all allocations for this invoice from paid payment vouchers
        $totalAllocated = static::forInvoice($supplierInvoice->id)
            ->whereHas('paymentVoucher', function ($query) {
                $query->currentStatus('paid');
            })
            ->sum('allocated_amount');

        // Update invoice amounts
        $supplierInvoice->paid_amount = $totalAllocated;
        $supplierInvoice->outstanding_amount = bcsub(
            (string) $supplierInvoice->total_amount,
            (string) $totalAllocated,
            4
        );
        $supplierInvoice->save();

        // Update invoice status based on payment
        if (bccomp((string) $supplierInvoice->outstanding_amount, '0', 4) <= 0) {
            if ($supplierInvoice->latestStatus() !== 'paid') {
                $supplierInvoice->setStatus('paid', 'Invoice fully paid via allocation');
            }
        } elseif (bccomp((string) $supplierInvoice->paid_amount, '0', 4) > 0) {
            if ($supplierInvoice->latestStatus() !== 'partially_paid') {
                $supplierInvoice->setStatus('partially_paid', 'Invoice partially paid via allocation');
            }
        }
    }
}
