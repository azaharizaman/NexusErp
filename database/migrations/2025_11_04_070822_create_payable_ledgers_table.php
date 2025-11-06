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
            $table->foreignId('supplier_invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_voucher_id')->nullable()->constrained()->nullOnDelete();
            
            // Currency tracking
            $table->foreignId('base_currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->foreignId('foreign_currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            
            $table->date('transaction_date');
            $table->string('transaction_type'); // e.g., 'invoice', 'payment', 'credit_note', 'debit_note'
            
            // Amounts in base currency
            $table->decimal('debit_amount_base', 15, 2)->default(0);
            $table->decimal('credit_amount_base', 15, 2)->default(0);
            
            // Amounts in foreign currency
            $table->decimal('debit_amount_foreign', 15, 2)->default(0);
            $table->decimal('credit_amount_foreign', 15, 2)->default(0);
            
            // Exchange rate snapshot
            $table->decimal('exchange_rate', 10, 6)->nullable();
            $table->date('exchange_rate_date')->nullable();
            
            // Running balance
            $table->decimal('balance_base', 15, 2)->default(0);
            $table->decimal('balance_foreign', 15, 2)->default(0);
            
            $table->string('reference_number')->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('transaction_date');
            $table->index(['supplier_id', 'transaction_date']);
            $table->index(['supplier_id', 'transaction_type']);
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
