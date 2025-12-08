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
        Schema::create('stock_check_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_code')->unique(); // รหัส session
            $table->string('title'); // ชื่อการตรวจสต๊อก
            $table->text('description')->nullable(); // คำอธิบาย
            $table->foreignId('warehouse_id')->constrained('warehouses'); // คลังที่ตรวจ
            $table->foreignId('category_id')->nullable()->constrained('categories'); // หมวดหมู่ (ถ้าต้องการกรอง)
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active'); // สถานะ
            $table->datetime('started_at'); // เริ่มตรวจ
            $table->datetime('completed_at')->nullable(); // เสร็จสิ้น
            $table->foreignId('created_by')->constrained('users'); // ผู้สร้าง
            $table->foreignId('completed_by')->nullable()->constrained('users'); // ผู้ปิด session
            $table->json('summary')->nullable(); // สรุปผลการตรวจ
            $table->timestamps();
            
            $table->index(['warehouse_id', 'status']);
            $table->index(['created_by', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_check_sessions');
    }
};