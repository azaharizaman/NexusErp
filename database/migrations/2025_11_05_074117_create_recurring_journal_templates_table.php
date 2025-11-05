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
        Schema::create('recurring_journal_templates', function (Blueprint $table) {
            $table->id();
            
            // Template identification
            $table->string('template_name');
            $table->string('template_code', 50)->unique();
            $table->text('description')->nullable();
            
            // Company
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            
            // Entry type
            $table->enum('entry_type', [
                'manual',
                'automatic',
                'adjusting',
                'reclassification',
            ])->default('manual');
            
            // Recurrence settings
            $table->enum('frequency', [
                'daily',
                'weekly',
                'biweekly',
                'monthly',
                'quarterly',
                'half-yearly',
                'yearly',
            ]);
            
            // Start and end dates
            $table->date('start_date');
            $table->date('end_date')->nullable();
            
            // Occurrence limits
            $table->unsignedInteger('max_occurrences')->nullable(); // Max number of times to generate
            $table->unsignedInteger('occurrences_count')->default(0); // Actual occurrences generated
            
            // Next generation
            $table->date('next_generation_date')->nullable();
            $table->date('last_generated_date')->nullable();
            
            // Template lines (JSON structure for the entry lines)
            $table->json('template_lines'); // Array of line items with account_id, debit, credit, description
            
            // Reference and notes
            $table->string('reference_prefix')->nullable(); // Prefix for auto-generated reference numbers
            $table->text('notes')->nullable();
            
            // Status
            $table->boolean('is_active')->default(true);
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['company_id', 'is_active']);
            $table->index('frequency');
            $table->index('next_generation_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_journal_templates');
    }
};
