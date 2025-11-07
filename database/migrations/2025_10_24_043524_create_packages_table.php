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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('ชื่อแพ เช่น แพพี่ผิว');
            $table->string('code', 20)->unique()->comment('รหัสแพ');
            $table->text('description')->nullable()->comment('คำอธิบายแพ');
            
            // ข้อมูลแพ
            $table->integer('package_quantity')->default(1)->comment('จำนวนแพ เช่น 4 แพ');
            $table->decimal('length_per_package', 10, 2)->nullable()->comment('ความยาวต่อแพ เช่น 240 เมตร');
            $table->string('length_unit', 20)->default('เมตร')->comment('หน่วยความยาว');
            $table->integer('items_per_package')->default(1)->comment('จำนวนต้นต่อแพ เช่น 10 ต้น/แพ');
            $table->string('item_unit', 20)->default('ต้น')->comment('หน่วยสินค้าในแพ');
            
            // การคำนวณ
            $table->decimal('total_length', 12, 2)->storedAs('length_per_package * package_quantity')->comment('ความยาวรวม');
            $table->integer('total_items')->storedAs('items_per_package * package_quantity')->comment('จำนวนรวมทั้งหมด');
            
            // ข้อมูลเพิ่มเติม
            $table->decimal('weight_per_package', 10, 2)->nullable()->comment('น้ำหนักต่อแพ');
            $table->string('weight_unit', 20)->default('กิโลกรัม')->comment('หน่วยน้ำหนัก');
            $table->decimal('cost_per_package', 12, 2)->nullable()->comment('ต้นทุนต่อแพ');
            $table->decimal('selling_price_per_package', 12, 2)->nullable()->comment('ราคาขายต่อแพ');
            
            // การจัดการ
            $table->string('color', 7)->default('#007bff')->comment('สีแพ');
            $table->integer('sort_order')->default(0)->comment('ลำดับการแสดง');
            $table->boolean('is_active')->default(true)->comment('เปิดใช้งาน');
            
            // ข้อมูลผู้จำหน่าย
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
            
            // หมวดหมู่
            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['is_active', 'sort_order']);
            $table->index(['supplier_id']);
            $table->index(['category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
