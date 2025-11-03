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
        // Add standard fields to request_for_quotations
        Schema::table('request_for_quotations', function (Blueprint $table) {
            $table->foreignId('requested_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->after('updated_by')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('remarks')->nullable()->after('notes');
        });

        // Add standard fields to quotations
        Schema::table('quotations', function (Blueprint $table) {
            $table->foreignId('requested_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->after('updated_by')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('remarks')->nullable()->after('notes');
        });

        // Add standard fields to purchase_recommendations
        Schema::table('purchase_recommendations', function (Blueprint $table) {
            $table->foreignId('requested_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable()->after('comparison_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_for_quotations', function (Blueprint $table) {
            $table->dropForeign(['requested_by']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['requested_by', 'approved_by', 'approved_at', 'remarks']);
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->dropForeign(['requested_by']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['requested_by', 'approved_by', 'approved_at', 'remarks']);
        });

        Schema::table('purchase_recommendations', function (Blueprint $table) {
            $table->dropForeign(['requested_by']);
            $table->dropColumn(['requested_by', 'remarks']);
        });
    }
};
