<?php

namespace Tests\Feature\Actions\AccountsPayable;

use App\Actions\AccountsPayable\PostPaymentVoucher;
use App\Models\Account;
use App\Models\AccountingPeriod;
use App\Models\Company;
use App\Models\Currency;
use App\Models\FiscalYear;
use App\Models\PaymentVoucher;
use App\Models\SupplierInvoice;
use AzahariZaman\Backoffice\Models\BusinessPartner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostPaymentVoucherTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private Currency $currency;
    private FiscalYear $fiscalYear;
    private AccountingPeriod $accountingPeriod;
    private Account $apAccount;
    private Account $cashAccount;
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

        $this->cashAccount = Account::factory()->create([
            'company_id' => $this->company->id,
            'account_code' => '1010',
            'account_name' => 'Cash - Main Account',
            'account_type' => 'Asset',
            'account_subtype' => 'Current Asset',
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
            'paid_amount' => '0.00',
            'outstanding_amount' => '1000.00',
        ]);
    }

    /** @test */
    public function it_posts_payment_voucher_to_gl_successfully()
    {
        $voucher = PaymentVoucher::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'supplier_invoice_id' => $this->invoice->id,
            'currency_id' => $this->currency->id,
            'payment_date' => '2025-11-15',
            'payment_method' => 'bank_transfer',
            'amount' => '500.00',
        ]);

        $voucher->setStatus('paid', 'Test payment');

        $journalEntry = PostPaymentVoucher::run(
            $voucher,
            $this->cashAccount->id,
            $this->apAccount->id
        );

        $this->assertNotNull($journalEntry);
        $this->assertTrue($journalEntry->isBalanced());
        $this->assertEquals('500.00', $journalEntry->total_debit);
        $this->assertEquals('500.00', $journalEntry->total_credit);

        // Check lines
        $this->assertCount(2, $journalEntry->lines);
        
        // Check AP debit
        $apLine = $journalEntry->lines->where('account_id', $this->apAccount->id)->first();
        $this->assertEquals('500.00', $apLine->debit);

        // Check Cash credit
        $cashLine = $journalEntry->lines->where('account_id', $this->cashAccount->id)->first();
        $this->assertEquals('500.00', $cashLine->credit);

        // Check voucher GL status
        $voucher->refresh();
        $this->assertTrue($voucher->is_posted_to_gl);
        $this->assertNotNull($voucher->posted_to_gl_at);

        // Check invoice updated
        $this->invoice->refresh();
        $this->assertEquals('500.00', $this->invoice->paid_amount);
        $this->assertEquals('500.00', $this->invoice->outstanding_amount);
        $this->assertEquals('partially_paid', $this->invoice->latestStatus());
    }

    /** @test */
    public function it_marks_invoice_as_paid_when_fully_paid()
    {
        $voucher = PaymentVoucher::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'supplier_invoice_id' => $this->invoice->id,
            'currency_id' => $this->currency->id,
            'payment_date' => '2025-11-15',
            'amount' => '1000.00',
        ]);

        $voucher->setStatus('paid', 'Test payment');

        PostPaymentVoucher::run($voucher, $this->cashAccount->id, $this->apAccount->id);

        $this->invoice->refresh();
        $this->assertEquals('1000.00', $this->invoice->paid_amount);
        $this->assertEquals('0.00', $this->invoice->outstanding_amount);
        $this->assertEquals('paid', $this->invoice->latestStatus());
    }

    /** @test */
    public function it_throws_exception_when_voucher_not_paid()
    {
        $voucher = PaymentVoucher::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'supplier_invoice_id' => $this->invoice->id,
            'currency_id' => $this->currency->id,
            'payment_date' => '2025-11-15',
        ]);

        $voucher->setStatus('approved', 'Test approved');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('voucher must be paid');

        PostPaymentVoucher::run($voucher, $this->cashAccount->id, $this->apAccount->id);
    }

    /** @test */
    public function it_throws_exception_when_voucher_has_no_invoice_allocation()
    {
        $voucher = PaymentVoucher::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'supplier_invoice_id' => null,
            'currency_id' => $this->currency->id,
            'payment_date' => '2025-11-15',
        ]);

        $voucher->setStatus('paid', 'Test payment');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('no supplier invoice allocated');

        PostPaymentVoucher::run($voucher, $this->cashAccount->id, $this->apAccount->id);
    }
}
