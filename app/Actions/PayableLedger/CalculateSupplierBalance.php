<?php

namespace App\Actions\PayableLedger;

use App\Models\PayableLedger;
use Lorisleiva\Actions\Concerns\AsAction;

class CalculateSupplierBalance
{
    use AsAction;

    /**
     * Calculate the outstanding balance for a supplier.
     *
     * @param  int  $supplierId
     * @param  string|null  $asOfDate
     * @return array
     */
    public function handle(int $supplierId, ?string $asOfDate = null): array
    {
        $balance = PayableLedger::calculateBalance($supplierId, $asOfDate);

        $query = PayableLedger::where('supplier_id', $supplierId);

        if ($asOfDate) {
            $query->where('transaction_date', '<=', $asOfDate);
        }

        $totalDebits = $query->sum('debit_amount_base');
        $totalCredits = $query->sum('credit_amount_base');

        return [
            'supplier_id' => $supplierId,
            'as_of_date' => $asOfDate ?? now()->toDateString(),
            'total_debits' => $totalDebits,
            'total_credits' => $totalCredits,
            'outstanding_balance' => $balance,
        ];
    }
}
