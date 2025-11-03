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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique(); // ISO 4217 currency code (e.g., USD, EUR, MYR)
            $table->string('name'); // Full currency name (e.g., US Dollar, Euro, Malaysian Ringgit)
            $table->string('symbol', 10)->nullable(); // Currency symbol (e.g., $, €, RM)
            $table->unsignedTinyInteger('decimal_places')->default(2); // Number of decimal places
            $table->boolean('is_active')->default(true); // Whether currency is active for transactions
            $table->boolean('is_base')->default(false); // Whether this is the base currency for the system
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
        Schema::dropIfExists('currencies');
    }
};
