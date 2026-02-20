<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('delivery_number');
            $table->string('share_token', 64)->nullable()->after('slug');
            $table->timestamp('share_token_expires_at')->nullable()->after('share_token');
            $table->index('share_token');
        });

        // Backfill slug สำหรับ delivery notes ที่มีอยู่แล้ว
        $notes = DB::table('delivery_notes')->whereNull('slug')->get();
        foreach ($notes as $note) {
            $base = Str::slug($note->delivery_number);
            $slug = $base . '-' . Str::lower(Str::random(6));
            DB::table('delivery_notes')->where('id', $note->id)->update(['slug' => $slug]);
        }

        // เพิ่ม unique index หลัง backfill
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropIndex(['share_token']);
            $table->dropColumn(['slug', 'share_token', 'share_token_expires_at']);
        });
    }
};
