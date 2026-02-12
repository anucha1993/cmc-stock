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
        // ลบฟิลด์ warehouse_id และ customer_address ออกจาก delivery_notes
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn(['warehouse_id', 'customer_address']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // คืนค่าฟิลด์กลับมา
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('status')->constrained('warehouses');
            $table->text('customer_address')->nullable()->after('customer_name');
        });
    }
};
