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
        Schema::create('delivery_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('purchase_order_item_id')->nullable()->constrained('purchase_order_items')->cascadeOnDelete();
            
            $table->string('schedule_number')->unique()->index();
            $table->date('scheduled_date');
            $table->date('expected_date')->nullable();
            $table->date('actual_delivery_date')->nullable();
            
            $table->decimal('scheduled_quantity', 15, 3);
            $table->decimal('delivered_quantity', 15, 3)->default(0);
            $table->decimal('remaining_quantity', 15, 3);
            
            $table->string('status')->default('scheduled')->index(); // scheduled, confirmed, in_transit, delivered, delayed, cancelled
            $table->string('delivery_location')->nullable();
            $table->string('tracking_number')->nullable();
            
            $table->text('notes')->nullable();
            $table->text('delivery_instructions')->nullable();
            
            // Notification settings
            $table->integer('reminder_days_before')->default(3);
            $table->timestamp('reminder_sent_at')->nullable();
            
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            
            $table->index(['purchase_order_id', 'scheduled_date']);
            $table->index(['status', 'scheduled_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_schedules');
    }
};
