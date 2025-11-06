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
        Schema::create('supplier_invoices', function (Blueprint $table) {
            $table->id();
            
            // Serial numbering
            $table->string('invoice_number', 50)->unique();
            
            // Relationships
            $table->foreignId('company_id')->constrained('backoffice_companies')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('business_partners')->restrictOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->foreignId('goods_received_note_id')->nullable()->constrained('goods_received_notes')->nullOnDelete();
            $table->foreignId('currency_id')->constrained('currencies')->restrictOnDelete();
            
            // Invoice details
            $table->string('supplier_invoice_number')->nullable();
            $table->date('invoice_date');
            $table->date('due_date');
            
            // Status
            $table->string('status')->default('draft')->index();
            
            // Amounts
            $table->decimal('subtotal', 20, 4)->default(0);
            $table->decimal('tax_amount', 20, 4)->default(0);
            $table->decimal('discount_amount', 20, 4)->default(0);
            $table->decimal('total_amount', 20, 4)->default(0);
            $table->decimal('paid_amount', 20, 4)->default(0);
            $table->decimal('outstanding_amount', 20, 4)->default(0);
            
            // Additional info
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();
            
            // Approval workflow
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['company_id', 'status']);
            $table->index(['supplier_id', 'status']);
            $table->index(['purchase_order_id']);
            $table->index(['goods_received_note_id']);
            $table->index(['invoice_date', 'due_date']);
        });

        Schema::create('supplier_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_invoice_id')->constrained('supplier_invoices')->cascadeOnDelete();
            $table->foreignId('purchase_order_item_id')->nullable()->constrained('purchase_order_items')->nullOnDelete();
            $table->foreignId('goods_received_note_item_id')->nullable()->constrained('goods_received_note_items')->nullOnDelete();
            
            $table->string('item_code')->nullable();
            $table->string('item_description');
            
            $table->decimal('quantity', 15, 3);
            $table->foreignId('uom_id')->nullable()->constrained('uom_units')->nullOnDelete();
            
            $table->decimal('unit_price', 15, 2);
            $table->decimal('line_total', 15, 2);
            
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            
            $table->text('notes')->nullable();
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            
            $table->index(['supplier_invoice_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_invoice_items');
        Schema::dropIfExists('supplier_invoices');
    }
};
