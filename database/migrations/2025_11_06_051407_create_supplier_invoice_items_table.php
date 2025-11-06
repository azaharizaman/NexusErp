<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('supplier_invoice_items', function (Blueprint $table) {
            $table->id();
            
            // Parent invoice
            $table->foreignId('supplier_invoice_id')->constrained('supplier_invoices')->cascadeOnDelete();
            
            // Related documents
            $table->foreignId('purchase_order_item_id')->nullable()->constrained('purchase_order_items')->nullOnDelete();
            $table->foreignId('goods_received_note_item_id')->nullable(); // Constraint will be added when GRN items table is created
            
            // Item details
            $table->string('item_code')->nullable();
            $table->text('item_description');
            
            // Quantity and pricing
            $table->decimal('quantity', 20, 3);
            $table->foreignId('uom_id')->nullable()->constrained('uoms')->restrictOnDelete();
            $table->decimal('unit_price', 20, 2);
            $table->decimal('line_total', 20, 2);
            
            // Discounts
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 20, 2)->default(0);
            
            // Tax
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 20, 2)->default(0);
            
            // Notes
            $table->text('notes')->nullable();
            
            // Sort order (for Spatie Sortable)
            $table->unsignedInteger('sort_order')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['supplier_invoice_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_invoice_items');
    }
};
