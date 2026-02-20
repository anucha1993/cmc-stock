<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockAdjustmentRequest extends Model
{
    use SoftDeletes;

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_COMPLETED = 'completed';

    const TYPE_IN = 'in';
    const TYPE_OUT = 'out';
    const TYPE_ADJUSTMENT = 'adjustment';

    const REASON_PURCHASE = 'purchase';
    const REASON_PRODUCTION = 'production';
    const REASON_SALES = 'sales';
    const REASON_DAMAGE = 'damage';
    const REASON_EXPIRED = 'expired';
    const REASON_LOST = 'lost';
    const REASON_FOUND = 'found';
    const REASON_CORRECTION = 'correction';
    const REASON_OTHER = 'other';

    protected $fillable = [
        'request_number',
        'type',
        'reason',
        'product_id',
        'warehouse_id',
        'current_quantity',
        'requested_quantity',
        'final_quantity',
        'description',
        'reference_document',
        'attachments',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'approval_notes',
        'processed_by',
        'processed_at'
    ];

    protected $casts = [
        'attachments' => 'array',
        'approved_at' => 'datetime',
        'processed_at' => 'datetime',
        'current_quantity' => 'integer',
        'requested_quantity' => 'integer',
        'final_quantity' => 'integer'
    ];

    /**
     * ความสัมพันธ์กับสินค้า
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * ความสัมพันธ์กับคลัง
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * ผู้ขอ
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * ผู้อนุมัติ
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * ผู้ดำเนินการ
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * สร้างเลขที่คำขอใหม่
     */
    public static function generateRequestNumber(): string
    {
        $prefix = 'SAR';
        $date = date('Ymd');
        $lastRequest = static::where('request_number', 'like', $prefix . $date . '%')
                           ->orderBy('request_number', 'desc')
                           ->first();

        if ($lastRequest) {
            $lastNumber = intval(substr($lastRequest->request_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * อนุมัติคำขo
     */
    public function approve($finalQuantity = null, $notes = null): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_APPROVED,
            'final_quantity' => $finalQuantity ?? $this->requested_quantity,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'approval_notes' => $notes
        ]);

        return true;
    }

    /**
     * ปฏิเสธคำขอ
     */
    public function reject($notes): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_REJECTED,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'approval_notes' => $notes
        ]);

        return true;
    }

    /**
     * ดำเนินการคำขอ (อัปเดตสต็อกจริง)
     */
    public function process(): bool
    {
        if ($this->status !== self::STATUS_APPROVED) {
            return false;
        }

        // อัปเดตสต็อกตามคำขอ
        $this->product->updateWarehouseStock(
            $this->warehouse_id,
            $this->final_quantity,
            $this->type,
            "คำขอปรับปรุงสต็อก: {$this->request_number}",
            Auth::id()
        );

        $this->update([
            'status' => self::STATUS_COMPLETED,
            'processed_by' => Auth::id(),
            'processed_at' => now()
        ]);

        return true;
    }

    /**
     * ข้อความสถานะ
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'รอการอนุมัติ',
            self::STATUS_APPROVED => 'อนุมัติแล้ว',
            self::STATUS_REJECTED => 'ปฏิเสธ',
            self::STATUS_COMPLETED => 'ดำเนินการเสร็จสิ้น',
            default => 'ไม่ระบุ'
        };
    }

    /**
     * สีสถานะ
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'info',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_COMPLETED => 'success',
            default => 'secondary'
        };
    }

    /**
     * ข้อความประเภท
     */
    public function getTypeTextAttribute(): string
    {
        return match($this->type) {
            self::TYPE_IN => 'เพิ่มสต็อก',
            self::TYPE_OUT => 'ลดสต็อก',
            self::TYPE_ADJUSTMENT => 'ปรับปรุงสต็อก',
            default => 'ไม่ระบุ'
        };
    }

    /**
     * ข้อความเหตุผล
     */
    public function getReasonTextAttribute(): string
    {
        return match($this->reason) {
            self::REASON_PURCHASE => 'รับซื้อ',
            self::REASON_PRODUCTION => 'ผลิต',
            self::REASON_SALES => 'ขาย',
            self::REASON_DAMAGE => 'ชำรุด',
            self::REASON_EXPIRED => 'หมดอายุ',
            self::REASON_LOST => 'สูญหาย',
            self::REASON_FOUND => 'พบเพิ่ม',
            self::REASON_CORRECTION => 'แก้ไขข้อผิดพลาด',
            self::REASON_OTHER => 'อื่นๆ',
            default => 'ไม่ระบุ'
        };
    }
}
