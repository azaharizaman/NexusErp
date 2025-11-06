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
            $table->string('invoice_number')->unique();
            $table->foreignId('company_id')->constrained('backoffice_companies')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('business_partners')->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('goods_received_note_id')->nullable(); // FK will be added when GRN table exists
            $table->foreignId('currency_id')->constrained()->cascadeOnDelete();
            
            $table->string('supplier_invoice_number')->nullable();
            $table->date('invoice_date');
            $table->date('due_date');
            $table->string('status')->nullable(); // Legacy field for compatibility
            
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('outstanding_amount', 15, 2)->default(0);
            
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();
            
            // Approval tracking
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('invoice_date');
            $table->index(['supplier_id', 'invoice_date']);
            $table->index('due_date');
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
