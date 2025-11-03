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
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->string('pr_number')->unique(); // Serial prefix: PR-
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete(); // Who requested
            $table->unsignedBigInteger('department_id')->nullable(); // Will add FK later when Department model exists
            $table->unsignedBigInteger('company_id')->nullable(); // Will add FK later when needed
            $table->date('request_date');
            $table->date('required_date')->nullable(); // When items are needed
            $table->string('status')->default('draft'); // draft, submitted, approved, rejected, cancelled
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->text('purpose')->nullable(); // Purpose of purchase
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
        });

        // Purchase Request Items (line items)
        Schema::create('purchase_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained()->cascadeOnDelete();
            $table->string('item_description'); // Item name/description
            $table->string('item_code')->nullable(); // Optional item code
            $table->decimal('quantity', 15, 4);
            $table->unsignedBigInteger('uom_id')->nullable(); // Unit of measure - will add FK later
            $table->decimal('estimated_unit_price', 15, 2)->nullable();
            $table->decimal('estimated_total', 15, 2)->nullable();
            $table->text('specifications')->nullable(); // Detailed specifications
            $table->text('notes')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
