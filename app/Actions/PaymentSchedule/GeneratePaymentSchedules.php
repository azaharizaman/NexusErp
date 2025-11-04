<?php

namespace App\Actions\PaymentSchedule;

use App\Models\PaymentSchedule;
use App\Models\PurchaseOrder;
use App\Models\SupplierInvoice;
use Lorisleiva\Actions\Concerns\AsAction;

class GeneratePaymentSchedules
{
    use AsAction;

    /**
     * Auto-generate payment schedules from a purchase order or supplier invoice.
     *
     * @param  PurchaseOrder|SupplierInvoice  $source
     * @param  array  $scheduleData  Array of schedule configurations
     * @return array
     */
    public function handle($source, array $scheduleData): array
    {
        $schedules = [];

        foreach ($scheduleData as $data) {
            $schedule = PaymentSchedule::create([
                'company_id' => $source->company_id,
                'supplier_id' => $source->supplier_id,
                'currency_id' => $source->currency_id,
                'purchase_order_id' => $source instanceof PurchaseOrder ? $source->id : null,
                'supplier_invoice_id' => $source instanceof SupplierInvoice ? $source->id : null,
                'due_date' => $data['due_date'],
                'amount' => $data['amount'],
                'milestone' => $data['milestone'] ?? null,
                'description' => $data['description'] ?? null,
                'outstanding_amount' => $data['amount'],
            ]);

            $schedule->setStatus('pending', 'Payment schedule created');
            $schedules[] = $schedule;
        }

        return $schedules;
    }

    /**
     * Generate payment schedules based on payment terms (e.g., Net 30, 50% Advance).
     *
     * @param  PurchaseOrder|SupplierInvoice  $source
     * @param  string  $paymentTerms
     * @return array
     */
    public function fromPaymentTerms($source, string $paymentTerms): array
    {
        $amount = $source->total_amount;
        $baseDate = $source instanceof PurchaseOrder ? $source->po_date : $source->invoice_date;

        $scheduleData = match (strtolower($paymentTerms)) {
            'net 30' => [
                [
                    'due_date' => $baseDate->copy()->addDays(30),
                    'amount' => $amount,
                    'milestone' => 'Net 30',
                ],
            ],
            'net 60' => [
                [
                    'due_date' => $baseDate->copy()->addDays(60),
                    'amount' => $amount,
                    'milestone' => 'Net 60',
                ],
            ],
            '50% advance' => [
                [
                    'due_date' => $baseDate->copy(),
                    'amount' => $amount * 0.5,
                    'milestone' => '50% Advance',
                ],
                [
                    'due_date' => $baseDate->copy()->addDays(30),
                    'amount' => $amount * 0.5,
                    'milestone' => '50% Upon Completion',
                ],
            ],
            default => [
                [
                    'due_date' => $baseDate->copy(),
                    'amount' => $amount,
                    'milestone' => 'Due Immediately',
                ],
            ],
        };

        return $this->handle($source, $scheduleData);
    }
}
