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
        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->id();
            
            // Serial numbering
            $table->string('invoice_number', 50)->unique();
            
            // Customer and company
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('business_partners')->restrictOnDelete();
            
            // Related documents
            $table->foreignId('sales_order_id')->nullable(); // When sales order module exists
            $table->foreignId('fiscal_year_id')->constrained('fiscal_years')->restrictOnDelete();
            $table->foreignId('accounting_period_id')->constrained('accounting_periods')->restrictOnDelete();
            
            // Invoice details
            $table->date('invoice_date');
            $table->date('due_date');
            $table->string('payment_terms')->nullable();
            $table->unsignedInteger('credit_days')->default(0);
            
            // Currency
            $table->foreignId('currency_id')->constrained('currencies')->restrictOnDelete();
            $table->decimal('exchange_rate', 20, 6)->default(1);
            
            // Amounts
            $table->decimal('subtotal', 20, 4)->default(0);
            $table->decimal('discount_amount', 20, 4)->default(0);
            $table->decimal('tax_amount', 20, 4)->default(0);
            $table->decimal('total_amount', 20, 4)->default(0);
            $table->decimal('paid_amount', 20, 4)->default(0);
            $table->decimal('outstanding_amount', 20, 4)->default(0);
            
            // Status
            $table->enum('status', [
                'draft',
                'issued',
                'partially_paid',
                'paid',
                'overdue',
                'cancelled',
            ])->default('draft');
            
            // GL integration
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->boolean('is_posted_to_gl')->default(false);
            $table->timestamp('posted_to_gl_at')->nullable();
            
            // Additional info
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->text('terms_and_conditions')->nullable();
            
            // Addresses
            $table->text('billing_address')->nullable();
            $table->text('shipping_address')->nullable();
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('issued_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['company_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['invoice_date', 'due_date']);
            $table->index('is_posted_to_gl');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_invoices');
    }
};
