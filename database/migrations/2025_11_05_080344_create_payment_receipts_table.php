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
        Schema::create('payment_receipts', function (Blueprint $table) {
            $table->id();
            
            // Serial numbering
            $table->string('receipt_number', 50)->unique();
            
            // Customer and company
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('business_partners')->restrictOnDelete();
            
            // Payment details
            $table->date('payment_date');
            $table->enum('payment_method', [
                'cash',
                'bank_transfer',
                'credit_card',
                'debit_card',
                'cheque',
                'online',
                'other',
            ]);
            
            // Reference details
            $table->string('reference_number')->nullable(); // Bank reference, cheque number, etc.
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('cheque_number')->nullable();
            $table->date('cheque_date')->nullable();
            $table->string('transaction_id')->nullable(); // For online payments
            
            // Currency
            $table->foreignId('currency_id')->constrained('currencies')->restrictOnDelete();
            $table->decimal('exchange_rate', 20, 6)->default(1);
            
            // Amount
            $table->decimal('amount', 20, 4);
            $table->decimal('allocated_amount', 20, 4)->default(0);
            $table->decimal('unallocated_amount', 20, 4)->default(0);
            
            // GL integration
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->boolean('is_posted_to_gl')->default(false);
            $table->timestamp('posted_to_gl_at')->nullable();
            
            // Status
            $table->enum('status', [
                'draft',
                'cleared',
                'bounced',
                'cancelled',
            ])->default('draft');
            
            // Additional info
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['company_id', 'status']);
            $table->index(['customer_id', 'payment_date']);
            $table->index('is_posted_to_gl');
            $table->index('payment_method');
        });
        
        // Payment allocations table - links payments to invoices
        Schema::create('payment_receipt_allocations', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('payment_receipt_id')->constrained('payment_receipts')->cascadeOnDelete();
            $table->foreignId('sales_invoice_id')->constrained('sales_invoices')->cascadeOnDelete();
            
            $table->decimal('allocated_amount', 20, 4);
            $table->text('notes')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            
            // Indexes
            $table->index('payment_receipt_id');
            $table->index('sales_invoice_id');
            $table->unique(['payment_receipt_id', 'sales_invoice_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_receipt_allocations');
        Schema::dropIfExists('payment_receipts');
    }
};
