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
        Schema::create('stock_check_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('stock_check_sessions')->onDelete('cascade');
            $table->string('submission_code')->unique(); // รหัสการส่ง เช่น SUB20251201-001
            $table->enum('status', [
                'pending', 
                'under_review', 
                'approved', 
                'rejected', 
                'partially_approved'
            ])->default('pending');
            
            // Submission Details
            $table->json('scanned_summary'); // สรุปรายการที่สแกน
            $table->json('discrepancy_summary'); // สรุปความแตกต่าง
            $table->text('notes')->nullable(); // หมายเหตุจากผู้ส่ง
            
            // Timestamps
            $table->datetime('submitted_at'); // วันที่ส่ง
            $table->datetime('reviewed_at')->nullable(); // วันที่รีวิว
            $table->datetime('approved_at')->nullable(); // วันที่อนุมัติ
            
            // Users
            $table->foreignId('submitted_by')->constrained('users'); // ผู้ส่ง
            $table->foreignId('reviewed_by')->nullable()->constrained('users'); // ผู้รีวิว
            $table->foreignId('approved_by')->nullable()->constrained('users'); // ผู้อนุมัติ
            
            // Admin decisions
            $table->text('review_notes')->nullable(); // หมายเหตุจาก admin
            $table->json('admin_decisions')->nullable(); // การตัดสินใจของ admin สำหรับแต่ละรายการ
            
            $table->timestamps();
            
            $table->index(['session_id', 'status']);
            $table->index(['submitted_by', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_check_submissions');
    }
};