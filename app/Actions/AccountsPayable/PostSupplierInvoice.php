<?php

namespace App\Actions\AccountsPayable;

use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\SupplierInvoice;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class PostSupplierInvoice
{
    use AsAction;

    /**
     * Post supplier invoice to the General Ledger.
     *
     * This creates a journal entry with:
     * - Debit: Expense Account (per line item with expense_account_id)
     * - Credit: Accounts Payable (liability account)
     * - Debit: Tax Payable (if applicable) - tax is an input credit for purchases
     *
     * @param  SupplierInvoice  $invoice  The supplier invoice to post
     * @param  int  $apAccountId  The Accounts Payable account ID
     * @param  int|null  $taxPayableAccountId  The Tax Payable account ID (optional, for input tax credit)
     * @return JournalEntry The created journal entry
     *
     * @throws \InvalidArgumentException If invoice is not approved or has no items
     * @throws \LogicException If invoice is already posted to GL
     * @throws \RuntimeException If fiscal year/period not found or not open
     */
    public function handle(
        SupplierInvoice $invoice,
        int $apAccountId,
        ?int $taxPayableAccountId = null
    ): JournalEntry {
        // Validate invoice status - must be approved
        if ($invoice->latestStatus() !== 'approved') {
            throw new \InvalidArgumentException(
                "Cannot post invoice {$invoice->invoice_number} - invoice must be approved, current status: " . $invoice->latestStatus()
            );
        }

        // Check if already posted
        if ($invoice->is_posted_to_gl) {
            throw new \LogicException(
                "Invoice {$invoice->invoice_number} is already posted to GL (Journal Entry: {$invoice->journal_entry_id})"
            );
        }

        // Validate invoice has items
        if ($invoice->items()->count() === 0) {
            throw new \InvalidArgumentException(
                "Cannot post invoice {$invoice->invoice_number} - no line items found"
            );
        }

        // Determine fiscal year and period from invoice date
        $fiscalYear = \App\Models\FiscalYear::query()
            ->where('company_id', $invoice->company_id)
            ->where('start_date', '<=', $invoice->invoice_date)
            ->where('end_date', '>=', $invoice->invoice_date)
            ->first();

        if (!$fiscalYear) {
            throw new \RuntimeException(
                "No fiscal year found for invoice date {$invoice->invoice_date->format('Y-m-d')}"
            );
        }

        $accountingPeriod = \App\Models\AccountingPeriod::query()
            ->where('fiscal_year_id', $fiscalYear->id)
            ->where('start_date', '<=', $invoice->invoice_date)
            ->where('end_date', '>=', $invoice->invoice_date)
            ->first();

        if (!$accountingPeriod) {
            throw new \RuntimeException(
                "No accounting period found for invoice date {$invoice->invoice_date->format('Y-m-d')}"
            );
        }

        // Validate accounting period is open
        if ($accountingPeriod->status !== 'open') {
            throw new \RuntimeException(
                "Cannot post invoice - accounting period {$accountingPeriod->period_name} is not open"
            );
        }

        return DB::transaction(function () use ($invoice, $apAccountId, $taxPayableAccountId, $fiscalYear, $accountingPeriod) {
            // Create journal entry
            $journalEntry = JournalEntry::create([
                'company_id' => $invoice->company_id,
                'fiscal_year_id' => $fiscalYear->id,
                'accounting_period_id' => $accountingPeriod->id,
                'entry_type' => 'automatic',
                'entry_date' => $invoice->invoice_date,
                'currency_id' => $invoice->currency_id,
                'exchange_rate' => $invoice->exchange_rate ?? 1,
                'description' => "Supplier Invoice {$invoice->invoice_number} - Supplier: {$invoice->supplier->name}",
                'reference_type' => 'supplier_invoice',
                'reference_id' => $invoice->id,
                'reference_number' => $invoice->invoice_number,
                'created_by' => auth()->id(),
            ]);

            // Debit: Expense accounts for each line item
            foreach ($invoice->items as $item) {
                if ($item->expense_account_id) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $item->expense_account_id,
                        'debit' => $item->line_total,
                        'credit' => '0.0000',
                        'description' => "Expense - {$item->item_description}",
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            // Debit: Tax Payable for input tax credit (if applicable)
            if ($invoice->tax_amount > 0 && $taxPayableAccountId) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $taxPayableAccountId,
                    'debit' => $invoice->tax_amount,
                    'credit' => '0.0000',
                    'description' => "Input Tax Credit - Invoice {$invoice->invoice_number}",
                    'created_by' => auth()->id(),
                ]);
            }

            // Credit: Accounts Payable for total amount
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $apAccountId,
                'debit' => '0.0000',
                'credit' => $invoice->total_amount,
                'description' => "AP - Invoice {$invoice->invoice_number}",
                'created_by' => auth()->id(),
            ]);

            // Update journal entry totals
            $journalEntry->updateTotals();

            // Validate the journal entry is balanced
            if (!$journalEntry->isBalanced()) {
                throw new \RuntimeException(
                    "Journal entry is not balanced: Debit={$journalEntry->total_debit}, Credit={$journalEntry->total_credit}"
                );
            }

            // Post the journal entry to GL
            $journalEntry->post();

            // Update invoice GL posting status
            $invoice->update([
                'journal_entry_id' => $journalEntry->id,
                'is_posted_to_gl' => true,
                'posted_to_gl_at' => now(),
            ]);

            return $journalEntry;
        });
    }

    /**
     * Get the console command description.
     */
    public function getCommandDescription(): string
    {
        return 'Post a supplier invoice to the General Ledger';
    }
}
