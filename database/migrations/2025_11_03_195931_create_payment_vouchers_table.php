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
        Schema::create('payment_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number')->unique()->index();
            $table->foreignId('company_id')->constrained('backoffice_companies')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('business_partners')->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            // Future: supplier_invoice_id when Phase 5 is implemented
            $table->foreignId('currency_id')->constrained('currencies')->cascadeOnDelete();
            
            $table->date('payment_date');
            $table->date('value_date')->nullable();
            
            $table->string('payment_method'); // bank_transfer, check, cash, credit_card, etc.
            $table->string('payment_reference')->nullable(); // Check number, transaction ID, etc.
            $table->string('bank_account')->nullable();
            
            $table->decimal('amount', 15, 2);
            $table->decimal('exchange_rate', 15, 6)->default(1.000000);
            $table->decimal('base_amount', 15, 2); // Amount in base currency
            
            $table->string('status')->default('draft')->index(); // draft, pending_approval, approved, paid, cancelled
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();
            
            // Approval workflow
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes
            $table->index(['company_id', 'status']);
            $table->index(['supplier_id', 'status']);
            $table->index('payment_date');
            $table->index('value_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_vouchers');
    }
};
