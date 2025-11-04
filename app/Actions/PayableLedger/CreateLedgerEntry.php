<?php

namespace App\Actions\PayableLedger;

use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\PayableLedger;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateLedgerEntry
{
    use AsAction;

    /**
     * Create a new ledger entry for payables tracking.
     *
     * @param  array  $data
     * @return PayableLedger
     */
    public function handle(array $data): PayableLedger
    {
        // Get base currency
        $baseCurrency = Currency::where('is_base', true)->first();

        // Get exchange rate if foreign currency is different
        $exchangeRate = 1;
        $exchangeRateDate = $data['transaction_date'] ?? now();

        if (isset($data['foreign_currency_id']) && $data['foreign_currency_id'] !== $baseCurrency->id) {
            $rate = ExchangeRate::where('from_currency_id', $data['foreign_currency_id'])
                ->where('to_currency_id', $baseCurrency->id)
                ->where('effective_date', '<=', $exchangeRateDate)
                ->orderBy('effective_date', 'desc')
                ->first();

            $exchangeRate = $rate?->rate ?? 1;
        }

        // Calculate amounts
        $debitBase = $data['debit_amount_base'] ?? 0;
        $creditBase = $data['credit_amount_base'] ?? 0;
        $debitForeign = $data['debit_amount_foreign'] ?? 0;
        $creditForeign = $data['credit_amount_foreign'] ?? 0;

        // If foreign amounts provided but not base amounts, convert
        if ($debitForeign > 0 && $debitBase === 0) {
            $debitBase = $debitForeign * $exchangeRate;
        }
        if ($creditForeign > 0 && $creditBase === 0) {
            $creditBase = $creditForeign * $exchangeRate;
        }

        // Calculate running balance (excluding current transaction date to avoid including itself)
        $query = PayableLedger::where('supplier_id', $data['supplier_id'])
            ->where('transaction_date', '<', $data['transaction_date']);
        
        $totals = $query->selectRaw('SUM(debit_amount_base) as total_debits, SUM(credit_amount_base) as total_credits')->first();
        $previousBalance = ($totals->total_debits ?? 0) - ($totals->total_credits ?? 0);
        $balance = $previousBalance + $debitBase - $creditBase;

        return PayableLedger::create([
            'company_id' => $data['company_id'],
            'supplier_id' => $data['supplier_id'],
            'supplier_invoice_id' => $data['supplier_invoice_id'] ?? null,
            'payment_voucher_id' => $data['payment_voucher_id'] ?? null,
            'base_currency_id' => $baseCurrency->id,
            'foreign_currency_id' => $data['foreign_currency_id'] ?? null,
            'transaction_date' => $data['transaction_date'],
            'transaction_type' => $data['transaction_type'],
            'debit_amount_base' => $debitBase,
            'credit_amount_base' => $creditBase,
            'debit_amount_foreign' => $debitForeign,
            'credit_amount_foreign' => $creditForeign,
            'exchange_rate' => $exchangeRate,
            'exchange_rate_date' => $exchangeRateDate,
            'balance_base' => $balance,
            'balance_foreign' => isset($data['foreign_currency_id']) 
                ? ($exchangeRate > 0 ? $balance / $exchangeRate : 0)
                : $debitForeign - $creditForeign,
            'reference_number' => $data['reference_number'] ?? null,
            'description' => $data['description'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);
    }
}
