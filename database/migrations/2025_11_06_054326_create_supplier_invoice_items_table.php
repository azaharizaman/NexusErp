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
            
            // Item details
            $table->string('item_code')->nullable();
            $table->text('item_description');
            $table->text('specifications')->nullable();
            
            // Quantity and pricing
            $table->decimal('quantity', 20, 4);
            $table->foreignId('uom_id')->nullable()->constrained('uoms')->restrictOnDelete();
            $table->decimal('unit_price', 20, 4);
            $table->decimal('line_total', 20, 4);
            
            // Discounts
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 20, 4)->default(0);
            
            // Tax
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 20, 4)->default(0);
            
            // GL account for expense recognition
            $table->foreignId('expense_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            
            // Notes
            $table->text('notes')->nullable();
            
            // Sort order
            $table->unsignedInteger('sort_order')->default(0);
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            
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
