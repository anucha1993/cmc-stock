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
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->string('quotation_number')->nullable()->after('sales_order_number');
            $table->foreignId('approved_by')->nullable()->after('scanned_at')->constrained('users');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->json('discrepancy_notes')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['quotation_number', 'approved_by', 'approved_at', 'discrepancy_notes']);
        });
    }
};
