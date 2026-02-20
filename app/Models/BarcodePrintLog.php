<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarcodePrintLog extends Model
{
    protected $fillable = [
        'stock_item_id',
        'product_id',
        'printed_by',
        'print_type',
        'label_size',
        'copies',
        'barcode',
        'reason',
        'is_reprint',
        'verified',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'is_reprint' => 'boolean',
        'verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    // ===== Relationships =====

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function printer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'printed_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // ===== Accessors =====

    public function getLabelSizeTextAttribute(): string
    {
        return match ($this->label_size) {
            'small' => 'เล็ก (4×2 ซม.)',
            'medium' => 'กลาง (6×3 ซม.)',
            'large' => 'ใหญ่ (8×4 ซม.)',
            default => $this->label_size,
        };
    }

    public function getPrintTypeTextAttribute(): string
    {
        return match ($this->print_type) {
            'stock_item' => 'รายชิ้น (Stock Item)',
            'product' => 'ระดับสินค้า (Product)',
            default => $this->print_type,
        };
    }

    // ===== Scopes =====

    public function scopeForStockItem($query, int $stockItemId)
    {
        return $query->where('stock_item_id', $stockItemId);
    }

    public function scopeReprints($query)
    {
        return $query->where('is_reprint', true);
    }

    public function scopeUnverified($query)
    {
        return $query->where('verified', false);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }
}
