<?php

namespace Tests\Unit\Models;

use App\Models\BusinessPartner;
use App\Models\Company;
use App\Models\Currency;
use App\Models\SupplierDebitNote;
use App\Models\SupplierInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierDebitNoteTest extends TestCase
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

    public function test_it_can_create_a_supplier_debit_note(): void
    {
        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'total_amount' => 1000.00,
            'outstanding_amount' => 1000.00,
        ]);

        $debitNote = SupplierDebitNote::create([
            'debit_note_number' => 'DN-2025-0001',
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'supplier_invoice_id' => $invoice->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 200.00,
            'status' => 'draft',
            'description' => 'Test debit note',
        ]);

        $this->assertNotNull($debitNote->id);
        $this->assertEquals('DN-2025-0001', $debitNote->debit_note_number);
        $this->assertEquals(200.00, $debitNote->debit_amount);
        $this->assertEquals('draft', $debitNote->status);
    }

    public function test_it_can_apply_debit_note_to_invoice(): void
    {
        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'total_amount' => 1000.00,
            'outstanding_amount' => 1000.00,
        ]);

        $debitNote = SupplierDebitNote::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'supplier_invoice_id' => $invoice->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 200.00,
            'status' => 'issued',
            'description' => 'Test debit note',
        ]);

        $debitNote->applyToInvoice();

        $this->assertEquals('applied', $debitNote->status);
        
        $invoice->refresh();
        $this->assertEquals(800.00, $invoice->outstanding_amount);
    }

    public function test_it_throws_exception_when_applying_non_issued_debit_note(): void
    {
        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'total_amount' => 1000.00,
            'outstanding_amount' => 1000.00,
        ]);

        $debitNote = SupplierDebitNote::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'supplier_invoice_id' => $invoice->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 200.00,
            'status' => 'draft',
            'description' => 'Test debit note',
        ]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Only issued debit notes can be applied to invoices');

        $debitNote->applyToInvoice();
    }

    public function test_it_throws_exception_when_applying_debit_note_without_invoice(): void
    {
        $debitNote = SupplierDebitNote::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'supplier_invoice_id' => null,
            'currency_id' => $this->currency->id,
            'debit_note_date' => now(),
            'reason' => 'price_adjustment',
            'debit_amount' => 200.00,
            'status' => 'issued',
            'description' => 'Test debit note',
        ]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Debit note must be linked to a supplier invoice');

        $debitNote->applyToInvoice();
    }

    public function test_it_throws_exception_when_debit_amount_exceeds_invoice_outstanding(): void
    {
        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'total_amount' => 1000.00,
            'outstanding_amount' => 100.00, // Already mostly paid
        ]);

        $debitNote = SupplierDebitNote::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'supplier_invoice_id' => $invoice->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 200.00,
            'status' => 'issued',
            'description' => 'Test debit note',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Debit note amount exceeds invoice outstanding amount');

        $debitNote->applyToInvoice();
    }

    public function test_it_can_check_if_debit_note_can_be_applied(): void
    {
        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'total_amount' => 1000.00,
            'outstanding_amount' => 1000.00,
        ]);

        // Can be applied
        $debitNote1 = SupplierDebitNote::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'supplier_invoice_id' => $invoice->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 200.00,
            'status' => 'issued',
            'description' => 'Test debit note',
        ]);
        $this->assertTrue($debitNote1->canBeApplied());

        // Cannot be applied - wrong status
        $debitNote2 = SupplierDebitNote::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'supplier_invoice_id' => $invoice->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 200.00,
            'status' => 'draft',
            'description' => 'Test debit note',
        ]);
        $this->assertFalse($debitNote2->canBeApplied());

        // Cannot be applied - no invoice
        $debitNote3 = SupplierDebitNote::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'supplier_invoice_id' => null,
            'currency_id' => $this->currency->id,
            'debit_note_date' => now(),
            'reason' => 'price_adjustment',
            'debit_amount' => 200.00,
            'status' => 'issued',
            'description' => 'Test debit note',
        ]);
        $this->assertFalse($debitNote3->canBeApplied());

        // Cannot be applied - zero amount
        $debitNote4 = SupplierDebitNote::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'supplier_invoice_id' => $invoice->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 0.00,
            'status' => 'issued',
            'description' => 'Test debit note',
        ]);
        $this->assertFalse($debitNote4->canBeApplied());
    }

    public function test_draft_scope_works(): void
    {
        SupplierDebitNote::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 100.00,
            'status' => 'draft',
            'description' => 'Test debit note',
        ]);

        SupplierDebitNote::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 100.00,
            'status' => 'issued',
            'description' => 'Test debit note',
        ]);

        $draftNotes = SupplierDebitNote::draft()->get();

        $this->assertCount(1, $draftNotes);
        $this->assertEquals('draft', $draftNotes->first()->status);
    }

    public function test_issued_scope_works(): void
    {
        SupplierDebitNote::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 100.00,
            'status' => 'issued',
            'description' => 'Test debit note',
        ]);

        SupplierDebitNote::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 100.00,
            'status' => 'draft',
            'description' => 'Test debit note',
        ]);

        $issuedNotes = SupplierDebitNote::issued()->get();

        $this->assertCount(1, $issuedNotes);
        $this->assertEquals('issued', $issuedNotes->first()->status);
    }

    public function test_applied_scope_works(): void
    {
        SupplierDebitNote::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 100.00,
            'status' => 'applied',
            'description' => 'Test debit note',
        ]);

        SupplierDebitNote::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 100.00,
            'status' => 'issued',
            'description' => 'Test debit note',
        ]);

        $appliedNotes = SupplierDebitNote::applied()->get();

        $this->assertCount(1, $appliedNotes);
        $this->assertEquals('applied', $appliedNotes->first()->status);
    }

    public function test_cancelled_scope_works(): void
    {
        SupplierDebitNote::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 100.00,
            'status' => 'cancelled',
            'description' => 'Test debit note',
        ]);

        SupplierDebitNote::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 100.00,
            'status' => 'issued',
            'description' => 'Test debit note',
        ]);

        $cancelledNotes = SupplierDebitNote::cancelled()->get();

        $this->assertCount(1, $cancelledNotes);
        $this->assertEquals('cancelled', $cancelledNotes->first()->status);
    }

    public function test_for_supplier_scope_works(): void
    {
        $supplier2 = BusinessPartner::factory()->create([
            'is_supplier' => true,
            'name' => 'Another Supplier',
        ]);

        SupplierDebitNote::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 100.00,
            'status' => 'issued',
            'description' => 'Test debit note',
        ]);

        SupplierDebitNote::create([
            'company_id' => $this->company->id,
            'supplier_id' => $supplier2->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 100.00,
            'status' => 'issued',
            'description' => 'Test debit note',
        ]);

        $supplierNotes = SupplierDebitNote::forSupplier($this->supplier->id)->get();

        $this->assertCount(1, $supplierNotes);
        $this->assertEquals($this->supplier->id, $supplierNotes->first()->supplier_id);
    }

    public function test_for_company_scope_works(): void
    {
        $company2 = Company::factory()->create();

        SupplierDebitNote::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 100.00,
            'status' => 'issued',
            'description' => 'Test debit note',
        ]);

        SupplierDebitNote::create([
            'company_id' => $company2->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 100.00,
            'status' => 'issued',
            'description' => 'Test debit note',
        ]);

        $companyNotes = SupplierDebitNote::forCompany($this->company->id)->get();

        $this->assertCount(1, $companyNotes);
        $this->assertEquals($this->company->id, $companyNotes->first()->company_id);
    }

    public function test_for_invoice_scope_works(): void
    {
        $invoice1 = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
        ]);

        $invoice2 = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
        ]);

        SupplierDebitNote::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'supplier_invoice_id' => $invoice1->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 100.00,
            'status' => 'issued',
            'description' => 'Test debit note',
        ]);

        SupplierDebitNote::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'supplier_invoice_id' => $invoice2->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 100.00,
            'status' => 'issued',
            'description' => 'Test debit note',
        ]);

        $invoiceNotes = SupplierDebitNote::forInvoice($invoice1->id)->get();

        $this->assertCount(1, $invoiceNotes);
        $this->assertEquals($invoice1->id, $invoiceNotes->first()->supplier_invoice_id);
    }

    public function test_posted_to_gl_scope_works(): void
    {
        SupplierDebitNote::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 100.00,
            'status' => 'issued',
            'description' => 'Test debit note',
            'is_posted_to_gl' => true,
        ]);

        SupplierDebitNote::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 100.00,
            'status' => 'issued',
            'description' => 'Test debit note',
            'is_posted_to_gl' => false,
        ]);

        $postedNotes = SupplierDebitNote::postedToGl()->get();

        $this->assertCount(1, $postedNotes);
        $this->assertTrue($postedNotes->first()->is_posted_to_gl);
    }

    public function test_by_reason_scope_works(): void
    {
        SupplierDebitNote::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 100.00,
            'status' => 'issued',
            'description' => 'Test debit note',
        ]);

        SupplierDebitNote::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => now(),
            'reason' => 'price_adjustment',
            'debit_amount' => 100.00,
            'status' => 'issued',
            'description' => 'Test debit note',
        ]);

        $returnNotes = SupplierDebitNote::byReason('return')->get();

        $this->assertCount(1, $returnNotes);
        $this->assertEquals('return', $returnNotes->first()->reason);
    }

    public function test_it_has_company_relationship(): void
    {
        $debitNote = SupplierDebitNote::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 100.00,
            'status' => 'draft',
            'description' => 'Test debit note',
        ]);

        $this->assertInstanceOf(Company::class, $debitNote->company);
        $this->assertEquals($this->company->id, $debitNote->company->id);
    }

    public function test_it_has_supplier_relationship(): void
    {
        $debitNote = SupplierDebitNote::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 100.00,
            'status' => 'draft',
            'description' => 'Test debit note',
        ]);

        $this->assertInstanceOf(BusinessPartner::class, $debitNote->supplier);
        $this->assertEquals($this->supplier->id, $debitNote->supplier->id);
    }

    public function test_it_has_currency_relationship(): void
    {
        $debitNote = SupplierDebitNote::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 100.00,
            'status' => 'draft',
            'description' => 'Test debit note',
        ]);

        $this->assertInstanceOf(Currency::class, $debitNote->currency);
        $this->assertEquals($this->currency->id, $debitNote->currency->id);
    }

    public function test_it_has_supplier_invoice_relationship(): void
    {
        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
        ]);

        $debitNote = SupplierDebitNote::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'supplier_invoice_id' => $invoice->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 100.00,
            'status' => 'draft',
            'description' => 'Test debit note',
        ]);

        $this->assertInstanceOf(SupplierInvoice::class, $debitNote->supplierInvoice);
        $this->assertEquals($invoice->id, $debitNote->supplierInvoice->id);
    }

    public function test_it_tracks_gl_posting_status(): void
    {
        $debitNote = SupplierDebitNote::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 100.00,
            'status' => 'draft',
            'description' => 'Test debit note',
            'is_posted_to_gl' => false,
        ]);

        $this->assertFalse($debitNote->is_posted_to_gl);
        $this->assertNull($debitNote->posted_to_gl_at);

        // Simulate posting to GL
        $debitNote->is_posted_to_gl = true;
        $debitNote->posted_to_gl_at = now();
        $debitNote->save();

        $debitNote->refresh();
        $this->assertTrue($debitNote->is_posted_to_gl);
        $this->assertNotNull($debitNote->posted_to_gl_at);
    }
}
