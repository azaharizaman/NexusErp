<?php

namespace Tests\Feature\Actions\AccountsPayable;

use App\Actions\AccountsPayable\PostSupplierDebitNote;
use App\Models\Account;
use App\Models\AccountingPeriod;
use App\Models\Company;
use App\Models\Currency;
use App\Models\DebitNote;
use App\Models\FiscalYear;
use App\Models\SupplierInvoice;
use AzahariZaman\Backoffice\Models\BusinessPartner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostSupplierDebitNoteTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private Currency $currency;
    private FiscalYear $fiscalYear;
    private AccountingPeriod $accountingPeriod;
    private Account $apAccount;
    private Account $purchaseReturnsAccount;
    private BusinessPartner $supplier;
    private SupplierInvoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

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

        $this->apAccount = Account::factory()->create([
            'company_id' => $this->company->id,
            'account_code' => '2100',
            'account_name' => 'Accounts Payable',
            'account_type' => 'Liability',
            'account_subtype' => 'Current Liability',
        ]);

        $this->purchaseReturnsAccount = Account::factory()->create([
            'company_id' => $this->company->id,
            'account_code' => '5900',
            'account_name' => 'Purchase Returns',
            'account_type' => 'Expense',
            'account_subtype' => 'Cost of Goods Sold',
        ]);

        $this->supplier = BusinessPartner::factory()->create([
            'name' => 'Test Supplier',
            'is_supplier' => true,
        ]);

        $this->invoice = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'total_amount' => '1000.00',
            'outstanding_amount' => '1000.00',
        ]);
    }

    /** @test */
    public function it_posts_debit_note_to_gl_successfully()
    {
        $debitNote = DebitNote::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'supplier_invoice_id' => $this->invoice->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => '2025-11-15',
            'reason' => 'return',
            'amount' => '200.00',
        ]);

        $debitNote->setStatus('approved', 'Test approval');

        $journalEntry = PostSupplierDebitNote::run(
            $debitNote,
            $this->apAccount->id,
            $this->purchaseReturnsAccount->id
        );

        // Assert journal entry created
        $this->assertNotNull($journalEntry);
        $this->assertEquals('debit_note', $journalEntry->reference_type);
        $this->assertTrue($journalEntry->isBalanced());
        $this->assertEquals('200.00', $journalEntry->total_debit);
        $this->assertEquals('200.00', $journalEntry->total_credit);

        // Check lines
        $this->assertCount(2, $journalEntry->lines);

        // AP debit
        $apLine = $journalEntry->lines->where('account_id', $this->apAccount->id)->first();
        $this->assertEquals('200.00', $apLine->debit);
        $this->assertEquals('0.0000', $apLine->credit);

        // Purchase Returns credit
        $returnsLine = $journalEntry->lines->where('account_id', $this->purchaseReturnsAccount->id)->first();
        $this->assertEquals('0.0000', $returnsLine->debit);
        $this->assertEquals('200.00', $returnsLine->credit);

        // Check debit note GL status
        $debitNote->refresh();
        $this->assertTrue($debitNote->is_posted_to_gl);
        $this->assertNotNull($debitNote->posted_to_gl_at);

        // Check invoice outstanding reduced
        $this->invoice->refresh();
        $this->assertEquals('800.00', $this->invoice->outstanding_amount);
    }

    /** @test */
    public function it_marks_invoice_as_paid_when_fully_credited()
    {
        $debitNote = DebitNote::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'supplier_invoice_id' => $this->invoice->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => '2025-11-15',
            'reason' => 'return',
            'amount' => '1000.00',
        ]);

        $debitNote->setStatus('approved', 'Test approval');

        PostSupplierDebitNote::run(
            $debitNote,
            $this->apAccount->id,
            $this->purchaseReturnsAccount->id
        );

        $this->invoice->refresh();
        $this->assertEquals('0.00', $this->invoice->outstanding_amount);
        $this->assertEquals('paid', $this->invoice->latestStatus());
    }

    /** @test */
    public function it_throws_exception_when_debit_note_not_approved()
    {
        $debitNote = DebitNote::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => '2025-11-15',
        ]);

        $debitNote->setStatus('draft', 'Test draft');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('debit note must be approved');

        PostSupplierDebitNote::run(
            $debitNote,
            $this->apAccount->id,
            $this->purchaseReturnsAccount->id
        );
    }

    /** @test */
    public function it_throws_exception_when_debit_note_already_posted()
    {
        $debitNote = DebitNote::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => '2025-11-15',
            'is_posted_to_gl' => true,
            'journal_entry_id' => 999,
        ]);

        $debitNote->setStatus('approved', 'Test approval');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('already posted to GL');

        PostSupplierDebitNote::run(
            $debitNote,
            $this->apAccount->id,
            $this->purchaseReturnsAccount->id
        );
    }

    /** @test */
    public function it_posts_debit_note_without_linked_invoice()
    {
        $debitNote = DebitNote::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'supplier_invoice_id' => null,
            'currency_id' => $this->currency->id,
            'debit_note_date' => '2025-11-15',
            'reason' => 'adjustment',
            'amount' => '150.00',
        ]);

        $debitNote->setStatus('approved', 'Test approval');

        $journalEntry = PostSupplierDebitNote::run(
            $debitNote,
            $this->apAccount->id,
            $this->purchaseReturnsAccount->id
        );

        // Should post successfully even without invoice
        $this->assertNotNull($journalEntry);
        $this->assertTrue($journalEntry->isBalanced());
        $this->assertTrue($debitNote->fresh()->is_posted_to_gl);
    }
}
