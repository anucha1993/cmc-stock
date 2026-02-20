<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->enum('claim_source', ['delivery_note', 'stock_damage'])
                  ->default('delivery_note')
                  ->after('claim_number')
                  ->comment('ที่มาของเคลม: delivery_note=จากใบตัดสต็อก, stock_damage=ชำรุดเอง');

            // Make customer_name nullable (stock damage claims may not have customer)
            $table->string('customer_name')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropColumn('claim_source');
            $table->string('customer_name')->nullable(false)->change();
        });
    }
};
