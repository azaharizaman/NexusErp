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
        Schema::create('purchase_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->unique()->index();
            $table->foreignId('company_id')->constrained('backoffice_companies')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('business_partners')->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained('currencies')->cascadeOnDelete();

            $table->string('contract_type')->default('blanket'); // blanket, framework, long_term
            $table->string('contract_name');
            $table->text('description')->nullable();

            $table->date('start_date');
            $table->date('end_date');
            $table->date('renewal_date')->nullable();

            $table->decimal('contract_value', 15, 2)->nullable();
            $table->decimal('utilized_value', 15, 2)->default(0);
            $table->decimal('remaining_value', 15, 2)->nullable();

            $table->string('status')->default('draft')->index(); // draft, active, expired, terminated
            $table->text('terms_and_conditions')->nullable();
            $table->text('notes')->nullable();

            // Approval workflow
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['supplier_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });

        // Link purchase orders to contracts
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('purchase_contract_id')->nullable()->after('purchase_recommendation_id')->constrained('purchase_contracts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['purchase_contract_id']);
            $table->dropColumn('purchase_contract_id');
        });

        Schema::dropIfExists('purchase_contracts');
    }
};
