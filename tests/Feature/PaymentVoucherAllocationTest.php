<?php

namespace Tests\Feature;

use App\Models\BusinessPartner;
use App\Models\Company;
use App\Models\Currency;
use App\Models\PaymentVoucher;
use App\Models\PaymentVoucherAllocation;
use App\Models\SupplierInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentVoucherAllocationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create and authenticate a user
        $this->actingAs(User::factory()->create());
    }

    /** @test */
    public function it_can_create_a_payment_voucher_allocation()
    {
        $company = Company::factory()->create();
        $currency = Currency::factory()->create(['is_base' => true]);
        $supplier = BusinessPartner::factory()->create(['is_supplier' => true]);

        $invoice = SupplierInvoice::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 1000.00,
            'outstanding_amount' => 1000.00,
        ]);

        $voucher = PaymentVoucher::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'payment_date' => now(),
            'amount' => 500.00,
        ]);

        $allocation = PaymentVoucherAllocation::create([
            'payment_voucher_id' => $voucher->id,
            'supplier_invoice_id' => $invoice->id,
            'allocated_amount' => 500.00,
        ]);

        $this->assertInstanceOf(PaymentVoucherAllocation::class, $allocation);
        $this->assertEquals(500.00, $allocation->allocated_amount);
        $this->assertEquals($voucher->id, $allocation->payment_voucher_id);
        $this->assertEquals($invoice->id, $allocation->supplier_invoice_id);
    }

    /** @test */
    public function it_has_proper_relationships()
    {
        $company = Company::factory()->create();
        $currency = Currency::factory()->create(['is_base' => true]);
        $supplier = BusinessPartner::factory()->create(['is_supplier' => true]);

        $invoice = SupplierInvoice::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 1000.00,
            'outstanding_amount' => 1000.00,
        ]);

        $voucher = PaymentVoucher::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'payment_date' => now(),
            'amount' => 500.00,
        ]);

        $allocation = PaymentVoucherAllocation::create([
            'payment_voucher_id' => $voucher->id,
            'supplier_invoice_id' => $invoice->id,
            'allocated_amount' => 500.00,
        ]);

        $this->assertInstanceOf(PaymentVoucher::class, $allocation->paymentVoucher);
        $this->assertInstanceOf(SupplierInvoice::class, $allocation->supplierInvoice);
        $this->assertEquals($voucher->id, $allocation->paymentVoucher->id);
        $this->assertEquals($invoice->id, $allocation->supplierInvoice->id);
    }

    /** @test */
    public function it_can_scope_by_payment_voucher()
    {
        $company = Company::factory()->create();
        $currency = Currency::factory()->create(['is_base' => true]);
        $supplier = BusinessPartner::factory()->create(['is_supplier' => true]);

        $invoice1 = SupplierInvoice::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 1000.00,
            'outstanding_amount' => 1000.00,
        ]);

        $invoice2 = SupplierInvoice::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 2000.00,
            'outstanding_amount' => 2000.00,
        ]);

        $voucher = PaymentVoucher::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'payment_date' => now(),
            'amount' => 1500.00,
        ]);

        PaymentVoucherAllocation::create([
            'payment_voucher_id' => $voucher->id,
            'supplier_invoice_id' => $invoice1->id,
            'allocated_amount' => 1000.00,
        ]);

        PaymentVoucherAllocation::create([
            'payment_voucher_id' => $voucher->id,
            'supplier_invoice_id' => $invoice2->id,
            'allocated_amount' => 500.00,
        ]);

        $allocations = PaymentVoucherAllocation::forPayment($voucher->id)->get();

        $this->assertCount(2, $allocations);
    }

    /** @test */
    public function it_can_scope_by_supplier_invoice()
    {
        $company = Company::factory()->create();
        $currency = Currency::factory()->create(['is_base' => true]);
        $supplier = BusinessPartner::factory()->create(['is_supplier' => true]);

        $invoice = SupplierInvoice::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 1000.00,
            'outstanding_amount' => 1000.00,
        ]);

        $voucher1 = PaymentVoucher::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'payment_date' => now(),
            'amount' => 300.00,
        ]);

        $voucher2 = PaymentVoucher::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'payment_date' => now(),
            'amount' => 700.00,
        ]);

        PaymentVoucherAllocation::create([
            'payment_voucher_id' => $voucher1->id,
            'supplier_invoice_id' => $invoice->id,
            'allocated_amount' => 300.00,
        ]);

        PaymentVoucherAllocation::create([
            'payment_voucher_id' => $voucher2->id,
            'supplier_invoice_id' => $invoice->id,
            'allocated_amount' => 700.00,
        ]);

        $allocations = PaymentVoucherAllocation::forInvoice($invoice->id)->get();

        $this->assertCount(2, $allocations);
    }

    /** @test */
    public function it_can_allocate_payment_to_invoice()
    {
        $company = Company::factory()->create();
        $currency = Currency::factory()->create(['is_base' => true]);
        $supplier = BusinessPartner::factory()->create(['is_supplier' => true]);

        $invoice = SupplierInvoice::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 1000.00,
            'outstanding_amount' => 1000.00,
        ]);
        $invoice->setStatus('approved', 'Invoice approved');

        $voucher = PaymentVoucher::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'payment_date' => now(),
            'amount' => 500.00,
        ]);
        $voucher->setStatus('paid', 'Payment made');

        $allocation = PaymentVoucherAllocation::allocateToInvoice($voucher, $invoice, 500.00);

        $this->assertInstanceOf(PaymentVoucherAllocation::class, $allocation);
        $this->assertEquals(500.00, $allocation->allocated_amount);
        
        // Refresh invoice to get updated amounts
        $invoice->refresh();
        $this->assertEquals(500.00, $invoice->paid_amount);
        $this->assertEquals(500.00, $invoice->outstanding_amount);
    }

    /** @test */
    public function it_validates_allocation_amount_is_positive()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Allocation amount must be greater than zero');

        $company = Company::factory()->create();
        $currency = Currency::factory()->create(['is_base' => true]);
        $supplier = BusinessPartner::factory()->create(['is_supplier' => true]);

        $invoice = SupplierInvoice::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 1000.00,
            'outstanding_amount' => 1000.00,
        ]);

        $voucher = PaymentVoucher::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'payment_date' => now(),
            'amount' => 500.00,
        ]);

        PaymentVoucherAllocation::allocateToInvoice($voucher, $invoice, 0);
    }

    /** @test */
    public function it_validates_currencies_match()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Payment voucher and supplier invoice must have the same currency');

        $company = Company::factory()->create();
        $currency1 = Currency::factory()->create(['code' => 'USD', 'is_base' => true]);
        $currency2 = Currency::factory()->create(['code' => 'EUR', 'is_base' => false]);
        $supplier = BusinessPartner::factory()->create(['is_supplier' => true]);

        $invoice = SupplierInvoice::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency1->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 1000.00,
            'outstanding_amount' => 1000.00,
        ]);

        $voucher = PaymentVoucher::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency2->id,
            'payment_date' => now(),
            'amount' => 500.00,
        ]);

        PaymentVoucherAllocation::allocateToInvoice($voucher, $invoice, 500.00);
    }

    /** @test */
    public function it_validates_total_allocation_does_not_exceed_payment_amount()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Total allocated amount');

        $company = Company::factory()->create();
        $currency = Currency::factory()->create(['is_base' => true]);
        $supplier = BusinessPartner::factory()->create(['is_supplier' => true]);

        $invoice1 = SupplierInvoice::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 1000.00,
            'outstanding_amount' => 1000.00,
        ]);

        $invoice2 = SupplierInvoice::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 1000.00,
            'outstanding_amount' => 1000.00,
        ]);

        $voucher = PaymentVoucher::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'payment_date' => now(),
            'amount' => 500.00,
        ]);

        // First allocation is OK
        PaymentVoucherAllocation::allocateToInvoice($voucher, $invoice1, 300.00);
        
        // Second allocation should fail as total would be 600 > 500
        PaymentVoucherAllocation::allocateToInvoice($voucher, $invoice2, 300.00);
    }

    /** @test */
    public function it_validates_allocation_does_not_exceed_invoice_outstanding()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Allocation amount');

        $company = Company::factory()->create();
        $currency = Currency::factory()->create(['is_base' => true]);
        $supplier = BusinessPartner::factory()->create(['is_supplier' => true]);

        $invoice = SupplierInvoice::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 1000.00,
            'outstanding_amount' => 500.00, // Already partially paid
        ]);

        $voucher = PaymentVoucher::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'payment_date' => now(),
            'amount' => 1000.00,
        ]);

        // Try to allocate more than outstanding
        PaymentVoucherAllocation::allocateToInvoice($voucher, $invoice, 600.00);
    }

    /** @test */
    public function it_can_recalculate_allocations()
    {
        $company = Company::factory()->create();
        $currency = Currency::factory()->create(['is_base' => true]);
        $supplier = BusinessPartner::factory()->create(['is_supplier' => true]);

        $invoice = SupplierInvoice::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 1000.00,
            'outstanding_amount' => 1000.00,
        ]);
        $invoice->setStatus('approved', 'Invoice approved');

        $voucher1 = PaymentVoucher::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'payment_date' => now(),
            'amount' => 300.00,
        ]);
        $voucher1->setStatus('paid', 'Payment made');

        $voucher2 = PaymentVoucher::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'payment_date' => now(),
            'amount' => 700.00,
        ]);
        $voucher2->setStatus('paid', 'Payment made');

        // Create allocations
        PaymentVoucherAllocation::create([
            'payment_voucher_id' => $voucher1->id,
            'supplier_invoice_id' => $invoice->id,
            'allocated_amount' => 300.00,
        ]);

        PaymentVoucherAllocation::create([
            'payment_voucher_id' => $voucher2->id,
            'supplier_invoice_id' => $invoice->id,
            'allocated_amount' => 700.00,
        ]);

        // Recalculate
        PaymentVoucherAllocation::recalculateAllocations($invoice);

        $invoice->refresh();
        $this->assertEquals(1000.00, $invoice->paid_amount);
        $this->assertEquals(0.00, $invoice->outstanding_amount);
        $this->assertEquals('paid', $invoice->latestStatus());
    }

    /** @test */
    public function it_only_counts_paid_vouchers_in_recalculation()
    {
        $company = Company::factory()->create();
        $currency = Currency::factory()->create(['is_base' => true]);
        $supplier = BusinessPartner::factory()->create(['is_supplier' => true]);

        $invoice = SupplierInvoice::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 1000.00,
            'outstanding_amount' => 1000.00,
        ]);
        $invoice->setStatus('approved', 'Invoice approved');

        $paidVoucher = PaymentVoucher::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'payment_date' => now(),
            'amount' => 300.00,
        ]);
        $paidVoucher->setStatus('paid', 'Payment made');

        $draftVoucher = PaymentVoucher::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'payment_date' => now(),
            'amount' => 700.00,
        ]);
        $draftVoucher->setStatus('draft', 'Draft voucher');

        // Create allocations
        PaymentVoucherAllocation::create([
            'payment_voucher_id' => $paidVoucher->id,
            'supplier_invoice_id' => $invoice->id,
            'allocated_amount' => 300.00,
        ]);

        PaymentVoucherAllocation::create([
            'payment_voucher_id' => $draftVoucher->id,
            'supplier_invoice_id' => $invoice->id,
            'allocated_amount' => 700.00,
        ]);

        // Recalculate - should only count the paid voucher
        PaymentVoucherAllocation::recalculateAllocations($invoice);

        $invoice->refresh();
        $this->assertEquals(300.00, $invoice->paid_amount);
        $this->assertEquals(700.00, $invoice->outstanding_amount);
        $this->assertEquals('partially_paid', $invoice->latestStatus());
    }

    /** @test */
    public function payment_voucher_has_allocations_relationship()
    {
        $company = Company::factory()->create();
        $currency = Currency::factory()->create(['is_base' => true]);
        $supplier = BusinessPartner::factory()->create(['is_supplier' => true]);

        $invoice = SupplierInvoice::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 1000.00,
            'outstanding_amount' => 1000.00,
        ]);

        $voucher = PaymentVoucher::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'payment_date' => now(),
            'amount' => 500.00,
        ]);

        PaymentVoucherAllocation::create([
            'payment_voucher_id' => $voucher->id,
            'supplier_invoice_id' => $invoice->id,
            'allocated_amount' => 500.00,
        ]);

        $this->assertCount(1, $voucher->allocations);
        $this->assertInstanceOf(PaymentVoucherAllocation::class, $voucher->allocations->first());
    }

    /** @test */
    public function supplier_invoice_has_allocations_relationship()
    {
        $company = Company::factory()->create();
        $currency = Currency::factory()->create(['is_base' => true]);
        $supplier = BusinessPartner::factory()->create(['is_supplier' => true]);

        $invoice = SupplierInvoice::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 1000.00,
            'outstanding_amount' => 1000.00,
        ]);

        $voucher = PaymentVoucher::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'payment_date' => now(),
            'amount' => 500.00,
        ]);

        PaymentVoucherAllocation::create([
            'payment_voucher_id' => $voucher->id,
            'supplier_invoice_id' => $invoice->id,
            'allocated_amount' => 500.00,
        ]);

        $this->assertCount(1, $invoice->allocations);
        $this->assertInstanceOf(PaymentVoucherAllocation::class, $invoice->allocations->first());
    }
}
