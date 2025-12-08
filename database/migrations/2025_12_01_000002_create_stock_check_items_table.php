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
        Schema::create('stock_check_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('stock_check_sessions')->onDelete('cascade');
            $table->string('barcode'); // barcode ที่สแกน
            $table->foreignId('product_id')->nullable()->constrained('products'); // สินค้าที่ map ได้
            $table->foreignId('stock_item_id')->nullable()->constrained('stock_items'); // stock item ที่ map ได้
            $table->integer('scanned_count')->default(1); // จำนวนครั้งที่สแกน
            $table->string('location_found')->nullable(); // ตำแหน่งที่เจอจริง
            $table->enum('status', ['found', 'not_in_system', 'duplicate', 'confirmed'])->default('found');
            $table->text('notes')->nullable(); // หมายเหตุ
            $table->datetime('first_scanned_at'); // ครั้งแรกที่สแกน
            $table->datetime('last_scanned_at'); // ครั้งล่าสุดที่สแกน
            $table->foreignId('scanned_by')->constrained('users'); // ผู้สแกน
            $table->timestamps();
            
            $table->index(['session_id', 'barcode']);
            $table->index(['session_id', 'status']);
            $table->unique(['session_id', 'barcode']); // ไม่ให้สแกนซ้ำใน session เดียวกัน
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_check_items');
    }
};