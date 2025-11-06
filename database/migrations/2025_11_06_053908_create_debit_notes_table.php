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
        Schema::create('debit_notes', function (Blueprint $table) {
            $table->id();
            
            // Serial numbering
            $table->string('debit_note_number', 50)->unique();
            
            // Company and supplier
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('business_partners')->restrictOnDelete();
            
            // Related invoice
            $table->foreignId('supplier_invoice_id')->nullable()->constrained('supplier_invoices')->nullOnDelete();
            
            // Debit note details
            $table->date('debit_note_date');
            $table->string('reason')->nullable(); // return, price_adjustment, error_correction, etc.
            
            // Currency
            $table->foreignId('currency_id')->constrained('currencies')->restrictOnDelete();
            $table->decimal('exchange_rate', 20, 6)->default(1);
            
            // Amount
            $table->decimal('amount', 20, 4);
            
            // Status - using Spatie ModelStatus
            $table->string('status', 50)->default('draft');
            
            // GL integration
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->boolean('is_posted_to_gl')->default(false);
            $table->timestamp('posted_to_gl_at')->nullable();
            
            // Additional info
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['company_id', 'status']);
            $table->index(['supplier_id', 'status']);
            $table->index('debit_note_date');
            $table->index('is_posted_to_gl');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debit_notes');
    }
};
