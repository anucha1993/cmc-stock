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
        Schema::create('stock_items', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->foreignId('package_id')->nullable()->constrained()->onDelete('set null');
            
            // Item Identity
            $table->string('barcode')->unique()->comment('Barcode เฉพาะของสินค้าชิ้นนี้');
            $table->string('serial_number')->nullable()->comment('Serial number');
            $table->string('lot_number')->nullable()->comment('หมายเลขล็อต');
            $table->string('batch_number')->nullable()->comment('หมายเลขแบทช์');
            
            // Location & Movement
            $table->string('location_code')->nullable()->comment('รหัสตำแหน่งในคลัง');
            $table->enum('status', ['available', 'reserved', 'sold', 'damaged', 'expired', 'returned'])
                  ->default('available')->comment('สถานะสินค้า');
            
            // Dates
            $table->date('manufacture_date')->nullable()->comment('วันที่ผลิต');
            $table->date('expire_date')->nullable()->comment('วันที่หมดอายุ');
            $table->date('received_date')->nullable()->comment('วันที่รับเข้าคลัง');
            
            // Pricing
            $table->decimal('cost_price', 10, 2)->nullable()->comment('ราคาต้นทุน');
            $table->decimal('selling_price', 10, 2)->nullable()->comment('ราคาขาย');
            
            // Quality Control
            $table->string('grade')->nullable()->comment('เกรดสินค้า A, B, C');
            $table->string('size')->nullable()->comment('ขนาด');
            $table->text('notes')->nullable()->comment('หมายเหตุ');
            
            // Audit Trail
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['product_id', 'warehouse_id']);
            $table->index(['status']);
            $table->index(['lot_number']);
            $table->index(['expire_date']);
            $table->index(['barcode']);
            
            // Unique constraints
            $table->unique(['product_id', 'serial_number'], 'unique_product_serial');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_items');
    }
};
