<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClaimItem extends Model
{
    protected $fillable = [
        'claim_id',
        'product_id',
        'stock_item_id',
        'quantity',
        'reason',
        'damaged_status',
        'action_taken',
        'description',
        'inspection_notes',
        'images',
        'replacement_stock_item_id',
        'replacement_product_id',
        'inspected_by',
        'inspected_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'images' => 'array',
        'inspected_at' => 'datetime',
    ];

    // ===== Reason Constants =====
    const REASON_BROKEN = 'broken';
    const REASON_DEFORMED = 'deformed';
    const REASON_RUST = 'rust';
    const REASON_WRONG_SIZE = 'wrong_size';
    const REASON_WRONG_SPEC = 'wrong_spec';
    const REASON_MISSING = 'missing';
    const REASON_QUALITY = 'quality';
    const REASON_OTHER = 'other';

    // ===== Damaged Status Constants =====
    const DAMAGED_PENDING_INSPECTION = 'pending_inspection';
    const DAMAGED_CONFIRMED = 'confirmed_damaged';
    const DAMAGED_REPAIRABLE = 'repairable';
    const DAMAGED_UNREPAIRABLE = 'unrepairable';
    const DAMAGED_SCRAPPED = 'scrapped';
    const DAMAGED_RETURNED_SUPPLIER = 'returned_to_supplier';
    const DAMAGED_RETURNED_STOCK = 'returned_to_stock';

    // ===== Action Constants =====
    const ACTION_NONE = 'none';
    const ACTION_REPLACED = 'replaced';
    const ACTION_REPAIRED = 'repaired';
    const ACTION_SCRAPPED = 'scrapped';
    const ACTION_RETURNED = 'returned';
    const ACTION_RESTOCKED = 'restocked';

    // ===== Relationships =====
    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class);
    }

    public function replacementStockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class, 'replacement_stock_item_id');
    }

    public function replacementProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'replacement_product_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    // ===== Methods =====
    public function markAsDamaged($notes = null, $userId = null): void
    {
        $this->update([
            'damaged_status' => self::DAMAGED_CONFIRMED,
            'inspection_notes' => $notes,
            'inspected_by' => $userId ?: \Illuminate\Support\Facades\Auth::id(),
            'inspected_at' => now(),
        ]);

        // Mark the stock item as damaged
        if ($this->stock_item_id && $this->stockItem) {
            $this->stockItem->changeStatus('damaged', 'เคลม: ' . $this->claim->claim_number);
        }
    }

    public function markAsRepairable($notes = null): void
    {
        $this->update([
            'damaged_status' => self::DAMAGED_REPAIRABLE,
            'inspection_notes' => $notes,
        ]);
    }

    public function markAsUnrepairable($notes = null): void
    {
        $this->update([
            'damaged_status' => self::DAMAGED_UNREPAIRABLE,
            'inspection_notes' => $notes,
        ]);
    }

    public function scrap($notes = null): void
    {
        $this->update([
            'damaged_status' => self::DAMAGED_SCRAPPED,
            'action_taken' => self::ACTION_SCRAPPED,
            'inspection_notes' => $notes,
        ]);

        if ($this->stock_item_id && $this->stockItem) {
            $this->stockItem->changeStatus('damaged', 'ทำลาย - เคลม: ' . $this->claim->claim_number);
        }
    }

    public function returnToSupplier($notes = null): void
    {
        $this->update([
            'damaged_status' => self::DAMAGED_RETURNED_SUPPLIER,
            'action_taken' => self::ACTION_RETURNED,
            'inspection_notes' => $notes,
        ]);
    }

    public function returnToStock($notes = null): void
    {
        $this->update([
            'damaged_status' => self::DAMAGED_RETURNED_STOCK,
            'action_taken' => self::ACTION_RESTOCKED,
            'inspection_notes' => $notes,
        ]);

        if ($this->stock_item_id && $this->stockItem) {
            $this->stockItem->changeStatus('available', 'คืนสต็อก - เคลม: ' . $this->claim->claim_number);
        }
    }

    public function replaceWith($replacementStockItemId, $notes = null): void
    {
        $this->update([
            'action_taken' => self::ACTION_REPLACED,
            'replacement_stock_item_id' => $replacementStockItemId,
            'inspection_notes' => $notes,
        ]);
    }

    // ===== Accessors =====
    public function getReasonTextAttribute(): string
    {
        return match($this->reason) {
            self::REASON_BROKEN => 'แตก/หัก',
            self::REASON_DEFORMED => 'ผิดรูป/บิดงอ',
            self::REASON_RUST => 'เป็นสนิม',
            self::REASON_WRONG_SIZE => 'ขนาดไม่ตรง',
            self::REASON_WRONG_SPEC => 'สเปคไม่ตรง',
            self::REASON_MISSING => 'ขาดหาย',
            self::REASON_QUALITY => 'คุณภาพไม่ได้มาตรฐาน',
            self::REASON_OTHER => 'อื่นๆ',
            default => 'ไม่ระบุ',
        };
    }

    public function getDamagedStatusTextAttribute(): string
    {
        return match($this->damaged_status) {
            self::DAMAGED_PENDING_INSPECTION => 'รอตรวจสอบ',
            self::DAMAGED_CONFIRMED => 'ยืนยันชำรุด',
            self::DAMAGED_REPAIRABLE => 'ซ่อมได้',
            self::DAMAGED_UNREPAIRABLE => 'ซ่อมไม่ได้',
            self::DAMAGED_SCRAPPED => 'ทำลายแล้ว',
            self::DAMAGED_RETURNED_SUPPLIER => 'ส่งคืนผู้จำหน่ายแล้ว',
            self::DAMAGED_RETURNED_STOCK => 'คืนเข้าสต็อกแล้ว',
            default => 'ไม่ระบุ',
        };
    }

    public function getDamagedStatusColorAttribute(): string
    {
        return match($this->damaged_status) {
            self::DAMAGED_PENDING_INSPECTION => 'warning',
            self::DAMAGED_CONFIRMED => 'danger',
            self::DAMAGED_REPAIRABLE => 'info',
            self::DAMAGED_UNREPAIRABLE => 'dark',
            self::DAMAGED_SCRAPPED => 'secondary',
            self::DAMAGED_RETURNED_SUPPLIER => 'primary',
            self::DAMAGED_RETURNED_STOCK => 'success',
            default => 'light',
        };
    }

    public function getActionTakenTextAttribute(): string
    {
        return match($this->action_taken) {
            self::ACTION_NONE => 'ยังไม่ดำเนินการ',
            self::ACTION_REPLACED => 'เปลี่ยนแล้ว',
            self::ACTION_REPAIRED => 'ซ่อมแล้ว',
            self::ACTION_SCRAPPED => 'ทำลายแล้ว',
            self::ACTION_RETURNED => 'ส่งคืนแล้ว',
            self::ACTION_RESTOCKED => 'คืนเข้าสต็อกแล้ว',
            default => 'ไม่ระบุ',
        };
    }

    public function getActionTakenColorAttribute(): string
    {
        return match($this->action_taken) {
            self::ACTION_NONE => 'secondary',
            self::ACTION_REPLACED => 'success',
            self::ACTION_REPAIRED => 'info',
            self::ACTION_SCRAPPED => 'dark',
            self::ACTION_RETURNED => 'warning',
            self::ACTION_RESTOCKED => 'primary',
            default => 'light',
        };
    }
}
