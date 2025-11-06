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
            $table->foreignId('supplier_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_order_item_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('goods_received_note_item_id')->nullable(); // FK will be added when GRN table exists
            
            $table->string('item_code')->nullable();
            $table->text('item_description');
            $table->decimal('quantity', 15, 3);
            $table->foreignId('uom_id')->nullable()->constrained('uoms')->nullOnDelete();
            
            $table->decimal('unit_price', 15, 2);
            $table->decimal('line_total', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            
            $table->text('notes')->nullable();
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            
            // Index for sorting
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
