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
        Schema::table('production_orders', function (Blueprint $table) {
            $table->enum('order_type', ['single', 'package', 'multiple'])->default('single')->after('order_code');
            $table->foreignId('package_id')->nullable()->constrained()->after('product_id');
            
            // Make product_id nullable เพราะอาจจะสั่งผลิตจากแพ
            $table->unsignedBigInteger('product_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_orders', function (Blueprint $table) {
            $table->dropColumn(['order_type', 'package_id']);
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
        });
    }
};
