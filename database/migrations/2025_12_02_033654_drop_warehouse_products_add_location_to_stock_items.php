<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add location_code to stock_items if it doesn't exist
        if (!Schema::hasColumn('stock_items', 'location_code')) {
            Schema::table('stock_items', function (Blueprint $table) {
                $table->string('location_code')->nullable()->comment('Location in warehouse');
            });
        }

        // Migrate location_code from warehouse_products to stock_items
        if (Schema::hasTable('warehouse_products')) {
            DB::statement('UPDATE stock_items si 
                INNER JOIN warehouse_products wp ON si.warehouse_id = wp.warehouse_id 
                AND si.product_id = wp.product_id
                SET si.location_code = wp.location_code 
                WHERE si.location_code IS NULL AND wp.location_code IS NOT NULL');
        }

        // Drop warehouse_products table
        Schema::dropIfExists('warehouse_products');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate warehouse_products table
        Schema::create('warehouse_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->integer('available_quantity')->default(0);
            $table->string('location_code')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['warehouse_id', 'product_id']);
        });

        // Remove location_code from stock_items
        if (Schema::hasColumn('stock_items', 'location_code')) {
            Schema::table('stock_items', function (Blueprint $table) {
                $table->dropColumn('location_code');
            });
        }
    }
};
