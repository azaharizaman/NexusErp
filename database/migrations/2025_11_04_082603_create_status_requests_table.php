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
        Schema::create('status_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_model_id')->constrained()->cascadeOnDelete();
            $table->morphs('model');
            $table->foreignId('current_status_id')->constrained('model_statuses');
            $table->foreignId('requested_status_id')->constrained('model_statuses');
            $table->json('approvers')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_requests');
    }
};
