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
        Schema::create('status_transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('status_from_id')->constrained('model_statuses')->cascadeOnDelete();
            $table->foreignId('status_to_id')->constrained('model_statuses')->cascadeOnDelete();
            $table->json('condition')->nullable();
            $table->timestamps();

            $table->unique(['status_from_id', 'status_to_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_transitions');
    }
};
