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
        Schema::create('payment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('backoffice_companies')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('business_partners')->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            // Future: supplier_invoice_id when Phase 5 is implemented
            $table->foreignId('currency_id')->constrained('currencies')->cascadeOnDelete();
            
            $table->string('milestone_description')->nullable();
            $table->date('due_date');
            $table->decimal('scheduled_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2);
            
            $table->string('status')->default('scheduled'); // scheduled, overdue, partially_paid, paid, cancelled
            $table->integer('reminder_days_before')->default(7);
            $table->boolean('reminder_sent')->default(false);
            $table->timestamp('reminder_sent_at')->nullable();
            
            $table->text('notes')->nullable();
            
            // Payment voucher link
            $table->foreignId('payment_voucher_id')->nullable()->constrained('payment_vouchers')->nullOnDelete();
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes
            $table->index(['company_id', 'status']);
            $table->index(['supplier_id', 'due_date']);
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_schedules');
    }
};
