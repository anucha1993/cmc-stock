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
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code')->unique(); // รหัสใบสั่งผลิต
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade'); // สินค้าที่สั่งผลิต
            $table->foreignId('target_warehouse_id')->constrained('warehouses')->onDelete('cascade'); // คลังที่จะรับสินค้า
            $table->integer('quantity'); // จำนวนที่สั่งผลิต
            $table->integer('produced_quantity')->default(0); // จำนวนที่ผลิตแล้ว
            $table->decimal('production_cost', 15, 2)->default(0); // ต้นทุนการผลิต
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal'); // ความสำคัญ
            $table->enum('status', ['pending', 'approved', 'in_production', 'completed', 'cancelled'])->default('pending'); // สถานะ
            $table->text('description')->nullable(); // รายละเอียด
            $table->text('notes')->nullable(); // หมายเหตุ
            $table->date('due_date')->nullable(); // วันที่ต้องการ
            $table->date('start_date')->nullable(); // วันที่เริ่มผลิต
            $table->date('completion_date')->nullable(); // วันที่ผลิตเสร็จ
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade'); // ผู้ขอ
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null'); // ผู้อนุมัติ
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null'); // ผู้รับผิดชอบ
            $table->timestamp('requested_at'); // วันที่ขอ
            $table->timestamp('approved_at')->nullable(); // วันที่อนุมัติ
            $table->timestamps();
            
            $table->index(['status', 'due_date']);
            $table->index(['product_id', 'status']);
            $table->index(['target_warehouse_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_orders');
    }
};
