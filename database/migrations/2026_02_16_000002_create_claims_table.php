<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->string('claim_number')->unique();
            
            // ข้อมูลลูกค้า
            $table->string('customer_name');
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->text('customer_address')->nullable();
            
            // อ้างอิง
            $table->string('reference_number')->nullable()->comment('เลขที่ใบส่งของ/ใบเสร็จ');
            $table->foreignId('delivery_note_id')->nullable()->constrained('delivery_notes')->nullOnDelete();
            
            // ประเภทเคลม
            $table->enum('claim_type', ['defective', 'damaged', 'wrong_item', 'missing_item', 'warranty', 'other'])->default('defective');
            
            // สถานะ
            $table->enum('status', [
                'pending',        // รอตรวจสอบ
                'reviewing',      // กำลังตรวจสอบ
                'approved',       // อนุมัติ
                'rejected',       // ปฏิเสธ
                'processing',     // กำลังดำเนินการ
                'completed',      // เสร็จสิ้น
                'cancelled'       // ยกเลิก
            ])->default('pending');
            
            // วิธีดำเนินการ
            $table->enum('resolution_type', [
                'replace',        // เปลี่ยนสินค้าใหม่
                'repair',         // ซ่อมแซม
                'refund',         // คืนเงิน
                'credit',         // เครดิตสำหรับการสั่งซื้อถัดไป
                'none'            // ไม่มีการดำเนินการ
            ])->nullable();
            
            // ลำดับความสำคัญ
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            
            // รายละเอียด
            $table->text('description');
            $table->text('resolution_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->json('attachments')->nullable()->comment('รูปภาพ/เอกสารแนบ');
            
            // คลังสินค้าสำหรับสินค้าชำรุด
            $table->foreignId('damaged_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            
            // ผู้ดำเนินการ
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->foreignId('processed_by')->nullable()->constrained('users');
            
            // วันที่
            $table->date('claim_date');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
