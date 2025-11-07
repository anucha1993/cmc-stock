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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('length', 10, 2)->nullable()->comment('ความยาว');
            $table->decimal('thickness', 10, 2)->nullable()->comment('ความหนา');
            $table->enum('steel_type', ['not_specified', 'wire_4', 'wire_5', 'wire_6', 'wire_7'])
                  ->default('not_specified')
                  ->comment('ประเภทเหล็ก: ไม่ระบุ, ลวด 4 เส้น, ลวด 5 เส้น, ลวด 6 เส้น, ลวด 7 เส้น');
            $table->enum('side_steel_type', ['not_specified', 'no_side_steel', 'show_side_steel'])
                  ->default('not_specified')
                  ->comment('ประเภทเหล็กข้าง: ไม่ระบุ, ไม่ Show เหล็กข้าง, Show เหล็กข้าง');
            $table->enum('measurement_unit', ['meter', 'centimeter', 'millimeter'])
                  ->default('meter')
                  ->comment('มาตราวัด: เมตร, เซ็นติเมตร, มิลลิเมตร');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['length', 'thickness', 'steel_type', 'side_steel_type', 'measurement_unit']);
        });
    }
};
