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
        // ลบฟิลด์ warehouse_id ออกจาก delivery_notes
        // เพราะสินค้าแต่ละ SN/Barcode มีคลังเชื่อมโยงอยู่แล้ว
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // คืนค่าฟิลด์ warehouse_id กลับมา
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->after('status')->constrained('warehouses');
        });
    }
};
