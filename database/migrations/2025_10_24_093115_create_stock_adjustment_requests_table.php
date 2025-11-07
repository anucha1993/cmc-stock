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
        Schema::create('stock_adjustment_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique(); // เลขที่คำขอ
            $table->enum('type', ['in', 'out', 'adjustment']); // ประเภท: เข้า, ออก, ปรับปรุง
            $table->enum('reason', [
                'purchase', 'production', 'sales', 'damage', 'expired', 
                'lost', 'found', 'correction', 'other'
            ]); // เหตุผล
            $table->foreignId('product_id')->constrained('products'); // สินค้า
            $table->foreignId('warehouse_id')->constrained('warehouses'); // คลัง
            $table->integer('current_quantity'); // จำนวนปัจจุบัน
            $table->integer('requested_quantity'); // จำนวนที่ขอ
            $table->integer('final_quantity')->nullable(); // จำนวนสุดท้าย (หลังอนุมัติ)
            $table->text('description'); // รายละเอียด
            $table->string('reference_document')->nullable(); // เอกสารอ้างอิง
            $table->json('attachments')->nullable(); // ไฟล์แนบ
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->foreignId('requested_by')->constrained('users'); // ผู้ขอ
            $table->foreignId('approved_by')->nullable()->constrained('users'); // ผู้อนุมัติ
            $table->timestamp('approved_at')->nullable(); // วันที่อนุมัติ
            $table->text('approval_notes')->nullable(); // หมายเหตุการอนุมัติ
            $table->foreignId('processed_by')->nullable()->constrained('users'); // ผู้ดำเนินการ
            $table->timestamp('processed_at')->nullable(); // วันที่ดำเนินการ
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustment_requests');
    }
};
