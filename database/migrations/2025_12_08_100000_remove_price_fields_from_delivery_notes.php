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
        // ลบฟิลด์ราคาออกจาก delivery_notes
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->dropColumn('total_amount');
        });

        // ลบฟิลด์ราคาออกจาก delivery_note_items
        Schema::table('delivery_note_items', function (Blueprint $table) {
            $table->dropColumn(['unit_price', 'total_price']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // คืนค่าฟิลด์ราคากลับมา
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->decimal('total_amount', 12, 2)->default(0)->after('warehouse_id');
        });

        Schema::table('delivery_note_items', function (Blueprint $table) {
            $table->decimal('unit_price', 10, 2)->after('scanned_quantity');
            $table->decimal('total_price', 12, 2)->after('unit_price');
        });
    }
};
