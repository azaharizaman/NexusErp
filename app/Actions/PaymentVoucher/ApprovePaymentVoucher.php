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
     */
    public function handle(PaymentVoucher $voucher): PaymentVoucher
    {
        if (! $voucher->canApprove()) {
            throw new \Exception('Payment voucher cannot be approved in its current state.');
        }

        $voucher->approved_by = Auth::id();
        $voucher->approved_at = now();
        $voucher->save();

        $voucher->setStatus('approved', 'Payment voucher approved');

        return $voucher->fresh();
    }
}
