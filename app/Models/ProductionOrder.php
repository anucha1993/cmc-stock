<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_code',
        'order_type',
        'product_id',
        'package_id',
        'target_warehouse_id',
        'storage_location',
        'quantity',
        'produced_quantity',
        'production_cost',
        'priority',
        'status',
        'description',
        'notes',
        'due_date',
        'start_date',
        'completion_date',
        'requested_by',
        'approved_by',
        'assigned_to',
        'requested_at',
        'approved_at'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'produced_quantity' => 'integer',
        'production_cost' => 'decimal:2',
        'due_date' => 'date',
        'start_date' => 'date',
        'completion_date' => 'date',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime'
    ];

    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_IN_PRODUCTION = 'in_production';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    const TYPE_SINGLE = 'single';
    const TYPE_PACKAGE = 'package';
    const TYPE_MULTIPLE = 'multiple';

    /**
     * สินค้าที่สั่งผลิต
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * คลังที่จะรับสินค้า
     */
    public function targetWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'target_warehouse_id');
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
     * ผู้รับผิดชอบ
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * แพที่สั่งผลิต (สำหรับ order_type = package)
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * รายการสินค้าที่สั่งผลิต (สำหรับ order_type = multiple)
     */
    public function items()
    {
        return $this->hasMany(ProductionOrderItem::class);
    }

    /**
     * สร้างรหัสใบสั่งผลิตอัตโนมัติ
     */
    public static function generateOrderCode(): string
    {
        $date = date('Ymd');
        $counter = 1;
        $code = 'PO' . $date . sprintf('%04d', $counter);
        
        while (self::where('order_code', $code)->exists()) {
            $counter++;
            $code = 'PO' . $date . sprintf('%04d', $counter);
        }
        
        return $code;
    }

    /**
     * Scope สำหรับสถานะรอดำเนินการ
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope สำหรับสถานะอนุมัติแล้ว
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope สำหรับสถานะกำลังผลิต
     */
    public function scopeInProduction($query)
    {
        return $query->where('status', self::STATUS_IN_PRODUCTION);
    }

    /**
     * Scope สำหรับความสำคัญสูง
     */
    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', [self::PRIORITY_HIGH, self::PRIORITY_URGENT]);
    }

    /**
     * Scope สำหรับใบสั่งที่เลยกำหนด
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                     ->whereIn('status', [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_IN_PRODUCTION]);
    }

    /**
     * ข้อความสถานะ
     */
    public function getStatusTextAttribute(): string
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return 'รอดำเนินการ';
            case self::STATUS_APPROVED:
                return 'อนุมัติแล้ว';
            case self::STATUS_IN_PRODUCTION:
                return 'กำลังผลิต';
            case self::STATUS_COMPLETED:
                return 'เสร็จสิ้น';
            case self::STATUS_CANCELLED:
                return 'ยกเลิก';
            default:
                return 'ไม่ระบุ';
        }
    }

    /**
     * สีสถานะ
     */
    public function getStatusColorAttribute(): string
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return 'warning';
            case self::STATUS_APPROVED:
                return 'info';
            case self::STATUS_IN_PRODUCTION:
                return 'primary';
            case self::STATUS_COMPLETED:
                return 'success';
            case self::STATUS_CANCELLED:
                return 'danger';
            default:
                return 'secondary';
        }
    }

    /**
     * ข้อความความสำคัญ
     */
    public function getPriorityTextAttribute(): string
    {
        switch ($this->priority) {
            case self::PRIORITY_LOW:
                return 'ต่ำ';
            case self::PRIORITY_NORMAL:
                return 'ปกติ';
            case self::PRIORITY_HIGH:
                return 'สูง';
            case self::PRIORITY_URGENT:
                return 'เร่งด่วน';
            default:
                return 'ปกติ';
        }
    }

    /**
     * สีความสำคัญ
     */
    public function getPriorityColorAttribute(): string
    {
        switch ($this->priority) {
            case self::PRIORITY_LOW:
                return 'secondary';
            case self::PRIORITY_NORMAL:
                return 'info';
            case self::PRIORITY_HIGH:
                return 'warning';
            case self::PRIORITY_URGENT:
                return 'danger';
            default:
                return 'info';
        }
    }

    /**
     * เปอร์เซ็นต์ความคืบหน้า
     */
    public function getProgressPercentageAttribute(): float
    {
        if ($this->quantity == 0) return 0;
        return ($this->produced_quantity / $this->quantity) * 100;
    }

    /**
     * จำนวนที่เหลือต้องผลิต
     */
    public function getRemainingQuantityAttribute(): int
    {
        return $this->quantity - $this->produced_quantity;
    }

    /**
     * ตรวจสอบว่าเลยกำหนดหรือไม่
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && $this->due_date < now() && 
               in_array($this->status, [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_IN_PRODUCTION]);
    }

    /**
     * อนุมัติใบสั่งผลิต
     */
    public function approve($userId = null, $assignedTo = null): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $userId ?: \Illuminate\Support\Facades\Auth::id(),
            'approved_at' => now(),
            'assigned_to' => $assignedTo
        ]);

        return true;
    }

    /**
     * เริ่มผลิต
     */
    public function startProduction($startDate = null): bool
    {
        if ($this->status !== self::STATUS_APPROVED) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_IN_PRODUCTION,
            'start_date' => $startDate ?: now()
        ]);

        return true;
    }

    /**
     * อัปเดตความคืบหน้าการผลิต
     */
    public function updateProgress($producedQuantity, $notes = null): bool
    {
        if ($this->status !== self::STATUS_IN_PRODUCTION) {
            return false;
        }

        if ($producedQuantity > $this->quantity) {
            throw new \Exception('จำนวนที่ผลิตไม่สามารถมากกว่าจำนวนที่สั่งได้');
        }

        $this->update([
            'produced_quantity' => $producedQuantity,
            'notes' => $this->notes . "\n" . now()->format('Y-m-d H:i') . ": " . $notes
        ]);

        // ถ้าผลิตครบแล้วให้เสร็จสิ้นอัตโนมัติ
        if ($producedQuantity >= $this->quantity) {
            $this->complete();
        }

        return true;
    }

    /**
     * เสร็จสิ้นการผลิต
     */
    public function complete($completionDate = null): bool
    {
        if (!in_array($this->status, [self::STATUS_IN_PRODUCTION, self::STATUS_APPROVED])) {
            return false;
        }

        // เพิ่มสินค้าเข้าคลัง
        $this->targetWarehouse->addStock(
            $this->product_id, 
            $this->produced_quantity ?: $this->quantity,
            null,
            "ผลิตเสร็จจากใบสั่ง {$this->order_code}"
        );

        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completion_date' => $completionDate ?: now(),
            'produced_quantity' => $this->produced_quantity ?: $this->quantity
        ]);

        // บันทึกประวัติใน inventory_transactions
        $this->product->inventoryTransactions()->create([
            'transaction_code' => InventoryTransaction::generateTransactionCode(),
            'type' => 'in',
            'quantity' => $this->produced_quantity ?: $this->quantity,
            'unit_cost' => $this->production_cost,
            'total_cost' => ($this->produced_quantity ?: $this->quantity) * $this->production_cost,
            'before_quantity' => 0, // จะต้องคำนวณจากคลัง
            'after_quantity' => 0, // จะต้องคำนวณจากคลัง
            'notes' => "ผลิตเสร็จจากใบสั่ง {$this->order_code} เข้าคลัง {$this->targetWarehouse->name}",
            'reference_type' => 'production_order',
            'reference_id' => $this->id,
            'user_id' => $this->assigned_to ?: $this->approved_by,
            'transaction_date' => now()
        ]);

        return true;
    }

    /**
     * ยกเลิกใบสั่งผลิต
     */
    public function cancel($reason = null): bool
    {
        if (!in_array($this->status, [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_IN_PRODUCTION])) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_CANCELLED,
            'notes' => $this->notes . "\nยกเลิก: " . $reason
        ]);

        return true;
    }

    /**
     * ข้อความประเภทการสั่งผลิต
     */
    public function getOrderTypeTextAttribute(): string
    {
        switch ($this->order_type) {
            case self::TYPE_SINGLE:
                return 'สินค้าเดี่ยว';
            case self::TYPE_PACKAGE:
                return 'แพ';
            case self::TYPE_MULTIPLE:
                return 'หลายรายการ';
            default:
                return 'ไม่ระบุ';
        }
    }

    /**
     * สร้าง Production Order Items จาก Package
     */
    public function createItemsFromPackage(): bool
    {
        if ($this->order_type !== self::TYPE_PACKAGE || !$this->package_id) {
            return false;
        }

        $package = $this->package()->with('packageProducts.product')->first();
        if (!$package) {
            return false;
        }

        // สร้าง items จากสินค้าในแพ
        foreach ($package->packageProducts as $packageProduct) {
            $this->items()->create([
                'product_id' => $packageProduct->product_id,
                'quantity' => $packageProduct->quantity_per_package * $this->quantity,
                'unit_cost' => $packageProduct->cost_per_unit,
                'total_cost' => $packageProduct->cost_per_unit * $packageProduct->quantity_per_package * $this->quantity,
                'notes' => "จากแพ: {$package->name}"
            ]);
        }

        return true;
    }

    /**
     * คำนวณความคืบหน้ารวม (สำหรับ multiple type)
     */
    public function getTotalProgressAttribute(): float
    {
        if ($this->order_type === self::TYPE_MULTIPLE) {
            $items = $this->items;
            if ($items->isEmpty()) return 0;

            $totalQuantity = $items->sum('quantity');
            $totalProduced = $items->sum('produced_quantity');
            
            return $totalQuantity > 0 ? ($totalProduced / $totalQuantity) * 100 : 0;
        }

        return $this->progress_percentage;
    }

    /**
     * ตรวจสอบว่าเสร็จสิ้นแล้วหรือไม่ (สำหรับ multiple type)
     */
    public function getIsAllItemsCompletedAttribute(): bool
    {
        if ($this->order_type === self::TYPE_MULTIPLE) {
            return $this->items->every(function ($item) {
                return $item->is_completed;
            });
        }

        return false;
    }

    /**
     * เสร็จสิ้นการผลิตแบบหลายรายการ
     */
    public function completeMultipleItems($userId = null): bool
    {
        if ($this->order_type !== self::TYPE_MULTIPLE) {
            return false;
        }

        if (!$this->is_all_items_completed) {
            return false;
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            // เพิ่มสินค้าเข้าคลังสำหรับทุก item
            foreach ($this->items as $item) {
                $this->targetWarehouse->addStock(
                    $item->product_id,
                    $item->produced_quantity,
                    null,
                    "ผลิตเสร็จจากใบสั่ง {$this->order_code}"
                );

                // บันทึกประวัติใน inventory_transactions
                $item->product->inventoryTransactions()->create([
                    'transaction_code' => InventoryTransaction::generateTransactionCode(),
                    'type' => 'in',
                    'quantity' => $item->produced_quantity,
                    'unit_cost' => $item->unit_cost,
                    'total_cost' => $item->produced_quantity * $item->unit_cost,
                    'before_quantity' => 0,
                    'after_quantity' => 0,
                    'notes' => "ผลิตเสร็จจากใบสั่ง {$this->order_code} เข้าคลัง {$this->targetWarehouse->name}",
                    'reference_type' => 'production_order',
                    'reference_id' => $this->id,
                    'user_id' => $userId ?: $this->assigned_to ?: $this->approved_by,
                    'transaction_date' => now()
                ]);
            }

            $this->update([
                'status' => self::STATUS_COMPLETED,
                'completion_date' => now(),
                'produced_quantity' => $this->items->sum('produced_quantity')
            ]);

            \Illuminate\Support\Facades\DB::commit();
            return true;

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollback();
            throw $e;
        }
    }

    /**
     * อัพเดทความคืบหน้าสำหรับ order_type = multiple
     */
    public function updateMultipleProgress(): void
    {
        if ($this->order_type === self::TYPE_MULTIPLE) {
            $totalQuantity = $this->items->sum('quantity');
            $totalProduced = $this->items->sum('produced_quantity');
            
            $this->update([
                'quantity' => $totalQuantity,
                'produced_quantity' => $totalProduced
            ]);

            // ถ้าทำเสร็จหมดแล้ว ให้เปลี่ยนสถานะเป็น completed
            if ($this->is_all_items_completed && $this->status === self::STATUS_IN_PRODUCTION) {
                $this->completeMultipleItems();
            }
        }
    }
}
