<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claim_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained('claims')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('stock_item_id')->nullable()->constrained('stock_items')->nullOnDelete();
            
            $table->integer('quantity')->default(1);
            
            // สาเหตุ
            $table->enum('reason', [
                'broken',         // แตก/หัก
                'deformed',       // ผิดรูป/บิดงอ
                'rust',           // เป็นสนิม
                'wrong_size',     // ขนาดไม่ตรง
                'wrong_spec',     // สเปคไม่ตรง
                'missing',        // ขาดหาย
                'quality',        // คุณภาพไม่ได้มาตรฐาน
                'other'           // อื่นๆ
            ])->default('broken');
            
            // สถานะสินค้าชำรุด
            $table->enum('damaged_status', [
                'pending_inspection',    // รอตรวจสอบ
                'confirmed_damaged',     // ยืนยันชำรุด
                'repairable',            // ซ่อมได้
                'unrepairable',          // ซ่อมไม่ได้
                'scrapped',              // ทำลายแล้ว
                'returned_to_supplier',  // ส่งคืนผู้จำหน่าย
                'returned_to_stock'      // คืนเข้าสต็อก
            ])->default('pending_inspection');
            
            // วิธีจัดการ
            $table->enum('action_taken', [
                'none',            // ยังไม่ดำเนินการ
                'replaced',        // เปลี่ยนแล้ว
                'repaired',        // ซ่อมแล้ว
                'scrapped',        // ทำลายแล้ว
                'returned',        // ส่งคืนแล้ว
                'restocked'        // คืนเข้าสต็อกแล้ว
            ])->default('none');
            
            $table->text('description')->nullable();
            $table->text('inspection_notes')->nullable();
            $table->json('images')->nullable();
            
            // สินค้าทดแทน (ถ้ามีการเปลี่ยน)
            $table->foreignId('replacement_stock_item_id')->nullable()->constrained('stock_items')->nullOnDelete();
            $table->foreignId('replacement_product_id')->nullable()->constrained('products')->nullOnDelete();
            
            // ผู้ตรวจสอบ
            $table->foreignId('inspected_by')->nullable()->constrained('users');
            $table->timestamp('inspected_at')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim_items');
    }
};
