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
        Schema::create('barcode_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('prefix', 10); // คำนำหน้า เช่น CMC, PRD
            $table->string('type', 20); // ประเภท เช่น product, category
            $table->unsignedBigInteger('current_number')->default(1); // เลขล่าสุด
            $table->unsignedTinyInteger('padding')->default(8); // จำนวนหลักที่ต้องการ
            $table->string('format_example')->nullable(); // ตัวอย่างรูปแบบ
            $table->timestamps();
            
            $table->unique(['prefix', 'type']);
            $table->index(['type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barcode_sequences');
    }
};
