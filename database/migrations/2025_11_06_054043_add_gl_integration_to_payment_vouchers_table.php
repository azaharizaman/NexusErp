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
            // GL integration fields
            $table->foreignId('journal_entry_id')->nullable()->after('void_reason')->constrained('journal_entries')->nullOnDelete();
            $table->boolean('is_posted_to_gl')->default(false)->after('journal_entry_id');
            $table->timestamp('posted_to_gl_at')->nullable()->after('is_posted_to_gl');
            
            // Index for GL posting queries
            $table->index('is_posted_to_gl');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_vouchers', function (Blueprint $table) {
            $table->dropIndex(['is_posted_to_gl']);
            $table->dropForeign(['journal_entry_id']);
            $table->dropColumn(['journal_entry_id', 'is_posted_to_gl', 'posted_to_gl_at']);
        });
    }
};
