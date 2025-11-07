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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_code')->unique(); // รหัสการโยกย้าย
            $table->foreignId('from_warehouse_id')->constrained('warehouses')->onDelete('cascade'); // คลังต้นทาง
            $table->foreignId('to_warehouse_id')->constrained('warehouses')->onDelete('cascade'); // คลังปลายทาง
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade'); // สินค้า
            $table->integer('quantity'); // จำนวนที่โยกย้าย
            $table->enum('status', ['pending', 'in_transit', 'completed', 'cancelled'])->default('pending'); // สถานะ
            $table->text('reason')->nullable(); // เหตุผลการโยกย้าย
            $table->text('notes')->nullable(); // หมายเหตุ
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade'); // ผู้ขอโยกย้าย
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null'); // ผู้อนุมัติ
            $table->timestamp('requested_at'); // วันที่ขอ
            $table->timestamp('approved_at')->nullable(); // วันที่อนุมัติ
            $table->timestamp('completed_at')->nullable(); // วันที่เสร็จสิ้น
            $table->timestamps();
            
            $table->index(['status', 'requested_at']);
            $table->index(['from_warehouse_id', 'to_warehouse_id']);
            $table->index(['product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
