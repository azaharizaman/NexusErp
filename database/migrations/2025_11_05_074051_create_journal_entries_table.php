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
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            
            // Serial numbering
            $table->string('journal_entry_number', 50)->unique();
            
            // Company and fiscal period
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('fiscal_year_id')->constrained('fiscal_years')->restrictOnDelete();
            $table->foreignId('accounting_period_id')->constrained('accounting_periods')->restrictOnDelete();
            
            // Entry classification
            $table->enum('entry_type', [
                'manual',           // Manual journal entry
                'automatic',        // Auto-generated (from other modules)
                'opening',          // Opening balances
                'closing',          // Closing entries
                'adjusting',        // Adjusting entries
                'reversing',        // Reversing entries
                'reclassification', // Reclassification entries
                'intercompany',     // Inter-company entries
            ])->default('manual');
            
            // Dates
            $table->date('entry_date');           // Transaction date
            $table->date('posting_date')->nullable(); // Date when posted to GL
            
            // Reference and description
            $table->string('reference_number')->nullable(); // External reference
            $table->text('description');                    // Entry description
            $table->text('notes')->nullable();              // Additional notes
            
            // Status tracking
            $table->enum('status', [
                'draft',
                'submitted',
                'posted',
                'cancelled',
            ])->default('draft');
            
            // Reversal tracking
            $table->boolean('is_reversal')->default(false);
            $table->foreignId('reversed_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->foreignId('reversal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            
            // Inter-company tracking
            $table->boolean('is_intercompany')->default(false);
            $table->foreignId('related_company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('reciprocal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            
            // Source tracking (for auto-generated entries)
            $table->string('source_type')->nullable(); // Morphable type (PurchaseOrder, SalesInvoice, etc.)
            $table->unsignedBigInteger('source_id')->nullable(); // Morphable ID
            
            // Currency
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->restrictOnDelete();
            $table->decimal('exchange_rate', 20, 6)->nullable(); // Exchange rate at transaction time
            
            // Totals (for validation)
            $table->decimal('total_debit', 20, 4)->default(0);
            $table->decimal('total_credit', 20, 4)->default(0);
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['company_id', 'status']);
            $table->index(['fiscal_year_id', 'accounting_period_id']);
            $table->index(['entry_date', 'posting_date']);
            $table->index(['entry_type', 'status']);
            $table->index(['source_type', 'source_id']);
            $table->index('is_reversal');
            $table->index('is_intercompany');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
