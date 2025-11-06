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
        Schema::create('invoice_matchings', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->foreignId('company_id')->constrained('backoffice_companies')->cascadeOnDelete();
            $table->foreignId('supplier_invoice_id')->constrained('supplier_invoices')->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->restrictOnDelete();
            $table->foreignId('goods_received_note_id')->nullable()->constrained('goods_received_notes')->nullOnDelete();
            
            // Matching status
            $table->enum('matching_status', [
                'matched',
                'quantity_mismatch',
                'price_mismatch',
                'not_matched',
            ])->default('not_matched');
            
            // Totals for comparison
            $table->decimal('po_total', 20, 4)->default(0);
            $table->decimal('grn_total', 20, 4)->nullable();
            $table->decimal('invoice_total', 20, 4)->default(0);
            
            // Variances
            $table->decimal('quantity_variance', 15, 3)->default(0);
            $table->decimal('price_variance', 20, 4)->default(0);
            $table->decimal('total_variance', 20, 4)->default(0);
            $table->decimal('variance_percentage', 5, 2)->default(0);
            
            // Tolerance
            $table->boolean('is_within_tolerance')->default(false);
            $table->decimal('tolerance_percentage', 5, 2)->default(5.00);
            
            // Mismatch details (JSON)
            $table->json('mismatches')->nullable();
            
            // Additional info
            $table->text('notes')->nullable();
            
            // Approval
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['company_id', 'matching_status']);
            $table->index(['supplier_invoice_id']);
            $table->index(['purchase_order_id']);
            $table->index(['goods_received_note_id']);
            $table->unique(['supplier_invoice_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_matchings');
    }
};
