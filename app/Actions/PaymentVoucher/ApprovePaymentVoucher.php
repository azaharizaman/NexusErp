<?php

namespace App\Actions\PaymentVoucher;

use App\Models\PaymentVoucher;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class ApprovePaymentVoucher
{
    use AsAction;

    /**
     * Approve a payment voucher.
     *
     * @param  PaymentVoucher  $voucher
     * @return PaymentVoucher
     *
     * @throws \LogicException If voucher cannot be approved
     */
    public function handle(PaymentVoucher $voucher): PaymentVoucher
    {
        // Validate that voucher has allocations first (before checking status)
        if ($voucher->allocations()->count() === 0) {
            throw new \LogicException(
                'Payment voucher must have at least one allocation before approval.'
            );
        }

        // Validate that voucher is not on hold
        if ($voucher->is_on_hold) {
            throw new \LogicException(
                "Payment voucher is on hold and cannot be approved: {$voucher->hold_reason}"
            );
        }

        // Now check if voucher can be approved based on status
        if (! $voucher->canApprove()) {
            throw new \LogicException('Payment voucher cannot be approved in its current state.');
        }

        $voucher->approved_by = Auth::id();
        $voucher->approved_at = now();
        $voucher->save();

        $voucher->setStatus('approved', 'Payment voucher approved');

        return $voucher->fresh();
    }
}
