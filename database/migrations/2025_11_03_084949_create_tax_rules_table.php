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
        Schema::create('tax_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tax rule name (e.g., "Standard VAT", "Sales Tax 6%")
            $table->string('code', 50)->unique(); // Short code for the tax rule
            $table->decimal('rate', 8, 4); // Tax rate percentage (e.g., 6.0000 for 6%)
            $table->enum('type', ['percentage', 'fixed'])->default('percentage');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_compound')->default(false); // Whether this tax is calculated after other taxes
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
        Schema::dropIfExists('tax_rules');
    }
};
