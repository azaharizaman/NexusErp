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
        Schema::create('request_for_quotations', function (Blueprint $table) {
            $table->id();
            $table->string('rfq_number')->unique(); // Serial prefix: RFQ-
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->date('rfq_date');
            $table->date('expiry_date')->nullable(); // Deadline for suppliers to respond
            $table->string('status')->default('draft'); // draft, sent, received, evaluated, closed, cancelled
            $table->text('description')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });

        // Pivot table for RFQ and Purchase Requests (many-to-many)
        Schema::create('purchase_request_rfq', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_for_quotation_id')->constrained('request_for_quotations')->cascadeOnDelete();
            $table->foreignId('purchase_request_id')->constrained('purchase_requests')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['request_for_quotation_id', 'purchase_request_id'], 'rfq_pr_unique');
        });

        // RFQ Suppliers (invited suppliers for this RFQ)
        Schema::create('rfq_suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_for_quotation_id')->constrained('request_for_quotations')->cascadeOnDelete();
            $table->foreignId('business_partner_id')->constrained('business_partners')->cascadeOnDelete();
            $table->string('status')->default('invited'); // invited, declined, submitted
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['request_for_quotation_id', 'business_partner_id'], 'rfq_supplier_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfq_suppliers');
        Schema::dropIfExists('purchase_request_rfq');
        Schema::dropIfExists('request_for_quotations');
    }
};
