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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // ชื่อสินค้า
            $table->string('sku')->unique(); // รหัสสินค้า (Stock Keeping Unit)
            $table->string('barcode', 50)->unique(); // บาร์โค้ด - แต่ละรายการไม่ซ้ำ
            $table->text('description')->nullable(); // คำอธิบาย
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade'); // หมวดหมู่
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null'); // ผู้จำหน่าย
            $table->string('unit'); // หน่วยนับ (ชิ้น, กิโลกรัม, เมตร)
            $table->decimal('price', 15, 2)->default(0); // ราคาขาย
            $table->decimal('cost', 15, 2)->default(0); // ราคาทุน
            $table->integer('stock_quantity')->default(0); // จำนวนคงเหลือ
            $table->integer('min_stock')->default(0); // จำนวนสต็อกขั้นต่ำ
            $table->integer('max_stock')->nullable(); // จำนวนสต็อกสูงสุด
            $table->string('location')->nullable(); // ตำแหน่งจัดเก็บ
            $table->json('images')->nullable(); // รูปภาพสินค้า (JSON array)
            $table->boolean('is_active')->default(true); // สถานะการใช้งาน
            $table->timestamps();
            
            $table->index(['is_active', 'name']);
            $table->index(['sku']);
            $table->index(['barcode']);
            $table->index(['category_id']);
            $table->index(['supplier_id']);
            $table->index(['stock_quantity', 'min_stock']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
