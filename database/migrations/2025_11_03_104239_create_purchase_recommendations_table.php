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
        Schema::create('purchase_recommendations', function (Blueprint $table) {
            $table->id();
            $table->string('recommendation_number')->unique(); // Serial prefix: PR-REC-
            $table->foreignId('request_for_quotation_id')->constrained('request_for_quotations')->cascadeOnDelete();
            $table->foreignId('recommended_quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->date('recommendation_date');
            $table->string('status')->default('draft'); // draft, submitted, approved, rejected
            $table->text('justification')->nullable(); // Why this supplier was recommended
            $table->text('comparison_notes')->nullable(); // Notes from comparison
            $table->decimal('recommended_total', 15, 2)->default(0);
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_recommendations');
    }
};
