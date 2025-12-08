<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class StockCheckSession extends Model
{
    protected $fillable = [
        'session_code',
        'title',
        'description',
        'warehouse_id',
        'category_id',
        'status',
        'started_at',
        'completed_at',
        'created_by',
        'completed_by',
        'summary'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'summary' => 'array'
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Generate unique session code
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (!$model->session_code) {
                $model->session_code = 'SC' . date('Ymd') . '-' . str_pad(
                    static::whereDate('created_at', today())->count() + 1, 
                    4, 
                    '0', 
                    STR_PAD_LEFT
                );
            }
            
            if (!$model->started_at) {
                $model->started_at = now();
            }
        });
    }

    // Relationships
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function checkItems(): HasMany
    {
        return $this->hasMany(StockCheckItem::class, 'session_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    // Methods
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function canBeCompleted(): bool
    {
        return $this->isActive() && $this->checkItems()->count() > 0;
    }

    /**
     * Complete the stock check session
     */
    public function complete($userId)
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'completed_by' => $userId,
            'summary' => $this->generateSummary()
        ]);
    }

    /**
     * Generate summary of the stock check
     */
    public function generateSummary(): array
    {
        $items = $this->checkItems;
        
        return [
            'total_scanned' => $items->count(), // นับจำนวน unique barcode
            'found_in_system' => $items->whereIn('status', ['found', 'duplicate'])->count(),
            'not_in_system' => $items->where('status', 'not_in_system')->count(),
            'duplicate_scans' => $items->where('scanned_count', '>', 1)->count(), // รายการที่สแกนซ้ำ
            'total_scan_attempts' => $items->sum('scanned_count'), // จำนวนครั้งที่สแกนทั้งหมด
            'confirmed' => $items->where('status', 'confirmed')->count(),
            'system_vs_actual' => $this->getDiscrepancyReport()
        ];
    }

    /**
     * Get discrepancy report between system and actual count
     */
    public function getDiscrepancyReport(): array
    {
        // Get expected stock items in this warehouse
        $expectedItems = StockItem::where('warehouse_id', $this->warehouse_id)
            ->where('status', 'available')
            ->when($this->category_id, function($query) {
                $query->whereHas('product', function($q) {
                    $q->where('category_id', $this->category_id);
                });
            })
            ->get();
            
        $scannedBarcodes = $this->checkItems->pluck('barcode')->toArray();
        
        // Items found in system but not scanned
        $missing = $expectedItems->whereNotIn('barcode', $scannedBarcodes);
        
        // Items scanned but not in system
        $extra = $this->checkItems->where('status', 'not_in_system');
        
        return [
            'expected_count' => $expectedItems->count(),
            'actual_count' => $this->checkItems->count(),
            'missing_items' => $missing->count(),
            'extra_items' => $extra->count(),
            'match_percentage' => $expectedItems->count() > 0 
                ? round(($expectedItems->count() - $missing->count()) / $expectedItems->count() * 100, 2)
                : 0
        ];
    }

    /**
     * Get missing items (in system but not scanned)
     */
    public function getMissingItems()
    {
        $scannedBarcodes = $this->checkItems->pluck('barcode')->toArray();
        
        // Count REAL-TIME stock items with status='available'
        // This gets the actual count from the database, not cached/stored values
        $query = StockItem::with(['product.category'])
            ->where('warehouse_id', $this->warehouse_id)
            ->where('status', 'available');
        
        // Filter by category if specified
        if ($this->category_id) {
            $query->whereHas('product', function($q) {
                $q->where('category_id', $this->category_id);
            });
        }
        
        $stockItems = $query->get();
        
        // Group by product and barcode for display
        return $stockItems->groupBy('barcode')->map(function($items, $barcode) use ($scannedBarcodes) {
            if (empty($barcode) || in_array($barcode, $scannedBarcodes)) {
                return null;
            }
            
            $firstItem = $items->first();
            
            return (object)[
                'id' => $firstItem->id,
                'barcode' => $barcode,
                'product' => $firstItem->product,
                'product_name' => $firstItem->product->name,
                'category_name' => $firstItem->product->category?->name,
                'quantity' => $items->count(), // Count of actual items
                'warehouse_id' => $this->warehouse_id
            ];
        })->filter();  // Remove null entries
    }
}