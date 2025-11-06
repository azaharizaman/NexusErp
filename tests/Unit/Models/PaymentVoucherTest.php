<?php

namespace Tests\Unit\Models;

use App\Models\BusinessPartner;
use App\Models\Company;
use App\Models\Currency;
use App\Models\PaymentVoucher;
use App\Models\SupplierInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentVoucherTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;
    protected BusinessPartner $supplier;
    protected Currency $currency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->company = Company::factory()->create();
        $this->currency = Currency::factory()->create(['code' => 'MYR', 'is_base' => true]);
        $this->supplier = BusinessPartner::factory()->create([
            'is_supplier' => true,
            'name' => 'Test Supplier',
        ]);
    }

    public function test_it_can_create_a_payment_voucher(): void
    {
        $voucher = PaymentVoucher::create([
            'voucher_number' => 'PV-2025-0001',
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'payment_method' => 'bank_transfer',
            'amount' => 1000.00,
            'allocated_amount' => 0.00,
            'unallocated_amount' => 1000.00,
        ]);

        $this->assertNotNull($voucher->id);
        $this->assertEquals('PV-2025-0001', $voucher->voucher_number);
        $this->assertEquals(1000.00, $voucher->amount);
        $this->assertEquals(0.00, $voucher->allocated_amount);
        $this->assertEquals(1000.00, $voucher->unallocated_amount);
    }

    public function test_it_can_allocate_payment_to_invoice(): void
    {
        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'total_amount' => 1000.00,
            'paid_amount' => 0.00,
            'outstanding_amount' => 1000.00,
        ]);

        $voucher = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 500.00,
            'allocated_amount' => 0.00,
            'unallocated_amount' => 500.00,
        ]);

        $allocation = $voucher->allocateToInvoice($invoice, 500.00);

        $this->assertNotNull($allocation->id);
        $this->assertEquals(500.00, $allocation->allocated_amount);
        
        $voucher->refresh();
        $this->assertEquals(500.00, $voucher->allocated_amount);
        $this->assertEquals(0.00, $voucher->unallocated_amount);
        
        $invoice->refresh();
        $this->assertEquals(500.00, $invoice->paid_amount);
        $this->assertEquals(500.00, $invoice->outstanding_amount);
    }

    public function test_it_throws_exception_when_allocation_exceeds_unallocated_amount(): void
    {
        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'total_amount' => 1000.00,
            'outstanding_amount' => 1000.00,
        ]);

        $voucher = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 500.00,
            'allocated_amount' => 0.00,
            'unallocated_amount' => 500.00,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Allocation amount exceeds unallocated payment amount');

        $voucher->allocateToInvoice($invoice, 600.00);
    }

    public function test_it_throws_exception_when_allocation_exceeds_invoice_outstanding(): void
    {
        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'total_amount' => 1000.00,
            'outstanding_amount' => 300.00, // Already partially paid
        ]);

        $voucher = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 500.00,
            'allocated_amount' => 0.00,
            'unallocated_amount' => 500.00,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Allocation amount exceeds invoice outstanding amount');

        $voucher->allocateToInvoice($invoice, 400.00);
    }

    public function test_it_checks_if_payment_is_fully_allocated(): void
    {
        $voucher = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 500.00,
            'allocated_amount' => 500.00,
            'unallocated_amount' => 0.00,
        ]);

        $this->assertTrue($voucher->isFullyAllocated());

        $voucher->unallocated_amount = 100.00;
        $this->assertFalse($voucher->isFullyAllocated());
    }

    public function test_it_recalculates_allocations(): void
    {
        $invoice1 = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'total_amount' => 1000.00,
            'outstanding_amount' => 1000.00,
        ]);

        $invoice2 = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'total_amount' => 1000.00,
            'outstanding_amount' => 1000.00,
        ]);

        $voucher = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 1000.00,
            'allocated_amount' => 0.00,
            'unallocated_amount' => 1000.00,
        ]);

        $voucher->allocateToInvoice($invoice1, 300.00);
        $voucher->allocateToInvoice($invoice2, 200.00);

        // Manually set incorrect amounts to test recalculation
        $voucher->allocated_amount = 0.00;
        $voucher->unallocated_amount = 1000.00;
        $voucher->save();

        $voucher->recalculateAllocations();

        $this->assertEquals(500.00, $voucher->allocated_amount);
        $this->assertEquals(500.00, $voucher->unallocated_amount);
    }

    public function test_it_can_check_if_voucher_can_be_approved(): void
    {
        $voucher = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 500.00,
        ]);

        $voucher->setStatus('submitted', 'Test submission');
        $this->assertTrue($voucher->canApprove());

        $voucher->setStatus('draft', 'Back to draft');
        $this->assertFalse($voucher->canApprove());

        $voucher->setStatus('approved', 'Test approval');
        $this->assertFalse($voucher->canApprove());
    }

    public function test_it_can_check_if_voucher_can_be_paid(): void
    {
        $voucher = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 500.00,
        ]);

        $voucher->setStatus('approved', 'Test approval');
        $this->assertTrue($voucher->canPay());

        $voucher->setStatus('submitted', 'Back to submitted');
        $this->assertFalse($voucher->canPay());

        $voucher->setStatus('paid', 'Test payment');
        $this->assertFalse($voucher->canPay());
    }

    public function test_it_can_check_if_voucher_can_be_voided(): void
    {
        $voucher = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 500.00,
        ]);

        $voucher->setStatus('draft', 'Test');
        $this->assertTrue($voucher->canVoid());

        $voucher->setStatus('submitted', 'Test');
        $this->assertTrue($voucher->canVoid());

        $voucher->setStatus('approved', 'Test');
        $this->assertTrue($voucher->canVoid());

        $voucher->setStatus('paid', 'Test');
        $this->assertFalse($voucher->canVoid());

        $voucher->setStatus('voided', 'Test');
        $this->assertFalse($voucher->canVoid());
    }

    public function test_draft_scope_works(): void
    {
        $voucher1 = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 500.00,
        ]);
        $voucher1->setStatus('draft', 'Test');

        $voucher2 = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 500.00,
        ]);
        $voucher2->setStatus('approved', 'Test');

        $draftVouchers = PaymentVoucher::draft()->get();

        $this->assertCount(1, $draftVouchers);
        $this->assertEquals('draft', $draftVouchers->first()->latestStatus());
    }

    public function test_approved_scope_works(): void
    {
        $voucher1 = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 500.00,
        ]);
        $voucher1->setStatus('approved', 'Test');

        $voucher2 = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 500.00,
        ]);
        $voucher2->setStatus('draft', 'Test');

        $approvedVouchers = PaymentVoucher::approved()->get();

        $this->assertCount(1, $approvedVouchers);
        $this->assertEquals('approved', $approvedVouchers->first()->latestStatus());
    }

    public function test_paid_scope_works(): void
    {
        $voucher1 = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 500.00,
        ]);
        $voucher1->setStatus('paid', 'Test');

        $voucher2 = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 500.00,
        ]);
        $voucher2->setStatus('approved', 'Test');

        $paidVouchers = PaymentVoucher::paid()->get();

        $this->assertCount(1, $paidVouchers);
        $this->assertEquals('paid', $paidVouchers->first()->latestStatus());
    }

    public function test_on_hold_scope_works(): void
    {
        $voucher1 = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 500.00,
            'is_on_hold' => true,
        ]);

        $voucher2 = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 500.00,
            'is_on_hold' => false,
        ]);

        $onHoldVouchers = PaymentVoucher::onHold()->get();

        $this->assertCount(1, $onHoldVouchers);
        $this->assertTrue($onHoldVouchers->first()->is_on_hold);
    }

    public function test_unallocated_scope_works(): void
    {
        $voucher1 = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 500.00,
            'unallocated_amount' => 500.00,
        ]);

        $voucher2 = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 500.00,
            'unallocated_amount' => 0.00,
        ]);

        $unallocatedVouchers = PaymentVoucher::unallocated()->get();

        $this->assertCount(1, $unallocatedVouchers);
        $this->assertGreaterThan(0, $unallocatedVouchers->first()->unallocated_amount);
    }

    public function test_posted_to_gl_scope_works(): void
    {
        $voucher1 = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 500.00,
            'is_posted_to_gl' => true,
        ]);

        $voucher2 = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 500.00,
            'is_posted_to_gl' => false,
        ]);

        $postedVouchers = PaymentVoucher::postedToGl()->get();

        $this->assertCount(1, $postedVouchers);
        $this->assertTrue($postedVouchers->first()->is_posted_to_gl);
    }

    public function test_it_has_company_relationship(): void
    {
        $voucher = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 500.00,
        ]);

        $this->assertInstanceOf(Company::class, $voucher->company);
        $this->assertEquals($this->company->id, $voucher->company->id);
    }

    public function test_it_has_supplier_relationship(): void
    {
        $voucher = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 500.00,
        ]);

        $this->assertInstanceOf(BusinessPartner::class, $voucher->supplier);
        $this->assertEquals($this->supplier->id, $voucher->supplier->id);
    }

    public function test_it_has_currency_relationship(): void
    {
        $voucher = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 500.00,
        ]);

        $this->assertInstanceOf(Currency::class, $voucher->currency);
        $this->assertEquals($this->currency->id, $voucher->currency->id);
    }

    public function test_it_has_allocations_relationship(): void
    {
        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'total_amount' => 1000.00,
            'outstanding_amount' => 1000.00,
        ]);

        $voucher = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 500.00,
            'allocated_amount' => 0.00,
            'unallocated_amount' => 500.00,
        ]);

        $voucher->allocateToInvoice($invoice, 500.00);

        $this->assertCount(1, $voucher->allocations);
        $this->assertEquals(500.00, $voucher->allocations->first()->allocated_amount);
    }

    public function test_it_tracks_gl_posting_status(): void
    {
        $voucher = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 500.00,
            'is_posted_to_gl' => false,
        ]);

        $this->assertFalse($voucher->is_posted_to_gl);
        $this->assertNull($voucher->posted_to_gl_at);

        // Simulate posting to GL
        $voucher->is_posted_to_gl = true;
        $voucher->posted_to_gl_at = now();
        $voucher->save();

        $voucher->refresh();
        $this->assertTrue($voucher->is_posted_to_gl);
        $this->assertNotNull($voucher->posted_to_gl_at);
    }
}
