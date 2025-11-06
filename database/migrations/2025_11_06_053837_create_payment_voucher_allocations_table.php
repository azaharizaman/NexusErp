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
        Schema::create('payment_voucher_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_voucher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_invoice_id')->constrained()->cascadeOnDelete();
            $table->decimal('allocated_amount', 15, 4);
            
            $table->timestamps();
            
            // Unique constraint to prevent duplicate allocations
            $table->unique(['payment_voucher_id', 'supplier_invoice_id'], 'pv_si_unique');
            
            // Index for queries
            $table->index('payment_voucher_id');
            $table->index('supplier_invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_voucher_allocations');
    }
};
