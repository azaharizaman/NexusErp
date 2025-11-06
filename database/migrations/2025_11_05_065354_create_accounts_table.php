<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    // Define constants for balance precision and scale
    private const BALANCE_PRECISION = 20;
    private const BALANCE_SCALE = 4;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_code')->unique();
            $table->string('account_name');
            $table->text('description')->nullable();
            $table->enum('account_type', ['Asset', 'Liability', 'Equity', 'Income', 'Expense']);
            $table->enum('sub_type', [
                // Asset subtypes
                'Current Asset', 'Fixed Asset', 'Intangible Asset', 'Investment',
                // Liability subtypes
                'Current Liability', 'Long-term Liability',
                // Equity subtypes
                'Capital', 'Retained Earnings', 'Drawings',
                // Income subtypes
                'Operating Revenue', 'Other Income',
                // Expense subtypes
                'Cost of Goods Sold', 'Operating Expense', 'Other Expense'
            ])->nullable();
            $table->foreignId('account_group_id')->nullable()->constrained('account_groups')->onDelete('set null');
            $table->foreignId('parent_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->foreignId('company_id')->constrained('backoffice_companies')->onDelete('cascade');
            
            // Account properties
            $table->boolean('is_group')->default(false); // For hierarchical grouping
            $table->boolean('is_control_account')->default(false); // For AR, AP control accounts
            $table->boolean('allow_manual_entries')->default(true);
            $table->boolean('is_active')->default(true);
            $table->integer('level')->default(0); // Hierarchy level
            $table->integer('sort_order')->default(0);
            
            // Balance tracking
            $table->decimal('opening_balance', self::BALANCE_PRECISION, self::BALANCE_SCALE)->default(0);
            $table->decimal('current_balance', self::BALANCE_PRECISION, self::BALANCE_SCALE)->default(0);
            $table->enum('balance_type', ['Debit', 'Credit']);
            
            // Currency support
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->onDelete('set null');
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['account_type', 'is_active']);
            // Removed redundant compound index on ['company_id', 'account_code'] since 'account_code' is already unique.
            $table->index('parent_account_id');
            $table->index('account_group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
