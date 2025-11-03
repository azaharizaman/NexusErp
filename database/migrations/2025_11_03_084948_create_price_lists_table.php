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
        Schema::create('price_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Price list name (e.g., "Standard Supplier Prices", "Preferred Vendor Rates")
            $table->foreignId('currency_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('supplier_id')->nullable(); // FK will be added after business_partners migration
            $table->date('effective_from'); // Start date for this price list
            $table->date('effective_to')->nullable(); // Optional end date
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_lists');
    }
};
