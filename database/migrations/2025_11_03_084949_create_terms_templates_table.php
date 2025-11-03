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
        Schema::create('terms_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Template name (e.g., "Standard Purchase Terms", "International Supplier Terms")
            $table->string('code', 50)->unique(); // Short code for reference
            $table->text('content'); // Full terms and conditions text (supports rich text/HTML)
            $table->enum('category', ['purchase', 'contract', 'delivery', 'payment', 'general'])->default('general');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false); // Whether this is the default template for its category
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
        Schema::dropIfExists('terms_templates');
    }
};
