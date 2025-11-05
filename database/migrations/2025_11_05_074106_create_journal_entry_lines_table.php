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
        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();
            
            // Parent journal entry
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
            
            // Account
            $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete();
            
            // Debit and Credit amounts
            $table->decimal('debit', 20, 4)->default(0);
            $table->decimal('credit', 20, 4)->default(0);
            
            // Foreign currency amounts (if multi-currency)
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->restrictOnDelete();
            $table->decimal('exchange_rate', 20, 6)->nullable();
            $table->decimal('foreign_debit', 20, 4)->nullable();
            $table->decimal('foreign_credit', 20, 4)->nullable();
            
            // Dimensions for analytics
            $table->foreignId('cost_center_id')->nullable()->constrained('cost_centers')->nullOnDelete();
            $table->foreignId('department_id')->nullable(); // When Department model exists
            $table->foreignId('project_id')->nullable();    // When Project model exists
            
            // Description
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            
            // Sort order
            $table->unsignedInteger('sort_order')->default(0);
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['journal_entry_id', 'sort_order']);
            $table->index('account_id');
            $table->index('cost_center_id');
            $table->index(['department_id', 'project_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
    }
};
