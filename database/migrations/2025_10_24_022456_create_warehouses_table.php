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
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // ชื่อคลัง
            $table->string('code')->unique(); // รหัสคลัง
            $table->text('description')->nullable(); // คำอธิบาย
            $table->text('address')->nullable(); // ที่อยู่คลัง
            $table->string('contact_person')->nullable(); // ผู้ดูแลคลัง
            $table->string('phone')->nullable(); // เบอร์โทรศัพท์
            $table->decimal('max_capacity', 15, 2)->nullable(); // ความจุสูงสุด (ตารางเมตร)
            $table->decimal('current_usage', 15, 2)->default(0); // พื้นที่ที่ใช้แล้ว
            $table->boolean('is_active')->default(true); // สถานะการใช้งาน
            $table->boolean('is_main')->default(false); // คลังหลัก
            $table->timestamps();
            
            $table->index(['is_active', 'name']);
            $table->index(['code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
