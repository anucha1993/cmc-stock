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
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code')->unique(); // รหัสธุรกรรม
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade'); // สินค้า
            $table->enum('type', ['in', 'out', 'adjustment']); // ประเภท: เข้า, ออก, ปรับปรุง
            $table->integer('quantity'); // จำนวน (+ สำหรับเข้า, - สำหรับออก)
            $table->decimal('unit_cost', 15, 2)->nullable(); // ราคาทุนต่อหน่วย
            $table->decimal('total_cost', 15, 2)->nullable(); // ราคาทุนรวม
            $table->integer('before_quantity'); // จำนวนก่อนทำรายการ
            $table->integer('after_quantity'); // จำนวนหลังทำรายการ
            $table->text('notes')->nullable(); // หมายเหตุ
            $table->string('reference_type')->nullable(); // ประเภทเอกสารอ้างอิง
            $table->unsignedBigInteger('reference_id')->nullable(); // ID เอกสารอ้างอิง
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // ผู้ทำรายการ
            $table->timestamp('transaction_date'); // วันที่ทำรายการ
            $table->timestamps();
            
            $table->index(['product_id', 'type']);
            $table->index(['transaction_date']);
            $table->index(['user_id']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
