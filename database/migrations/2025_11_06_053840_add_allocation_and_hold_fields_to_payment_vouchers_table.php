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
        Schema::table('payment_vouchers', function (Blueprint $table) {
            // Add allocation tracking fields
            $table->decimal('allocated_amount', 20, 4)->default(0)->after('amount');
            $table->decimal('unallocated_amount', 20, 4)->default(0)->after('allocated_amount');
            
            // Add hold fields
            $table->boolean('is_on_hold')->default(false)->after('unallocated_amount');
            $table->text('hold_reason')->nullable()->after('is_on_hold');
            $table->foreignId('held_by')->nullable()->constrained('users')->nullOnDelete()->after('hold_reason');
            $table->timestamp('held_at')->nullable()->after('held_by');
            
            // Add index for querying on-hold vouchers
            $table->index('is_on_hold');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_vouchers', function (Blueprint $table) {
            $table->dropIndex(['is_on_hold']);
            $table->dropForeign(['held_by']);
            $table->dropColumn([
                'allocated_amount',
                'unallocated_amount',
                'is_on_hold',
                'hold_reason',
                'held_by',
                'held_at',
            ]);
        });
    }
};
