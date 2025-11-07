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
        Schema::create('delivery_notes', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_number')->unique(); // เลขที่ใบส่งของ
            $table->string('sales_order_number')->nullable(); // เลขที่ใบสั่งขาย
            $table->string('customer_name'); // ชื่อลูกค้า
            $table->text('customer_address')->nullable(); // ที่อยู่ลูกค้า
            $table->string('customer_phone')->nullable(); // เบอร์โทรลูกค้า
            $table->date('delivery_date'); // วันที่จัดส่ง
            $table->enum('status', ['pending', 'confirmed', 'scanned', 'completed'])->default('pending');
            $table->foreignId('warehouse_id')->constrained('warehouses'); // คลังที่จ่าย
            $table->decimal('total_amount', 12, 2)->default(0); // ยอดรวม
            $table->text('notes')->nullable(); // หมายเหตุ
            $table->foreignId('created_by')->constrained('users'); // ผู้สร้าง
            $table->foreignId('confirmed_by')->nullable()->constrained('users'); // ผู้ยืนยัน
            $table->timestamp('confirmed_at')->nullable(); // วันที่ยืนยัน
            $table->foreignId('scanned_by')->nullable()->constrained('users'); // ผู้สแกน
            $table->timestamp('scanned_at')->nullable(); // วันที่สแกน
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_notes');
    }
};
