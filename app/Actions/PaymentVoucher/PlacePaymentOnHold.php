<?php

namespace App\Actions\PaymentVoucher;

use App\Models\PaymentVoucher;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Lorisleiva\Actions\Concerns\AsAction;

class PlacePaymentOnHold
{
    use AsAction;

    /**
     * Place a payment voucher on hold.
     *
     * @param  PaymentVoucher  $voucher
     * @param  string  $reason  Reason for placing payment on hold
     * @param  bool  $notifyFinance  Whether to send notification to finance team
     * @return PaymentVoucher
     *
     * @throws \InvalidArgumentException If reason is empty
     * @throws \LogicException If voucher is already paid or voided
     */
    public function handle(
        PaymentVoucher $voucher,
        string $reason,
        bool $notifyFinance = true
    ): PaymentVoucher {
        // Validate reason is provided
        if (empty(trim($reason))) {
            throw new \InvalidArgumentException('Hold reason is required.');
        }

        // Validate voucher status - cannot hold paid or voided vouchers
        $currentStatus = $voucher->latestStatus();
        if (in_array($currentStatus, ['paid', 'voided'])) {
            throw new \LogicException(
                "Cannot place payment voucher on hold - current status is {$currentStatus}"
            );
        }

        // Update hold fields
        $voucher->is_on_hold = true;
        $voucher->hold_reason = $reason;
        $voucher->held_by = Auth::id();
        $voucher->held_at = now();
        $voucher->save();

        // Add status note about hold
        $voucher->setStatus(
            $currentStatus,
            "Payment placed on hold: {$reason}"
        );

        // Send notification to finance team if requested
        if ($notifyFinance) {
            $this->notifyFinanceTeam($voucher);
        }

        return $voucher->fresh();
    }

    /**
     * Remove hold from a payment voucher.
     *
     * @param  PaymentVoucher  $voucher
     * @param  string|null  $note  Optional note about removing hold
     * @return PaymentVoucher
     *
     * @throws \LogicException If voucher is not on hold
     */
    public function handleRemoveHold(
        PaymentVoucher $voucher,
        ?string $note = null
    ): PaymentVoucher {
        // Validate voucher is on hold
        if (! $voucher->is_on_hold) {
            throw new \LogicException('Payment voucher is not on hold.');
        }

        $currentStatus = $voucher->latestStatus();

        // Update hold fields
        $voucher->is_on_hold = false;
        $voucher->hold_reason = null;
        $voucher->held_by = null;
        $voucher->held_at = null;
        $voucher->save();

        // Add status note about removing hold
        $message = 'Payment hold removed';
        if ($note) {
            $message .= ": {$note}";
        }
        
        $voucher->setStatus($currentStatus, $message);

        return $voucher->fresh();
    }

    /**
     * Notify finance team about payment being placed on hold.
     *
     * This is a placeholder implementation. In production, you would:
     * - Create a custom notification class
     * - Send to users with 'finance' role or specific permission
     * - Include payment details and hold reason
     */
    protected function notifyFinanceTeam(PaymentVoucher $voucher): void
    {
        // TODO: Implement actual notification logic
        // For now, this is a placeholder that logs the notification
        
        // Example implementation (commented out):
        /*
        $financeUsers = User::role('finance')->get();
        
        Notification::send($financeUsers, new PaymentOnHoldNotification($voucher));
        */
        
        // Log for now
        logger()->info('Payment voucher placed on hold', [
            'voucher_id' => $voucher->id,
            'voucher_number' => $voucher->voucher_number,
            'supplier_id' => $voucher->supplier_id,
            'amount' => $voucher->amount,
            'hold_reason' => $voucher->hold_reason,
            'held_by' => $voucher->held_by,
            'held_at' => $voucher->held_at,
        ]);
    }

    /**
     * Get the console command description.
     */
    public function getCommandDescription(): string
    {
        return 'Place a payment voucher on hold or remove hold';
    }
}
