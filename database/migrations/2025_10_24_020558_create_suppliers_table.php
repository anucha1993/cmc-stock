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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // ชื่อผู้จำหน่าย
            $table->string('code')->unique(); // รหัสผู้จำหน่าย
            $table->string('contact_person')->nullable(); // ชื่อผู้ติดต่อ
            $table->string('phone')->nullable(); // เบอร์โทรศัพท์
            $table->string('email')->nullable(); // อีเมล
            $table->text('address')->nullable(); // ที่อยู่
            $table->string('tax_id')->nullable(); // เลขประจำตัวผู้เสียภาษี
            $table->text('notes')->nullable(); // หมายเหตุ
            $table->boolean('is_active')->default(true); // สถานะการใช้งาน
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
        Schema::dropIfExists('suppliers');
    }
};
