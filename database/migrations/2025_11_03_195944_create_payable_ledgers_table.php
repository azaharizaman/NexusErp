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
        Schema::create('payable_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('backoffice_companies')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('business_partners')->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->foreignId('base_currency_id')->constrained('currencies')->cascadeOnDelete();
            
            // Reference documents
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            // Future: supplier_invoice_id when Phase 5 is implemented
            $table->foreignId('payment_voucher_id')->nullable()->constrained('payment_vouchers')->nullOnDelete();
            
            $table->date('transaction_date');
            $table->string('transaction_type'); // invoice, payment, credit_note, debit_note, adjustment
            $table->string('reference_number')->nullable();
            
            // Original currency amounts
            $table->decimal('debit_amount', 15, 2)->default(0);
            $table->decimal('credit_amount', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);
            
            // Base currency amounts
            $table->decimal('exchange_rate', 15, 6)->default(1.000000);
            $table->decimal('base_debit_amount', 15, 2)->default(0);
            $table->decimal('base_credit_amount', 15, 2)->default(0);
            $table->decimal('base_balance', 15, 2)->default(0);
            
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes
            $table->index(['company_id', 'supplier_id']);
            $table->index(['supplier_id', 'transaction_date']);
            $table->index('transaction_date');
            $table->index('transaction_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payable_ledgers');
    }
};
