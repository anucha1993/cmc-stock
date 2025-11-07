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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // ชื่อหมวดหมู่
            $table->string('code')->unique(); // รหัสหมวดหมู่
            $table->text('description')->nullable(); // คำอธิบาย
            $table->string('color', 7)->default('#007bff'); // สีหมวดหมู่ (hex code)
            $table->string('icon')->nullable(); // ไอคอน
            $table->boolean('is_active')->default(true); // สถานะการใช้งาน
            $table->timestamps();
            
            $table->index(['is_active', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
