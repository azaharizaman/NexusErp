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
        Schema::create('purchase_order_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->string('revision_number')->index();
            $table->string('revision_type')->default('amendment'); // amendment, correction, cancellation

            $table->text('reason')->nullable();
            $table->text('description')->nullable();
            $table->json('changes')->nullable(); // Store old vs new values

            $table->foreignId('revised_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revised_at')->useCurrent();

            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->string('status')->default('draft')->index(); // draft, approved, rejected

            $table->timestamps();

            $table->index(['purchase_order_id', 'revision_number']);
            $table->index('revised_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_revisions');
    }
};
