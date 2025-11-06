<?php

namespace Tests\Feature\Actions\AccountsPayable;

use App\Actions\AccountsPayable\PostSupplierInvoice;
use App\Models\Account;
use App\Models\AccountingPeriod;
use App\Models\Company;
use App\Models\Currency;
use App\Models\FiscalYear;
use App\Models\SupplierInvoice;
use App\Models\SupplierInvoiceItem;
use AzahariZaman\Backoffice\Models\BusinessPartner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostSupplierInvoiceTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private Currency $currency;
    private FiscalYear $fiscalYear;
    private AccountingPeriod $accountingPeriod;
    private Account $apAccount;
    private Account $expenseAccount;
    private Account $taxAccount;
    private BusinessPartner $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->company = Company::factory()->create();
        $this->currency = Currency::factory()->create(['code' => 'USD', 'is_base' => true]);
        
        $this->fiscalYear = FiscalYear::create([
            'company_id' => $this->company->id,
            'code' => 'FY2025',
            'name' => 'Fiscal Year 2025',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'is_locked' => false,
        ]);
        
        $this->fiscalYear->setStatus('active', 'Test fiscal year');

        $this->accountingPeriod = AccountingPeriod::create([
            'fiscal_year_id' => $this->fiscalYear->id,
            'period_type' => 'monthly',
            'period_number' => 11,
            'period_name' => 'November 2025',
            'start_date' => '2025-11-01',
            'end_date' => '2025-11-30',
            'status' => 'open',
        ]);

        // Create GL accounts
        $this->apAccount = Account::factory()->create([
            'company_id' => $this->company->id,
            'account_code' => '2100',
            'account_name' => 'Accounts Payable',
            'account_type' => 'Liability',
            'account_subtype' => 'Current Liability',
        ]);

        $this->expenseAccount = Account::factory()->create([
            'company_id' => $this->company->id,
            'account_code' => '5100',
            'account_name' => 'Office Supplies',
            'account_type' => 'Expense',
            'account_subtype' => 'Operating Expense',
        ]);

        $this->taxAccount = Account::factory()->create([
            'company_id' => $this->company->id,
            'account_code' => '1150',
            'account_name' => 'Input Tax Credit',
            'account_type' => 'Asset',
            'account_subtype' => 'Current Asset',
        ]);

        $this->supplier = BusinessPartner::factory()->create([
            'name' => 'Test Supplier',
            'is_supplier' => true,
        ]);
    }

    /** @test */
    public function it_posts_supplier_invoice_to_gl_successfully()
    {
        // Create an approved supplier invoice
        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'invoice_date' => '2025-11-15',
            'due_date' => '2025-12-15',
            'subtotal' => '1000.00',
            'tax_amount' => '100.00',
            'total_amount' => '1100.00',
        ]);

        $invoice->setStatus('approved', 'Test approval');

        // Create invoice items
        SupplierInvoiceItem::factory()->create([
            'supplier_invoice_id' => $invoice->id,
            'item_description' => 'Office Supplies',
            'quantity' => 10,
            'unit_price' => '100.00',
            'line_total' => '1000.00',
            'expense_account_id' => $this->expenseAccount->id,
        ]);

        // Post to GL
        $journalEntry = PostSupplierInvoice::run(
            $invoice,
            $this->apAccount->id,
            $this->taxAccount->id
        );

        // Assert journal entry was created
        $this->assertNotNull($journalEntry);
        $this->assertEquals($this->company->id, $journalEntry->company_id);
        $this->assertEquals('automatic', $journalEntry->entry_type);
        $this->assertEquals('supplier_invoice', $journalEntry->reference_type);
        $this->assertEquals($invoice->id, $journalEntry->reference_id);

        // Assert journal entry is balanced
        $this->assertTrue($journalEntry->isBalanced());
        $this->assertEquals('1100.00', $journalEntry->total_debit);
        $this->assertEquals('1100.00', $journalEntry->total_credit);

        // Assert journal entry lines
        $lines = $journalEntry->lines;
        $this->assertCount(3, $lines);

        // Expense debit
        $expenseLine = $lines->where('account_id', $this->expenseAccount->id)->first();
        $this->assertEquals('1000.00', $expenseLine->debit);
        $this->assertEquals('0.0000', $expenseLine->credit);

        // Tax debit
        $taxLine = $lines->where('account_id', $this->taxAccount->id)->first();
        $this->assertEquals('100.00', $taxLine->debit);
        $this->assertEquals('0.0000', $taxLine->credit);

        // AP credit
        $apLine = $lines->where('account_id', $this->apAccount->id)->first();
        $this->assertEquals('0.0000', $apLine->debit);
        $this->assertEquals('1100.00', $apLine->credit);

        // Assert invoice GL status updated
        $invoice->refresh();
        $this->assertTrue($invoice->is_posted_to_gl);
        $this->assertEquals($journalEntry->id, $invoice->journal_entry_id);
        $this->assertNotNull($invoice->posted_to_gl_at);
    }

    /** @test */
    public function it_throws_exception_when_invoice_not_approved()
    {
        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'invoice_date' => '2025-11-15',
        ]);

        $invoice->setStatus('draft', 'Test draft');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('invoice must be approved');

        PostSupplierInvoice::run($invoice, $this->apAccount->id);
    }

    /** @test */
    public function it_throws_exception_when_invoice_already_posted()
    {
        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'invoice_date' => '2025-11-15',
            'is_posted_to_gl' => true,
            'journal_entry_id' => 999,
        ]);

        $invoice->setStatus('approved', 'Test approval');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('already posted to GL');

        PostSupplierInvoice::run($invoice, $this->apAccount->id);
    }

    /** @test */
    public function it_throws_exception_when_invoice_has_no_items()
    {
        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'invoice_date' => '2025-11-15',
        ]);

        $invoice->setStatus('approved', 'Test approval');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('no line items found');

        PostSupplierInvoice::run($invoice, $this->apAccount->id);
    }

    /** @test */
    public function it_throws_exception_when_accounting_period_not_open()
    {
        $this->accountingPeriod->update(['status' => 'closed']);

        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'invoice_date' => '2025-11-15',
        ]);

        $invoice->setStatus('approved', 'Test approval');

        SupplierInvoiceItem::factory()->create([
            'supplier_invoice_id' => $invoice->id,
            'expense_account_id' => $this->expenseAccount->id,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('is not open');

        PostSupplierInvoice::run($invoice, $this->apAccount->id);
    }

    /** @test */
    public function it_posts_invoice_without_tax()
    {
        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'invoice_date' => '2025-11-15',
            'subtotal' => '500.00',
            'tax_amount' => '0.00',
            'total_amount' => '500.00',
        ]);

        $invoice->setStatus('approved', 'Test approval');

        SupplierInvoiceItem::factory()->create([
            'supplier_invoice_id' => $invoice->id,
            'line_total' => '500.00',
            'expense_account_id' => $this->expenseAccount->id,
        ]);

        // Post without tax account
        $journalEntry = PostSupplierInvoice::run($invoice, $this->apAccount->id, null);

        // Should have only 2 lines (expense debit and AP credit)
        $this->assertCount(2, $journalEntry->lines);
        $this->assertTrue($journalEntry->isBalanced());
    }
}
