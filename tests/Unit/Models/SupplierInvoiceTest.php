<?php

namespace Tests\Unit\Models;

use App\Models\BusinessPartner;
use App\Models\Company;
use App\Models\Currency;
use App\Models\JournalEntry;
use App\Models\SupplierInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierInvoiceTest extends TestCase
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
        $this->currency = Currency::factory()->create(['code' => 'MYR']);
        $this->supplier = BusinessPartner::factory()->create([
            'is_supplier' => true,
            'name' => 'Test Supplier',
        ]);
    }

    public function test_it_can_create_a_supplier_invoice(): void
    {
        $invoice = SupplierInvoice::create([
            'invoice_number' => 'SI-2025-0001',
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'supplier_invoice_number' => 'SUPP-INV-001',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 1000.00,
            'paid_amount' => 0.00,
            'outstanding_amount' => 1000.00,
            'payment_status' => 'unpaid',
        ]);

        $this->assertNotNull($invoice->id);
        $this->assertEquals('SI-2025-0001', $invoice->invoice_number);
        $this->assertEquals(1000.00, $invoice->total_amount);
        $this->assertEquals(0.00, $invoice->paid_amount);
        $this->assertEquals(1000.00, $invoice->outstanding_amount);
        $this->assertEquals('unpaid', $invoice->payment_status);
    }

    public function test_it_calculates_outstanding_amount(): void
    {
        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'total_amount' => 1000.00,
            'paid_amount' => 300.00,
        ]);

        $invoice->calculateOutstanding();

        $this->assertEquals(700.00, $invoice->outstanding_amount);
    }

    public function test_it_checks_if_invoice_is_fully_paid(): void
    {
        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'total_amount' => 1000.00,
            'paid_amount' => 1000.00,
        ]);

        $this->assertTrue($invoice->isFullyPaid());

        $invoice->paid_amount = 999.99;
        $this->assertFalse($invoice->isFullyPaid());
    }

    public function test_it_checks_if_invoice_is_overdue(): void
    {
        // Invoice not yet due
        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'due_date' => now()->addDays(10),
            'total_amount' => 1000.00,
            'paid_amount' => 0.00,
        ]);

        $this->assertFalse($invoice->isOverdue());

        // Invoice past due date
        $overdueInvoice = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'due_date' => now()->subDays(10),
            'total_amount' => 1000.00,
            'paid_amount' => 0.00,
        ]);

        $this->assertTrue($overdueInvoice->isOverdue());

        // Paid invoice should not be overdue even if past due date
        $paidOverdueInvoice = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'due_date' => now()->subDays(10),
            'total_amount' => 1000.00,
            'paid_amount' => 1000.00,
        ]);

        $this->assertFalse($paidOverdueInvoice->isOverdue());
    }

    public function test_it_updates_payment_status_correctly(): void
    {
        // Unpaid invoice
        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'due_date' => now()->addDays(30),
            'total_amount' => 1000.00,
            'paid_amount' => 0.00,
        ]);

        $invoice->updatePaymentStatus();
        $this->assertEquals('unpaid', $invoice->payment_status);

        // Partially paid invoice
        $invoice->paid_amount = 500.00;
        $invoice->updatePaymentStatus();
        $this->assertEquals('partially_paid', $invoice->payment_status);

        // Fully paid invoice
        $invoice->paid_amount = 1000.00;
        $invoice->updatePaymentStatus();
        $this->assertEquals('paid', $invoice->payment_status);

        // Overdue invoice
        $overdueInvoice = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'due_date' => now()->subDays(10),
            'total_amount' => 1000.00,
            'paid_amount' => 0.00,
        ]);

        $overdueInvoice->updatePaymentStatus();
        $this->assertEquals('overdue', $overdueInvoice->payment_status);
    }

    public function test_it_records_payment(): void
    {
        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'total_amount' => 1000.00,
            'paid_amount' => 0.00,
            'outstanding_amount' => 1000.00,
            'payment_status' => 'unpaid',
        ]);

        $invoice->recordPayment(300.00);

        $this->assertEquals(300.00, $invoice->paid_amount);
        $this->assertEquals(700.00, $invoice->outstanding_amount);
        $this->assertEquals('partially_paid', $invoice->payment_status);

        $invoice->recordPayment(700.00);

        $this->assertEquals(1000.00, $invoice->paid_amount);
        $this->assertEquals(0.00, $invoice->outstanding_amount);
        $this->assertEquals('paid', $invoice->payment_status);
    }

    public function test_it_has_company_relationship(): void
    {
        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
        ]);

        $this->assertInstanceOf(Company::class, $invoice->company);
        $this->assertEquals($this->company->id, $invoice->company->id);
    }

    public function test_it_has_supplier_relationship(): void
    {
        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
        ]);

        $this->assertInstanceOf(BusinessPartner::class, $invoice->supplier);
        $this->assertEquals($this->supplier->id, $invoice->supplier->id);
    }

    public function test_it_has_currency_relationship(): void
    {
        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
        ]);

        $this->assertInstanceOf(Currency::class, $invoice->currency);
        $this->assertEquals($this->currency->id, $invoice->currency->id);
    }

    public function test_it_has_journal_entry_relationship(): void
    {
        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'journal_entry_id' => null,
        ]);

        // Test that relationship is null when not set
        $this->assertNull($invoice->journalEntry);
        
        // Test that the relationship method exists and returns correct type
        $relation = $invoice->journalEntry();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    }

    public function test_it_tracks_gl_posting_status(): void
    {
        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'is_posted_to_gl' => false,
        ]);

        $this->assertFalse($invoice->is_posted_to_gl);
        $this->assertNull($invoice->posted_to_gl_at);

        // Simulate posting to GL
        $invoice->is_posted_to_gl = true;
        $invoice->posted_to_gl_at = now();
        $invoice->save();

        $invoice->refresh();
        $this->assertTrue($invoice->is_posted_to_gl);
        $this->assertNotNull($invoice->posted_to_gl_at);
    }

    public function test_unpaid_scope_works(): void
    {
        SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_status' => 'unpaid',
        ]);

        SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_status' => 'paid',
        ]);

        $unpaidInvoices = SupplierInvoice::unpaid()->get();

        $this->assertCount(1, $unpaidInvoices);
        $this->assertEquals('unpaid', $unpaidInvoices->first()->payment_status);
    }

    public function test_partially_paid_scope_works(): void
    {
        SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_status' => 'partially_paid',
        ]);

        SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'payment_status' => 'paid',
        ]);

        $partiallyPaidInvoices = SupplierInvoice::partiallyPaid()->get();

        $this->assertCount(1, $partiallyPaidInvoices);
        $this->assertEquals('partially_paid', $partiallyPaidInvoices->first()->payment_status);
    }

    public function test_posted_to_gl_scope_works(): void
    {
        SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'is_posted_to_gl' => true,
        ]);

        SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'is_posted_to_gl' => false,
        ]);

        $postedInvoices = SupplierInvoice::postedToGl()->get();

        $this->assertCount(1, $postedInvoices);
        $this->assertTrue($postedInvoices->first()->is_posted_to_gl);
    }
}
