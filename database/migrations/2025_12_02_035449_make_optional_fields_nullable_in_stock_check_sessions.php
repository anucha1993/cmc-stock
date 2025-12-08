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
        Schema::table('stock_check_sessions', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
            $table->unsignedBigInteger('created_by')->nullable()->change();
            $table->unsignedBigInteger('completed_by')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_check_sessions', function (Blueprint $table) {
            $table->text('description')->nullable(false)->change();
            $table->unsignedBigInteger('created_by')->nullable(false)->change();
            $table->unsignedBigInteger('completed_by')->nullable(false)->change();
        });
    }
};
