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
        Schema::create('package_products', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->unsignedBigInteger('package_id');
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');
            
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            
            // จำนวนสินค้าในแพ
            $table->decimal('quantity_per_package', 10, 2)->comment('จำนวนสินค้าต่อแพ');
            $table->string('unit', 20)->default('ชิ้น')->comment('หน่วยสินค้า');
            
            // รายละเอียดเพิ่มเติม
            $table->decimal('length_per_unit', 8, 2)->nullable()->comment('ความยาวต่อหน่วย');
            $table->decimal('weight_per_unit', 8, 2)->nullable()->comment('น้ำหนักต่อหน่วย');
            $table->decimal('cost_per_unit', 10, 2)->nullable()->comment('ต้นทุนต่อหน่วย');
            $table->decimal('selling_price_per_unit', 10, 2)->nullable()->comment('ราคาขายต่อหน่วย');
            
            // ข้อมูลคุณภาพ/เกรด
            $table->string('grade', 50)->nullable()->comment('เกรด เช่น A, B, C');
            $table->string('size', 50)->nullable()->comment('ขนาด เช่น เล็ก, กลาง, ใหญ่');
            $table->text('specifications')->nullable()->comment('รายละเอียดเพิ่มเติม');
            
            // การจัดเรียง
            $table->integer('sort_order')->default(0)->comment('ลำดับในแพ');
            $table->boolean('is_main_product')->default(false)->comment('สินค้าหลักในแพ');
            
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['package_id', 'product_id']);
            
            // Indexes
            $table->index(['package_id', 'sort_order']);
            $table->index(['product_id']);
            $table->index(['is_main_product']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_products');
    }
};
