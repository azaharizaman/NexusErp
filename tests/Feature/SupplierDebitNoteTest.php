<?php

namespace Tests\Feature;

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

    protected function setUp(): void
    {
        parent::setUp();

        // Create necessary test data
        $this->actingAs(User::factory()->create());
    }

    /** @test */
    public function it_can_create_a_supplier_debit_note()
    {
        $company = Company::factory()->create();
        $currency = Currency::factory()->create(['is_base' => true]);
        $supplier = BusinessPartner::factory()->create(['is_supplier' => true]);
        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'total_amount' => 10000.00,
            'outstanding_amount' => 10000.00,
        ]);

        $debitNote = SupplierDebitNote::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'supplier_invoice_id' => $invoice->id,
            'currency_id' => $currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 1000.00,
            'description' => 'Product return',
        ]);

        $this->assertInstanceOf(SupplierDebitNote::class, $debitNote);
        $this->assertEquals(1000.00, $debitNote->debit_amount);
        $this->assertEquals('return', $debitNote->reason);
        $this->assertNotNull($debitNote->debit_note_number);
    }

    /** @test */
    public function it_has_proper_relationships()
    {
        $company = Company::factory()->create();
        $currency = Currency::factory()->create(['is_base' => true]);
        $supplier = BusinessPartner::factory()->create(['is_supplier' => true]);
        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'total_amount' => 10000.00,
            'outstanding_amount' => 10000.00,
        ]);

        $debitNote = SupplierDebitNote::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'supplier_invoice_id' => $invoice->id,
            'currency_id' => $currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 1000.00,
            'description' => 'Product return',
        ]);

        $this->assertInstanceOf(Company::class, $debitNote->company);
        $this->assertInstanceOf(BusinessPartner::class, $debitNote->supplier);
        $this->assertInstanceOf(SupplierInvoice::class, $debitNote->supplierInvoice);
        $this->assertInstanceOf(Currency::class, $debitNote->currency);
        $this->assertEquals($company->id, $debitNote->company->id);
        $this->assertEquals($supplier->id, $debitNote->supplier->id);
        $this->assertEquals($invoice->id, $debitNote->supplierInvoice->id);
    }

    /** @test */
    public function it_can_apply_debit_note_to_invoice()
    {
        $company = Company::factory()->create();
        $currency = Currency::factory()->create(['is_base' => true]);
        $supplier = BusinessPartner::factory()->create(['is_supplier' => true]);
        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'total_amount' => 10000.00,
            'outstanding_amount' => 10000.00,
        ]);

        $debitNote = SupplierDebitNote::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'supplier_invoice_id' => $invoice->id,
            'currency_id' => $currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 1000.00,
            'description' => 'Product return',
        ]);

        // Set status to issued so it can be applied
        $debitNote->status = 'issued';
        $debitNote->save();

        $debitNote->applyToInvoice();

        // Refresh the invoice
        $invoice->refresh();

        // Check that invoice outstanding amount was reduced
        $this->assertEquals(9000.00, $invoice->outstanding_amount);
        
        // Check that debit note status is now 'applied'
        $this->assertEquals('applied', $debitNote->fresh()->status);
    }

    /** @test */
    public function it_validates_debit_note_cannot_exceed_invoice_outstanding_amount()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Debit note amount exceeds invoice outstanding amount');

        $company = Company::factory()->create();
        $currency = Currency::factory()->create(['is_base' => true]);
        $supplier = BusinessPartner::factory()->create(['is_supplier' => true]);
        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'total_amount' => 10000.00,
            'outstanding_amount' => 5000.00,
        ]);

        $debitNote = SupplierDebitNote::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'supplier_invoice_id' => $invoice->id,
            'currency_id' => $currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 6000.00, // More than outstanding
            'description' => 'Product return',
        ]);

        // Set status to issued so it can be applied
        $debitNote->status = 'issued';
        $debitNote->save();
        $debitNote->refresh();

        $debitNote->applyToInvoice();
    }

    /** @test */
    public function it_validates_only_issued_debit_notes_can_be_applied()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Only issued debit notes can be applied to invoices');

        $company = Company::factory()->create();
        $currency = Currency::factory()->create(['is_base' => true]);
        $supplier = BusinessPartner::factory()->create(['is_supplier' => true]);
        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'total_amount' => 10000.00,
            'outstanding_amount' => 10000.00,
        ]);

        $debitNote = SupplierDebitNote::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'supplier_invoice_id' => $invoice->id,
            'currency_id' => $currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 1000.00,
            'description' => 'Product return',
        ]);

        // Don't set status to issued - leave it as draft
        $debitNote->applyToInvoice();
    }

    /** @test */
    public function it_validates_debit_note_must_be_linked_to_invoice()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Debit note must be linked to a supplier invoice');

        $company = Company::factory()->create();
        $currency = Currency::factory()->create(['is_base' => true]);
        $supplier = BusinessPartner::factory()->create(['is_supplier' => true]);

        $debitNote = SupplierDebitNote::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'supplier_invoice_id' => null, // No invoice linked
            'currency_id' => $currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 1000.00,
            'description' => 'Product return',
        ]);

        // Set status to issued
        $debitNote->status = 'issued';
        $debitNote->save();
        $debitNote->refresh();

        $debitNote->applyToInvoice();
    }

    /** @test */
    public function it_can_check_if_debit_note_can_be_applied()
    {
        $company = Company::factory()->create();
        $currency = Currency::factory()->create(['is_base' => true]);
        $supplier = BusinessPartner::factory()->create(['is_supplier' => true]);
        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'total_amount' => 10000.00,
            'outstanding_amount' => 10000.00,
        ]);

        $debitNote = SupplierDebitNote::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'supplier_invoice_id' => $invoice->id,
            'currency_id' => $currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 1000.00,
            'description' => 'Product return',
        ]);

        // Initially should not be applicable (draft status)
        $this->assertFalse($debitNote->canBeApplied());

        // Set status to issued
        $debitNote->status = 'issued';
        $debitNote->save();
        $debitNote->refresh();

        // Now should be applicable
        $this->assertTrue($debitNote->canBeApplied());
    }

    /** @test */
    public function it_has_proper_scopes()
    {
        $company = Company::factory()->create();
        $currency = Currency::factory()->create(['is_base' => true]);
        $supplier = BusinessPartner::factory()->create(['is_supplier' => true]);
        $invoice = SupplierInvoice::factory()->create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'total_amount' => 10000.00,
            'outstanding_amount' => 10000.00,
        ]);

        // Create draft debit note
        $draftNote = SupplierDebitNote::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'supplier_invoice_id' => $invoice->id,
            'currency_id' => $currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 1000.00,
            'description' => 'Draft note',
        ]);

        // Create issued debit note
        $issuedNote = SupplierDebitNote::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'supplier_invoice_id' => $invoice->id,
            'currency_id' => $currency->id,
            'debit_note_date' => now(),
            'reason' => 'price_adjustment',
            'debit_amount' => 500.00,
            'description' => 'Issued note',
        ]);
        $issuedNote->status = 'issued';
        $issuedNote->save();

        // Test draft scope
        $draftNotes = SupplierDebitNote::draft()->get();
        $this->assertCount(1, $draftNotes);
        $this->assertEquals($draftNote->id, $draftNotes->first()->id);

        // Test issued scope
        $issuedNotes = SupplierDebitNote::issued()->get();
        $this->assertCount(1, $issuedNotes);
        $this->assertEquals($issuedNote->id, $issuedNotes->first()->id);

        // Test for supplier scope
        $supplierNotes = SupplierDebitNote::forSupplier($supplier->id)->get();
        $this->assertCount(2, $supplierNotes);

        // Test by reason scope
        $returnNotes = SupplierDebitNote::byReason('return')->get();
        $this->assertCount(1, $returnNotes);
        $this->assertEquals('return', $returnNotes->first()->reason);
    }

    /** @test */
    public function it_generates_serial_number_automatically()
    {
        $company = Company::factory()->create();
        $currency = Currency::factory()->create(['is_base' => true]);
        $supplier = BusinessPartner::factory()->create(['is_supplier' => true]);

        $debitNote = SupplierDebitNote::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'debit_note_date' => now(),
            'reason' => 'return',
            'debit_amount' => 1000.00,
            'description' => 'Test note',
        ]);

        $this->assertNotNull($debitNote->debit_note_number);
        $this->assertStringStartsWith('DN-', $debitNote->debit_note_number);
    }
}
