<?php

namespace Tests\Feature\Actions;

use App\Actions\PaymentVoucher\AllocatePaymentToSupplierInvoices;
use App\Actions\PaymentVoucher\ApprovePaymentVoucher;
use App\Actions\PaymentVoucher\PlacePaymentOnHold;
use App\Models\BusinessPartner;
use App\Models\Company;
use App\Models\Currency;
use App\Models\PaymentVoucher;
use App\Models\SupplierInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentProcessingActionsTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected Currency $currency;
    protected BusinessPartner $supplier;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->company = Company::factory()->create();
        $this->currency = Currency::factory()->create(['is_base' => true]);
        $this->supplier = BusinessPartner::factory()->create(['is_supplier' => true]);
    }

    /** @test */
    public function it_can_allocate_payment_to_supplier_invoices_manually()
    {
        // Create payment voucher
        $payment = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 5000.00,
            'unallocated_amount' => 5000.00,
        ]);
        $payment->setStatus('submitted', 'Submitted for allocation');

        // Create supplier invoices
        $invoice1 = SupplierInvoice::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'invoice_date' => now()->subDays(30),
            'due_date' => now()->addDays(30),
            'total_amount' => 2000.00,
            'paid_amount' => 0,
            'outstanding_amount' => 2000.00,
        ]);
        $invoice1->setStatus('approved', 'Invoice approved');

        $invoice2 = SupplierInvoice::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'invoice_date' => now()->subDays(20),
            'due_date' => now()->addDays(40),
            'total_amount' => 1500.00,
            'paid_amount' => 0,
            'outstanding_amount' => 1500.00,
        ]);
        $invoice2->setStatus('approved', 'Invoice approved');

        // Allocate payment
        $allocations = [
            $invoice1->id => 2000.00,
            $invoice2->id => 1500.00,
        ];

        $result = AllocatePaymentToSupplierInvoices::run($payment, $allocations);

        // Assert allocations were created
        $this->assertCount(2, $result);
        $this->assertEquals(2000.00, $result[0]->allocated_amount);
        $this->assertEquals(1500.00, $result[1]->allocated_amount);

        // Assert payment was updated
        $payment->refresh();
        $this->assertEquals(3500.00, $payment->allocated_amount);
        $this->assertEquals(1500.00, $payment->unallocated_amount);

        // Assert invoices were updated
        $invoice1->refresh();
        $this->assertEquals(2000.00, $invoice1->paid_amount);
        $this->assertEquals(0, $invoice1->outstanding_amount);
        $this->assertEquals('paid', $invoice1->latestStatus());

        $invoice2->refresh();
        $this->assertEquals(1500.00, $invoice2->paid_amount);
        $this->assertEquals(0, $invoice2->outstanding_amount);
        $this->assertEquals('paid', $invoice2->latestStatus());
    }

    /** @test */
    public function it_can_allocate_payment_automatically_using_fifo()
    {
        // Create payment voucher
        $payment = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 3000.00,
            'unallocated_amount' => 3000.00,
        ]);
        $payment->setStatus('submitted', 'Submitted for allocation');

        // Create supplier invoices with different dates (oldest first for FIFO)
        $oldestInvoice = SupplierInvoice::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'invoice_date' => now()->subDays(60),
            'due_date' => now()->addDays(10),
            'total_amount' => 1500.00,
            'paid_amount' => 0,
            'outstanding_amount' => 1500.00,
        ]);
        $oldestInvoice->setStatus('approved', 'Invoice approved');

        $middleInvoice = SupplierInvoice::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'invoice_date' => now()->subDays(30),
            'due_date' => now()->addDays(20),
            'total_amount' => 1000.00,
            'paid_amount' => 0,
            'outstanding_amount' => 1000.00,
        ]);
        $middleInvoice->setStatus('approved', 'Invoice approved');

        $newestInvoice = SupplierInvoice::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'invoice_date' => now()->subDays(10),
            'due_date' => now()->addDays(30),
            'total_amount' => 2000.00,
            'paid_amount' => 0,
            'outstanding_amount' => 2000.00,
        ]);
        $newestInvoice->setStatus('approved', 'Invoice approved');

        // Allocate automatically (FIFO)
        $result = AllocatePaymentToSupplierInvoices::make()->handleAutomatic($payment);

        // Assert oldest invoices were paid first
        $oldestInvoice->refresh();
        $this->assertEquals(1500.00, $oldestInvoice->paid_amount);
        $this->assertEquals('paid', $oldestInvoice->latestStatus());

        $middleInvoice->refresh();
        $this->assertEquals(1000.00, $middleInvoice->paid_amount);
        $this->assertEquals('paid', $middleInvoice->latestStatus());

        // Newest invoice should receive partial payment
        $newestInvoice->refresh();
        $this->assertEquals(500.00, $newestInvoice->paid_amount);
        $this->assertEquals(1500.00, $newestInvoice->outstanding_amount);
        $this->assertEquals('partially_paid', $newestInvoice->latestStatus());

        // Payment should be fully allocated
        $payment->refresh();
        $this->assertEquals(3000.00, $payment->allocated_amount);
        $this->assertEquals(0, $payment->unallocated_amount);
    }

    /** @test */
    public function it_validates_allocation_doesnt_exceed_unallocated_amount()
    {
        $payment = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 1000.00,
            'unallocated_amount' => 1000.00,
        ]);
        $payment->setStatus('submitted', 'Submitted');

        $invoice = SupplierInvoice::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 2000.00,
            'paid_amount' => 0,
            'outstanding_amount' => 2000.00,
        ]);
        $invoice->setStatus('approved', 'Approved');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('exceeds unallocated payment amount');

        AllocatePaymentToSupplierInvoices::run($payment, [$invoice->id => 1500.00]);
    }

    /** @test */
    public function it_validates_allocation_doesnt_exceed_invoice_outstanding()
    {
        $payment = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 3000.00,
            'unallocated_amount' => 3000.00,
        ]);
        $payment->setStatus('submitted', 'Submitted');

        $invoice = SupplierInvoice::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 1000.00,
            'paid_amount' => 0,
            'outstanding_amount' => 1000.00,
        ]);
        $invoice->setStatus('approved', 'Approved');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('exceeds invoice');

        AllocatePaymentToSupplierInvoices::run($payment, [$invoice->id => 1500.00]);
    }

    /** @test */
    public function it_cannot_allocate_payment_on_hold()
    {
        $payment = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 1000.00,
            'unallocated_amount' => 1000.00,
            'is_on_hold' => true,
            'hold_reason' => 'Pending verification',
        ]);
        $payment->setStatus('submitted', 'Submitted');

        $invoice = SupplierInvoice::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 500.00,
            'paid_amount' => 0,
            'outstanding_amount' => 500.00,
        ]);
        $invoice->setStatus('approved', 'Approved');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('payment is on hold');

        AllocatePaymentToSupplierInvoices::run($payment, [$invoice->id => 500.00]);
    }

    /** @test */
    public function it_can_approve_payment_voucher_with_allocations()
    {
        $payment = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 1000.00,
            'unallocated_amount' => 1000.00,
        ]);
        $payment->setStatus('submitted', 'Submitted');

        $invoice = SupplierInvoice::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 1000.00,
            'paid_amount' => 0,
            'outstanding_amount' => 1000.00,
        ]);
        $invoice->setStatus('approved', 'Approved');

        // Allocate first
        AllocatePaymentToSupplierInvoices::run($payment, [$invoice->id => 1000.00]);

        // Force reload of the model with relationships
        $payment = PaymentVoucher::find($payment->id);

        // Now approve
        $approvedPayment = ApprovePaymentVoucher::run($payment);

        $this->assertNotNull($approvedPayment->approved_by);
        $this->assertNotNull($approvedPayment->approved_at);
        $this->assertEquals('approved', $approvedPayment->latestStatus());
    }

    /** @test */
    public function it_cannot_approve_payment_without_allocations()
    {
        $payment = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 1000.00,
            'unallocated_amount' => 1000.00,
        ]);
        $payment->setStatus('submitted', 'Submitted');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('must have at least one allocation');

        ApprovePaymentVoucher::run($payment);
    }

    /** @test */
    public function it_cannot_approve_payment_on_hold()
    {
        $payment = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 1000.00,
            'unallocated_amount' => 1000.00,
            'is_on_hold' => true,
            'hold_reason' => 'Pending verification',
        ]);
        $payment->setStatus('submitted', 'Submitted');

        $invoice = SupplierInvoice::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 1000.00,
            'paid_amount' => 0,
            'outstanding_amount' => 1000.00,
        ]);
        $invoice->setStatus('approved', 'Approved');

        // Allocate first
        $payment->is_on_hold = false;
        $payment->save();
        AllocatePaymentToSupplierInvoices::run($payment, [$invoice->id => 1000.00]);
        $payment->is_on_hold = true;
        $payment->save();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('on hold and cannot be approved');

        ApprovePaymentVoucher::run($payment);
    }

    /** @test */
    public function it_can_place_payment_on_hold()
    {
        $payment = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 1000.00,
            'unallocated_amount' => 1000.00,
        ]);
        $payment->setStatus('submitted', 'Submitted');

        $heldPayment = PlacePaymentOnHold::run(
            $payment,
            'Supplier documentation incomplete',
            false // Don't send notification in tests
        );

        $this->assertTrue($heldPayment->is_on_hold);
        $this->assertEquals('Supplier documentation incomplete', $heldPayment->hold_reason);
        $this->assertNotNull($heldPayment->held_by);
        $this->assertNotNull($heldPayment->held_at);
        $this->assertEquals($this->user->id, $heldPayment->held_by);
    }

    /** @test */
    public function it_can_remove_hold_from_payment()
    {
        $payment = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 1000.00,
            'unallocated_amount' => 1000.00,
            'is_on_hold' => true,
            'hold_reason' => 'Test hold',
            'held_by' => $this->user->id,
            'held_at' => now(),
        ]);
        $payment->setStatus('submitted', 'Submitted');

        $releasedPayment = PlacePaymentOnHold::make()->handleRemoveHold(
            $payment,
            'Documentation received'
        );

        $this->assertFalse($releasedPayment->is_on_hold);
        $this->assertNull($releasedPayment->hold_reason);
        $this->assertNull($releasedPayment->held_by);
        $this->assertNull($releasedPayment->held_at);
    }

    /** @test */
    public function it_cannot_place_paid_voucher_on_hold()
    {
        $payment = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 1000.00,
        ]);
        $payment->setStatus('paid', 'Payment completed');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('current status is paid');

        PlacePaymentOnHold::run($payment, 'Some reason');
    }

    /** @test */
    public function it_validates_hold_reason_is_required()
    {
        $payment = PaymentVoucher::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_date' => now(),
            'amount' => 1000.00,
        ]);
        $payment->setStatus('submitted', 'Submitted');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Hold reason is required');

        PlacePaymentOnHold::run($payment, '   '); // Empty reason
    }
}
