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
        Schema::table('products', function (Blueprint $table) {
            $table->enum('size_type', ['standard', 'custom'])->default('standard')->after('unit')->comment('ประเภทไซส์: standard=มาตรฐาน, custom=กำหนดเอง');
            $table->text('custom_size_options')->nullable()->after('size_type')->comment('ตัวเลือกไซส์กำหนดเอง (JSON)');
            $table->boolean('allow_custom_order')->default(false)->after('custom_size_options')->comment('รับผลิตตามสั่ง');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['size_type', 'custom_size_options', 'allow_custom_order']);
        });
    }
};
