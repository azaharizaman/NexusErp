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
        Schema::create('fiscal_years', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "FY 2025", "FY 2025-2026"
            $table->string('code')->unique(); // e.g., "FY2025"
            $table->date('start_date');
            $table->date('end_date');
            $table->foreignId('company_id')->constrained('backoffice_companies')->onDelete('cascade');
            
            // Status
            $table->enum('status', ['draft', 'active', 'closed'])->default('draft');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_locked')->default(false);
            
            // Closure information
            $table->date('closed_on')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['company_id', 'status']);
            $table->index(['start_date', 'end_date']);
            $table->unique(['company_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiscal_years');
    }
};
