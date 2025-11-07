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
        Schema::create('delivery_note_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_note_id')->constrained('delivery_notes')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            $table->integer('quantity'); // จำนวนที่ต้องจัดส่ง
            $table->integer('scanned_quantity')->default(0); // จำนวนที่สแกนแล้ว
            $table->decimal('unit_price', 10, 2); // ราคาต่อหน่วย
            $table->decimal('total_price', 12, 2); // ราคารวม
            $table->text('notes')->nullable(); // หมายเหตุ
            $table->json('scanned_items')->nullable(); // รายการ barcode ที่สแกนแล้ว
            $table->enum('status', ['pending', 'partial', 'completed'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_note_items');
    }
};
