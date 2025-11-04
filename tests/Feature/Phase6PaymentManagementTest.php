<?php

namespace Tests\Feature;

use App\Actions\PayableLedger\CalculateSupplierBalance;
use App\Actions\PayableLedger\CreateLedgerEntry;
use App\Actions\PaymentSchedule\GeneratePaymentSchedules;
use App\Actions\PaymentVoucher\ApprovePaymentVoucher;
use App\Actions\PaymentVoucher\CreatePaymentVoucher;
use App\Actions\PaymentVoucher\RecordPayment;
use App\Models\BusinessPartner;
use App\Models\Company;
use App\Models\Currency;
use App\Models\PayableLedger;
use App\Models\PaymentSchedule;
use App\Models\PaymentVoucher;
use App\Models\PurchaseOrder;
use App\Models\SupplierInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase6PaymentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create necessary test data
        $this->actingAs(User::factory()->create());
    }

    /** @test */
    public function it_can_create_a_payment_voucher()
    {
        $company = Company::factory()->create();
        $currency = Currency::factory()->create(['is_base' => true]);
        $supplier = BusinessPartner::factory()->create(['is_supplier' => true]);

        $voucher = PaymentVoucher::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'payment_date' => now(),
            'amount' => 1000.00,
            'payment_method' => 'bank_transfer',
        ]);

        $this->assertInstanceOf(PaymentVoucher::class, $voucher);
        $this->assertEquals(1000.00, $voucher->amount);
        $this->assertEquals('bank_transfer', $voucher->payment_method);
        $this->assertNotNull($voucher->voucher_number);
    }

    /** @test */
    public function it_can_approve_a_payment_voucher()
    {
        $company = Company::factory()->create();
        $currency = Currency::factory()->create(['is_base' => true]);
        $supplier = BusinessPartner::factory()->create(['is_supplier' => true]);

        $voucher = CreatePaymentVoucher::run([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'payment_date' => now(),
            'amount' => 1000.00,
        ]);

        // Change status to submitted so it can be approved
        $voucher->setStatus('submitted', 'Submitted for approval');

        $approvedVoucher = ApprovePaymentVoucher::run($voucher);

        $this->assertNotNull($approvedVoucher->approved_by);
        $this->assertNotNull($approvedVoucher->approved_at);
        $this->assertEquals('approved', $approvedVoucher->latestStatus());
    }

    /** @test */
    public function it_can_create_a_payment_schedule()
    {
        $company = Company::factory()->create();
        $currency = Currency::factory()->create(['is_base' => true]);
        $supplier = BusinessPartner::factory()->create(['is_supplier' => true]);

        $schedule = PaymentSchedule::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'due_date' => now()->addDays(30),
            'amount' => 5000.00,
            'outstanding_amount' => 5000.00,
            'milestone' => 'Net 30',
        ]);

        $this->assertInstanceOf(PaymentSchedule::class, $schedule);
        $this->assertEquals(5000.00, $schedule->amount);
        $this->assertEquals('Net 30', $schedule->milestone);
        $this->assertNotNull($schedule->schedule_number);
    }

    /** @test */
    public function it_can_check_if_schedule_is_overdue()
    {
        $company = Company::factory()->create();
        $currency = Currency::factory()->create(['is_base' => true]);
        $supplier = BusinessPartner::factory()->create(['is_supplier' => true]);

        $overdueSchedule = PaymentSchedule::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'due_date' => now()->subDays(5),
            'amount' => 5000.00,
            'outstanding_amount' => 5000.00,
        ]);

        $overdueSchedule->setStatus('pending', 'Created');

        $this->assertTrue($overdueSchedule->isOverdue());
    }

    /** @test */
    public function it_can_create_a_payable_ledger_entry()
    {
        $company = Company::factory()->create();
        $baseCurrency = Currency::factory()->create(['is_base' => true]);
        $supplier = BusinessPartner::factory()->create(['is_supplier' => true]);

        $ledgerData = [
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'base_currency_id' => $baseCurrency->id,
            'transaction_date' => now(),
            'transaction_type' => 'invoice',
            'debit_amount_base' => 10000.00,
            'description' => 'Test invoice entry',
        ];

        $ledger = CreateLedgerEntry::run($ledgerData);

        $this->assertInstanceOf(PayableLedger::class, $ledger);
        $this->assertEquals(10000.00, $ledger->debit_amount_base);
        $this->assertEquals('invoice', $ledger->transaction_type);
        $this->assertEquals(10000.00, $ledger->balance_base);
    }

    /** @test */
    public function it_can_calculate_supplier_balance()
    {
        $company = Company::factory()->create();
        $baseCurrency = Currency::factory()->create(['is_base' => true]);
        $supplier = BusinessPartner::factory()->create(['is_supplier' => true]);

        // Create debit entry (invoice)
        CreateLedgerEntry::run([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'base_currency_id' => $baseCurrency->id,
            'transaction_date' => now(),
            'transaction_type' => 'invoice',
            'debit_amount_base' => 10000.00,
        ]);

        // Create credit entry (payment)
        CreateLedgerEntry::run([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'base_currency_id' => $baseCurrency->id,
            'transaction_date' => now(),
            'transaction_type' => 'payment',
            'credit_amount_base' => 3000.00,
        ]);

        $balance = CalculateSupplierBalance::run($supplier->id);

        $this->assertEquals($supplier->id, $balance['supplier_id']);
        $this->assertEquals(10000.00, $balance['total_debits']);
        $this->assertEquals(3000.00, $balance['total_credits']);
        $this->assertEquals(7000.00, $balance['outstanding_balance']);
    }

    /** @test */
    public function it_has_proper_relationships_on_payment_voucher()
    {
        $company = Company::factory()->create();
        $currency = Currency::factory()->create(['is_base' => true]);
        $supplier = BusinessPartner::factory()->create(['is_supplier' => true]);

        $voucher = CreatePaymentVoucher::run([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'payment_date' => now(),
            'amount' => 1000.00,
        ]);

        $this->assertInstanceOf(Company::class, $voucher->company);
        $this->assertInstanceOf(BusinessPartner::class, $voucher->supplier);
        $this->assertInstanceOf(Currency::class, $voucher->currency);
        $this->assertEquals($company->id, $voucher->company->id);
        $this->assertEquals($supplier->id, $voucher->supplier->id);
    }

    /** @test */
    public function it_can_update_outstanding_amount_on_payment_schedule()
    {
        $company = Company::factory()->create();
        $currency = Currency::factory()->create(['is_base' => true]);
        $supplier = BusinessPartner::factory()->create(['is_supplier' => true]);

        $schedule = PaymentSchedule::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'due_date' => now()->addDays(30),
            'amount' => 5000.00,
            'paid_amount' => 2000.00,
            'outstanding_amount' => 3000.00,
        ]);

        $schedule->paid_amount = 3500.00;
        $schedule->updateOutstanding();

        $this->assertEquals(1500.00, $schedule->outstanding_amount);
    }

    /** @test */
    public function it_can_scope_overdue_schedules()
    {
        $company = Company::factory()->create();
        $currency = Currency::factory()->create(['is_base' => true]);
        $supplier = BusinessPartner::factory()->create(['is_supplier' => true]);

        // Create overdue schedule
        $overdueSchedule = PaymentSchedule::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'due_date' => now()->subDays(5),
            'amount' => 5000.00,
            'outstanding_amount' => 5000.00,
        ]);
        $overdueSchedule->setStatus('pending', 'Created');

        // Create future schedule
        $futureSchedule = PaymentSchedule::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'due_date' => now()->addDays(30),
            'amount' => 3000.00,
            'outstanding_amount' => 3000.00,
        ]);
        $futureSchedule->setStatus('pending', 'Created');

        $overdueSchedules = PaymentSchedule::overdue()->get();

        $this->assertCount(1, $overdueSchedules);
        $this->assertEquals($overdueSchedule->id, $overdueSchedules->first()->id);
    }
}
