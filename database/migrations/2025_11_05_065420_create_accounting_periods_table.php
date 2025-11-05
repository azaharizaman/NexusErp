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
        Schema::create('accounting_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiscal_year_id')->constrained('fiscal_years')->onDelete('cascade');
            $table->string('period_name'); // e.g., "January 2025", "Q1 2025"
            $table->string('period_code'); // e.g., "JAN2025", "Q12025"
            $table->enum('period_type', ['monthly', 'quarterly', 'half-yearly', 'yearly'])->default('monthly');
            $table->integer('period_number'); // 1-12 for monthly, 1-4 for quarterly
            $table->date('start_date');
            $table->date('end_date');
            
            // Status
            $table->enum('status', ['open', 'closed', 'locked'])->default('open');
            $table->boolean('is_adjusting_period')->default(false); // For year-end adjustments
            
            // Closure information
            $table->date('closed_on')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['fiscal_year_id', 'status']);
            $table->index(['start_date', 'end_date']);
            $table->unique(['fiscal_year_id', 'period_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounting_periods');
    }
};
