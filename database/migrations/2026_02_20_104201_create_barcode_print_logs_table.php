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
        Schema::create('barcode_print_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_item_id')->nullable()->constrained('stock_items')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('printed_by')->constrained('users')->cascadeOnDelete();
            $table->string('print_type')->default('stock_item'); // stock_item or product
            $table->string('label_size'); // small, medium, large
            $table->unsignedTinyInteger('copies');
            $table->string('barcode'); // snapshot of barcode at print time
            $table->text('reason')->nullable(); // reason for reprint
            $table->boolean('is_reprint')->default(false);
            $table->boolean('verified')->default(false); // scan-verified after sticking
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['stock_item_id', 'created_at']);
            $table->index(['product_id', 'created_at']);
            $table->index('printed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barcode_print_logs');
    }
};
