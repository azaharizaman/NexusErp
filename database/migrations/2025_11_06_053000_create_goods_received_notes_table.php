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
        Schema::create('goods_received_notes', function (Blueprint $table) {
            $table->id();
            
            // Serial numbering
            $table->string('grn_number', 50)->unique();
            
            // Relationships
            $table->foreignId('company_id')->constrained('backoffice_companies')->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->restrictOnDelete();
            $table->foreignId('supplier_id')->constrained('business_partners')->restrictOnDelete();
            
            // GRN details
            $table->string('warehouse_location')->nullable();
            $table->date('received_date');
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Status
            $table->string('status')->default('draft')->index();
            
            // Additional info
            $table->text('notes')->nullable();
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['company_id', 'status']);
            $table->index(['purchase_order_id']);
            $table->index(['supplier_id']);
            $table->index('received_date');
        });

        Schema::create('goods_received_note_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_received_note_id')->constrained('goods_received_notes')->cascadeOnDelete();
            $table->foreignId('purchase_order_item_id')->nullable()->constrained('purchase_order_items')->nullOnDelete();
            
            $table->string('item_code')->nullable();
            $table->string('item_description');
            
            $table->decimal('ordered_quantity', 15, 3);
            $table->decimal('received_quantity', 15, 3);
            $table->decimal('rejected_quantity', 15, 3)->default(0);
            $table->foreignId('uom_id')->nullable()->constrained('uom_units')->nullOnDelete();
            
            // Batch and serial tracking
            $table->string('batch_number')->nullable();
            $table->json('serial_numbers')->nullable();
            $table->date('expiry_date')->nullable();
            
            $table->text('notes')->nullable();
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            
            $table->index(['goods_received_note_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_received_note_items');
        Schema::dropIfExists('goods_received_notes');
    }
};
