<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Claim extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'claim_number',
        'claim_source',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'reference_number',
        'delivery_note_id',
        'claim_type',
        'status',
        'resolution_type',
        'priority',
        'description',
        'resolution_notes',
        'rejection_reason',
        'attachments',
        'damaged_warehouse_id',
        'created_by',
        'reviewed_by',
        'approved_by',
        'processed_by',
        'claim_date',
        'reviewed_at',
        'approved_at',
        'processed_at',
        'completed_at',
    ];

    protected $casts = [
        'attachments' => 'array',
        'claim_date' => 'date',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // ===== Status Constants =====
    const STATUS_PENDING = 'pending';
    const STATUS_REVIEWING = 'reviewing';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // ===== Claim Source Constants =====
    const SOURCE_DELIVERY_NOTE = 'delivery_note';
    const SOURCE_STOCK_DAMAGE = 'stock_damage';

    // ===== Claim Type Constants =====
    const TYPE_DEFECTIVE = 'defective';
    const TYPE_DAMAGED = 'damaged';
    const TYPE_WRONG_ITEM = 'wrong_item';
    const TYPE_MISSING_ITEM = 'missing_item';
    const TYPE_WARRANTY = 'warranty';
    const TYPE_OTHER = 'other';

    // ===== Resolution Type Constants =====
    const RESOLUTION_REPLACE = 'replace';
    const RESOLUTION_REPAIR = 'repair';
    const RESOLUTION_REFUND = 'refund';
    const RESOLUTION_CREDIT = 'credit';
    const RESOLUTION_NONE = 'none';

    // ===== Priority Constants =====
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    // ===== Boot =====
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($claim) {
            if (empty($claim->claim_number)) {
                $claim->claim_number = self::generateClaimNumber();
            }
            if (empty($claim->created_by)) {
                $claim->created_by = Auth::id();
            }
        });
    }

    // ===== Relationships =====
    public function items(): HasMany
    {
        return $this->hasMany(ClaimItem::class);
    }

    public function deliveryNote(): BelongsTo
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    public function damagedWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'damaged_warehouse_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // ===== Generate Claim Number =====
    public static function generateClaimNumber(): string
    {
        $prefix = 'CLM';
        $date = now()->format('Ymd');
        $count = static::whereDate('created_at', today())->count() + 1;
        return $prefix . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    // ===== Status Actions =====
    public function startReview($userId = null): bool
    {
        if ($this->status !== self::STATUS_PENDING) return false;

        $this->update([
            'status' => self::STATUS_REVIEWING,
            'reviewed_by' => $userId ?: Auth::id(),
            'reviewed_at' => now(),
        ]);

        return true;
    }

    public function approve($resolutionType, $notes = null, $userId = null): bool
    {
        if (!in_array($this->status, [self::STATUS_PENDING, self::STATUS_REVIEWING])) return false;

        $this->update([
            'status' => self::STATUS_APPROVED,
            'resolution_type' => $resolutionType,
            'resolution_notes' => $notes,
            'approved_by' => $userId ?: Auth::id(),
            'approved_at' => now(),
        ]);

        return true;
    }

    public function reject($reason, $userId = null): bool
    {
        if (!in_array($this->status, [self::STATUS_PENDING, self::STATUS_REVIEWING])) return false;

        $this->update([
            'status' => self::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'approved_by' => $userId ?: Auth::id(),
            'approved_at' => now(),
        ]);

        return true;
    }

    public function startProcessing($userId = null): bool
    {
        if ($this->status !== self::STATUS_APPROVED) return false;

        $this->update([
            'status' => self::STATUS_PROCESSING,
            'processed_by' => $userId ?: Auth::id(),
            'processed_at' => now(),
        ]);

        return true;
    }

    public function complete($notes = null): bool
    {
        if ($this->status !== self::STATUS_PROCESSING) return false;

        $this->update([
            'status' => self::STATUS_COMPLETED,
            'resolution_notes' => $notes ?: $this->resolution_notes,
            'completed_at' => now(),
        ]);

        return true;
    }

    public function cancel($reason = null): bool
    {
        if (in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED])) return false;

        $this->update([
            'status' => self::STATUS_CANCELLED,
            'resolution_notes' => ($this->resolution_notes ? $this->resolution_notes . "\n" : '') . 'ยกเลิก: ' . $reason,
        ]);

        return true;
    }

    // ===== Scopes =====
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeReviewing($query)
    {
        return $query->where('status', self::STATUS_REVIEWING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED, self::STATUS_REJECTED]);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    // ===== Accessors =====
    public function getClaimSourceTextAttribute(): string
    {
        return match($this->claim_source) {
            self::SOURCE_DELIVERY_NOTE => 'จากใบตัดสต็อก/ขาย',
            self::SOURCE_STOCK_DAMAGE => 'ชำรุดจากสต็อก',
            default => 'ไม่ระบุ',
        };
    }

    public function getClaimSourceColorAttribute(): string
    {
        return match($this->claim_source) {
            self::SOURCE_DELIVERY_NOTE => 'info',
            self::SOURCE_STOCK_DAMAGE => 'warning',
            default => 'secondary',
        };
    }

    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'รอตรวจสอบ',
            self::STATUS_REVIEWING => 'กำลังตรวจสอบ',
            self::STATUS_APPROVED => 'อนุมัติแล้ว',
            self::STATUS_REJECTED => 'ปฏิเสธ',
            self::STATUS_PROCESSING => 'กำลังดำเนินการ',
            self::STATUS_COMPLETED => 'เสร็จสิ้น',
            self::STATUS_CANCELLED => 'ยกเลิก',
            default => 'ไม่ระบุ',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_REVIEWING => 'info',
            self::STATUS_APPROVED => 'primary',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_PROCESSING => 'info',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_CANCELLED => 'secondary',
            default => 'light',
        };
    }

    public function getClaimTypeTextAttribute(): string
    {
        return match($this->claim_type) {
            self::TYPE_DEFECTIVE => 'สินค้าชำรุด',
            self::TYPE_DAMAGED => 'สินค้าเสียหาย',
            self::TYPE_WRONG_ITEM => 'สินค้าผิดรายการ',
            self::TYPE_MISSING_ITEM => 'สินค้าขาดหาย',
            self::TYPE_WARRANTY => 'เคลมประกัน',
            self::TYPE_OTHER => 'อื่นๆ',
            default => 'ไม่ระบุ',
        };
    }

    public function getClaimTypeColorAttribute(): string
    {
        return match($this->claim_type) {
            self::TYPE_DEFECTIVE => 'danger',
            self::TYPE_DAMAGED => 'warning',
            self::TYPE_WRONG_ITEM => 'info',
            self::TYPE_MISSING_ITEM => 'dark',
            self::TYPE_WARRANTY => 'primary',
            self::TYPE_OTHER => 'secondary',
            default => 'light',
        };
    }

    public function getResolutionTypeTextAttribute(): string
    {
        return match($this->resolution_type) {
            self::RESOLUTION_REPLACE => 'เปลี่ยนสินค้าใหม่',
            self::RESOLUTION_REPAIR => 'ซ่อมแซม',
            self::RESOLUTION_REFUND => 'คืนเงิน',
            self::RESOLUTION_CREDIT => 'เครดิต',
            self::RESOLUTION_NONE => 'ไม่มีการดำเนินการ',
            default => 'ยังไม่กำหนด',
        };
    }

    public function getPriorityTextAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'ต่ำ',
            self::PRIORITY_NORMAL => 'ปกติ',
            self::PRIORITY_HIGH => 'สูง',
            self::PRIORITY_URGENT => 'เร่งด่วน',
            default => 'ปกติ',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'secondary',
            self::PRIORITY_NORMAL => 'info',
            self::PRIORITY_HIGH => 'warning',
            self::PRIORITY_URGENT => 'danger',
            default => 'info',
        };
    }

    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    public function getDamagedItemsCountAttribute(): int
    {
        return $this->items->where('damaged_status', 'confirmed_damaged')->count();
    }

    public function getProcessedItemsCountAttribute(): int
    {
        return $this->items->where('action_taken', '!=', 'none')->count();
    }
}
