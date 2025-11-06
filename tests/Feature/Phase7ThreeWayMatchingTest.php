<?php

namespace Tests\Feature;

use App\Actions\InvoiceMatching\PerformThreeWayMatching;
use App\Actions\InvoiceMatching\ValidateThreeWayMatch;
use App\Models\BusinessPartner;
use App\Models\Company;
use App\Models\Currency;
use App\Models\GoodsReceivedNote;
use App\Models\GoodsReceivedNoteItem;
use App\Models\InvoiceMatching;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\SupplierInvoice;
use App\Models\SupplierInvoiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase7ThreeWayMatchingTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected Currency $currency;
    protected BusinessPartner $supplier;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create necessary test data
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->company = Company::factory()->create();
        $this->currency = Currency::factory()->create(['is_base' => true]);
        $this->supplier = BusinessPartner::factory()->create(['is_supplier' => true]);
    }

    /** @test */
    public function it_can_create_a_purchase_order_with_items()
    {
        $po = PurchaseOrder::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'po_number' => 'PO-2025-0001',
            'po_date' => now(),
            'subtotal' => 1000.00,
            'tax_amount' => 100.00,
            'total_amount' => 1100.00,
            'status' => 'issued',
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'item_code' => 'ITEM-001',
            'item_description' => 'Test Item 1',
            'quantity' => 10,
            'uom_id' => null, // No UOM required for testing
            'unit_price' => 100.00,
            'line_total' => 1000.00,
            'sort_order' => 1,
        ]);

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'po_number' => 'PO-2025-0001',
        ]);

        $this->assertDatabaseHas('purchase_order_items', [
            'purchase_order_id' => $po->id,
            'item_code' => 'ITEM-001',
        ]);
    }

    /** @test */
    public function it_can_create_a_goods_received_note()
    {
        $po = $this->createPurchaseOrder();

        $grn = GoodsReceivedNote::create([
            'company_id' => $this->company->id,
            'purchase_order_id' => $po->id,
            'supplier_id' => $this->supplier->id,
            'grn_number' => 'GRN-2025-0001',
            'received_date' => now(),
            'status' => 'completed',
        ]);

        $poItem = $po->items->first();

        GoodsReceivedNoteItem::create([
            'goods_received_note_id' => $grn->id,
            'purchase_order_item_id' => $poItem->id,
            'item_code' => $poItem->item_code,
            'item_description' => $poItem->item_description,
            'ordered_quantity' => 10,
            'received_quantity' => 10,
            'uom_id' => null, // No UOM required for testing
            'sort_order' => 1,
        ]);

        $this->assertDatabaseHas('goods_received_notes', [
            'id' => $grn->id,
            'grn_number' => 'GRN-2025-0001',
        ]);

        $this->assertDatabaseHas('goods_received_note_items', [
            'goods_received_note_id' => $grn->id,
            'received_quantity' => 10,
        ]);
    }

    /** @test */
    public function it_can_create_a_supplier_invoice()
    {
        $po = $this->createPurchaseOrder();
        $grn = $this->createGoodsReceivedNote($po);

        $invoice = SupplierInvoice::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'purchase_order_id' => $po->id,
            'goods_received_note_id' => $grn->id,
            'currency_id' => $this->currency->id,
            'invoice_number' => 'INV-2025-0001',
            'supplier_invoice_number' => 'SUP-INV-001',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 1000.00,
            'tax_amount' => 100.00,
            'total_amount' => 1100.00,
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('supplier_invoices', [
            'id' => $invoice->id,
            'invoice_number' => 'INV-2025-0001',
            'purchase_order_id' => $po->id,
        ]);
    }

    /** @test */
    public function it_performs_three_way_matching_with_exact_match()
    {
        $po = $this->createPurchaseOrder();
        $grn = $this->createGoodsReceivedNote($po);
        $invoice = $this->createSupplierInvoice($po, $grn);

        // Add invoice items that match PO exactly
        $poItem = $po->items->first();
        $grnItem = $grn->items->first();

        SupplierInvoiceItem::create([
            'supplier_invoice_id' => $invoice->id,
            'purchase_order_item_id' => $poItem->id,
            'goods_received_note_item_id' => $grnItem->id,
            'item_code' => $poItem->item_code,
            'item_description' => $poItem->item_description,
            'quantity' => 10,
            'unit_price' => 100.00,
            'line_total' => 1000.00,
            'sort_order' => 1,
        ]);

        $matching = ValidateThreeWayMatch::run($invoice);

        $this->assertNotNull($matching);
        $this->assertEquals(InvoiceMatching::STATUS_MATCHED, $matching->matching_status);
        $this->assertTrue($matching->is_within_tolerance);
        $this->assertEmpty($matching->mismatches);
    }

    /** @test */
    public function it_detects_quantity_mismatch()
    {
        $po = $this->createPurchaseOrder();
        $grn = $this->createGoodsReceivedNote($po);
        $invoice = $this->createSupplierInvoice($po, $grn);

        // Add invoice items with different quantity
        $poItem = $po->items->first();
        $grnItem = $grn->items->first();

        SupplierInvoiceItem::create([
            'supplier_invoice_id' => $invoice->id,
            'purchase_order_item_id' => $poItem->id,
            'goods_received_note_item_id' => $grnItem->id,
            'item_code' => $poItem->item_code,
            'item_description' => $poItem->item_description,
            'quantity' => 12, // Different from GRN quantity
            'unit_price' => 100.00,
            'line_total' => 1200.00,
            'sort_order' => 1,
        ]);

        // Update invoice total
        $invoice->update(['total_amount' => 1200.00]);

        $matching = ValidateThreeWayMatch::run($invoice);

        $this->assertEquals(InvoiceMatching::STATUS_QUANTITY_MISMATCH, $matching->matching_status);
        $this->assertNotEmpty($matching->mismatches);
        $this->assertEquals('quantity_mismatch', $matching->mismatches[0]['type']);
    }

    /** @test */
    public function it_detects_price_mismatch()
    {
        $po = $this->createPurchaseOrder();
        $grn = $this->createGoodsReceivedNote($po);
        $invoice = $this->createSupplierInvoice($po, $grn);

        // Add invoice items with different price
        $poItem = $po->items->first();
        $grnItem = $grn->items->first();

        SupplierInvoiceItem::create([
            'supplier_invoice_id' => $invoice->id,
            'purchase_order_item_id' => $poItem->id,
            'goods_received_note_item_id' => $grnItem->id,
            'item_code' => $poItem->item_code,
            'item_description' => $poItem->item_description,
            'quantity' => 10,
            'unit_price' => 120.00, // Different from PO price
            'line_total' => 1200.00,
            'sort_order' => 1,
        ]);

        // Update invoice total
        $invoice->update(['total_amount' => 1200.00]);

        $matching = ValidateThreeWayMatch::run($invoice);

        $this->assertEquals(InvoiceMatching::STATUS_PRICE_MISMATCH, $matching->matching_status);
        $this->assertNotEmpty($matching->mismatches);
        $this->assertEquals('price_mismatch', $matching->mismatches[0]['type']);
    }

    /** @test */
    public function it_matches_within_tolerance()
    {
        $po = $this->createPurchaseOrder();
        $grn = $this->createGoodsReceivedNote($po);
        $invoice = $this->createSupplierInvoice($po, $grn);

        // Add invoice items with slight price difference
        $poItem = $po->items->first();
        $grnItem = $grn->items->first();

        SupplierInvoiceItem::create([
            'supplier_invoice_id' => $invoice->id,
            'purchase_order_item_id' => $poItem->id,
            'goods_received_note_item_id' => $grnItem->id,
            'item_code' => $poItem->item_code,
            'item_description' => $poItem->item_description,
            'quantity' => 10,
            'unit_price' => 103.00, // 3% higher than PO price - within 5% tolerance
            'line_total' => 1030.00,
            'sort_order' => 1,
        ]);

        // Update invoice total
        $invoice->update(['total_amount' => 1030.00]);

        $matching = ValidateThreeWayMatch::run($invoice, 5.0);

        $this->assertEquals(InvoiceMatching::STATUS_MATCHED, $matching->matching_status);
        $this->assertTrue($matching->is_within_tolerance);
        $this->assertLessThanOrEqual(5.0, $matching->variance_percentage);
    }

    /** @test */
    public function it_blocks_approval_when_matching_fails()
    {
        $po = $this->createPurchaseOrder();
        $grn = $this->createGoodsReceivedNote($po);
        $invoice = $this->createSupplierInvoice($po, $grn);

        // Add invoice items with large price difference
        $poItem = $po->items->first();
        $grnItem = $grn->items->first();

        SupplierInvoiceItem::create([
            'supplier_invoice_id' => $invoice->id,
            'purchase_order_item_id' => $poItem->id,
            'goods_received_note_item_id' => $grnItem->id,
            'item_code' => $poItem->item_code,
            'item_description' => $poItem->item_description,
            'quantity' => 10,
            'unit_price' => 150.00, // 50% higher than PO price - exceeds tolerance
            'line_total' => 1500.00,
            'sort_order' => 1,
        ]);

        // Update invoice total
        $invoice->update(['total_amount' => 1500.00]);

        $matching = ValidateThreeWayMatch::run($invoice, 5.0);

        $this->assertTrue($invoice->fresh()->shouldBlockApproval());
        $this->assertFalse($matching->is_within_tolerance);
    }

    /** @test */
    public function it_generates_matching_report()
    {
        $po = $this->createPurchaseOrder();
        $grn = $this->createGoodsReceivedNote($po);
        $invoice = $this->createSupplierInvoice($po, $grn);

        // Add invoice items
        $poItem = $po->items->first();
        $grnItem = $grn->items->first();

        SupplierInvoiceItem::create([
            'supplier_invoice_id' => $invoice->id,
            'purchase_order_item_id' => $poItem->id,
            'goods_received_note_item_id' => $grnItem->id,
            'item_code' => $poItem->item_code,
            'item_description' => $poItem->item_description,
            'quantity' => 10,
            'unit_price' => 100.00,
            'line_total' => 1000.00,
            'sort_order' => 1,
        ]);

        ValidateThreeWayMatch::run($invoice);

        $report = $invoice->fresh()->getMatchingReport();

        $this->assertIsArray($report);
        $this->assertArrayHasKey('status', $report);
        $this->assertArrayHasKey('is_within_tolerance', $report);
        $this->assertArrayHasKey('mismatches', $report);
        $this->assertArrayHasKey('totals', $report);
        $this->assertArrayHasKey('variances', $report);
    }

    /** @test */
    public function it_can_use_perform_three_way_matching_action()
    {
        $po = $this->createPurchaseOrder();
        $grn = $this->createGoodsReceivedNote($po);
        $invoice = $this->createSupplierInvoice($po, $grn);

        // Add invoice items
        $poItem = $po->items->first();
        $grnItem = $grn->items->first();

        SupplierInvoiceItem::create([
            'supplier_invoice_id' => $invoice->id,
            'purchase_order_item_id' => $poItem->id,
            'goods_received_note_item_id' => $grnItem->id,
            'item_code' => $poItem->item_code,
            'item_description' => $poItem->item_description,
            'quantity' => 10,
            'unit_price' => 100.00,
            'line_total' => 1000.00,
            'sort_order' => 1,
        ]);

        $matching = PerformThreeWayMatching::run($invoice);

        $this->assertNotNull($matching);
        $this->assertEquals(InvoiceMatching::STATUS_MATCHED, $matching->matching_status);
    }

    /** @test */
    public function it_validates_invoice_without_grn_against_po()
    {
        $po = $this->createPurchaseOrder();
        // No GRN created
        $invoice = $this->createSupplierInvoice($po, null);

        // Add invoice items that exceed PO quantity
        $poItem = $po->items->first();

        SupplierInvoiceItem::create([
            'supplier_invoice_id' => $invoice->id,
            'purchase_order_item_id' => $poItem->id,
            'item_code' => $poItem->item_code,
            'item_description' => $poItem->item_description,
            'quantity' => 15, // Exceeds PO quantity
            'unit_price' => 100.00,
            'line_total' => 1500.00,
            'sort_order' => 1,
        ]);

        // Update invoice total
        $invoice->update(['total_amount' => 1500.00]);

        $matching = ValidateThreeWayMatch::run($invoice);

        $this->assertEquals(InvoiceMatching::STATUS_QUANTITY_MISMATCH, $matching->matching_status);
        $this->assertNotEmpty($matching->mismatches);
        $this->assertEquals('quantity_exceeds_po', $matching->mismatches[0]['type']);
    }

    // Helper methods

    protected function createPurchaseOrder(): PurchaseOrder
    {
        $po = PurchaseOrder::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'currency_id' => $this->currency->id,
            'po_number' => 'PO-' . now()->format('Ymd') . '-' . rand(1000, 9999),
            'po_date' => now(),
            'subtotal' => 1000.00,
            'tax_amount' => 100.00,
            'total_amount' => 1100.00,
            'status' => 'issued',
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'item_code' => 'ITEM-001',
            'item_description' => 'Test Item 1',
            'quantity' => 10,
            'uom_id' => null, // No UOM required for testing
            'unit_price' => 100.00,
            'line_total' => 1000.00,
            'sort_order' => 1,
        ]);

        return $po->fresh();
    }

    protected function createGoodsReceivedNote(PurchaseOrder $po): GoodsReceivedNote
    {
        $grn = GoodsReceivedNote::create([
            'company_id' => $this->company->id,
            'purchase_order_id' => $po->id,
            'supplier_id' => $this->supplier->id,
            'grn_number' => 'GRN-' . now()->format('Ymd') . '-' . rand(1000, 9999),
            'received_date' => now(),
            'status' => 'completed',
        ]);

        $poItem = $po->items->first();

        GoodsReceivedNoteItem::create([
            'goods_received_note_id' => $grn->id,
            'purchase_order_item_id' => $poItem->id,
            'item_code' => $poItem->item_code,
            'item_description' => $poItem->item_description,
            'ordered_quantity' => 10,
            'received_quantity' => 10,
            'uom_id' => null, // No UOM required for testing
            'sort_order' => 1,
        ]);

        return $grn->fresh();
    }

    protected function createSupplierInvoice(PurchaseOrder $po, ?GoodsReceivedNote $grn): SupplierInvoice
    {
        return SupplierInvoice::create([
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'purchase_order_id' => $po->id,
            'goods_received_note_id' => $grn?->id,
            'currency_id' => $this->currency->id,
            'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . rand(1000, 9999),
            'supplier_invoice_number' => 'SUP-INV-' . rand(1000, 9999),
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 1000.00,
            'tax_amount' => 100.00,
            'total_amount' => 1100.00,
            'status' => 'draft',
        ]);
    }
}
