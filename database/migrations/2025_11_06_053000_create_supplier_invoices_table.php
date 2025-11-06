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
            
            // Company and supplier
            $table->foreignId('company_id')->constrained('backoffice_companies')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('business_partners')->restrictOnDelete();
            
            // Related documents (nullable - migrations may not exist yet)
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->unsignedBigInteger('goods_received_note_id')->nullable();
            
            // Currency
            $table->foreignId('currency_id')->constrained('currencies')->restrictOnDelete();
            
            // Invoice details
            $table->string('supplier_invoice_number', 100);
            $table->date('invoice_date');
            $table->date('due_date');
            
            // Amounts
            $table->decimal('subtotal', 20, 2)->default(0);
            $table->decimal('tax_amount', 20, 2)->default(0);
            $table->decimal('discount_amount', 20, 2)->default(0);
            $table->decimal('total_amount', 20, 2)->default(0);
            $table->decimal('paid_amount', 20, 2)->default(0);
            $table->decimal('outstanding_amount', 20, 2)->default(0);
            
            // Additional info
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();
            
            // Audit fields
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['company_id', 'supplier_id']);
            $table->index('invoice_date');
            $table->index('due_date');
            $table->index(['supplier_id', 'invoice_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_invoices');
    }
};
