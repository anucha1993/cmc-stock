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
        Schema::create('warehouse_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade'); // คลัง
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade'); // สินค้า
            $table->integer('quantity')->default(0); // จำนวนสต็อกในคลังนี้
            $table->integer('reserved_quantity')->default(0); // จำนวนที่จองไว้
            $table->integer('available_quantity')->default(0); // จำนวนที่พร้อมใช้งาน
            $table->string('location_code')->nullable(); // รหัสตำแหน่งในคลัง (เช่น A1-01, B2-05)
            $table->text('notes')->nullable(); // หมายเหตุ
            $table->timestamps();
            
            $table->unique(['warehouse_id', 'product_id']);
            $table->index(['warehouse_id', 'quantity']);
            $table->index(['product_id', 'quantity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_products');
    }
};
