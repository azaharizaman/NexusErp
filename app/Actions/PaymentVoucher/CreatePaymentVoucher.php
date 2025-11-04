<?php

namespace App\Actions\PaymentVoucher;

use App\Models\PaymentVoucher;
use Lorisleiva\Actions\Concerns\AsAction;

class CreatePaymentVoucher
{
    use AsAction;

    /**
     * Create a new payment voucher.
     *
     * @param  array  $data
     * @return PaymentVoucher
     */
    public function handle(array $data): PaymentVoucher
    {
        $voucher = PaymentVoucher::create($data);

        // Set initial status to draft
        $voucher->setStatus('draft', 'Payment voucher created');

        return $voucher->fresh();
    }
}
