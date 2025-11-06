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
            $table->string('voucher_number')->unique();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('business_partners')->cascadeOnDelete();
            // Foreign key constraint commented out until supplier_invoices table is created
            // TODO: Uncomment when supplier_invoices migration is added
            // $table->foreignId('supplier_invoice_id')->nullable()->constrained('supplier_invoices')->nullOnDelete();
            $table->unsignedBigInteger('supplier_invoice_id')->nullable();
            $table->foreignId('currency_id')->constrained()->cascadeOnDelete();
            
            $table->date('payment_date');
            $table->enum('payment_method', [
                'cash',
                'bank_transfer',
                'credit_card',
                'debit_card',
                'cheque',
                'online',
                'other',
            ])->nullable();
            $table->string('reference_number')->nullable();
            $table->decimal('amount', 20, 4);
            $table->decimal('exchange_rate', 20, 6)->default(1);
            
            // Allocation tracking (similar to PaymentReceipt)
            $table->decimal('allocated_amount', 20, 4)->default(0);
            $table->decimal('unallocated_amount', 20, 4)->default(0);
            
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();
            
            // Payment details
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('cheque_number')->nullable();
            $table->date('cheque_date')->nullable();
            $table->string('transaction_id')->nullable();
            
            // On hold flag
            $table->boolean('is_on_hold')->default(false);
            
            // GL integration
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->boolean('is_posted_to_gl')->default(false);
            $table->timestamp('posted_to_gl_at')->nullable();
            
            // Workflow tracking
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->text('void_reason')->nullable();
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('payment_date');
            $table->index(['supplier_id', 'payment_date']);
            $table->index('is_posted_to_gl');
            $table->index('payment_method');
            $table->index('is_on_hold');
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
