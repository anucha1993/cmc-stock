<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class StockCheckSubmission extends Model
{
    protected $fillable = [
        'session_id',
        'submission_code',
        'status',
        'scanned_summary',
        'discrepancy_summary',
        'notes',
        'submitted_at',
        'reviewed_at',
        'approved_at',
        'submitted_by',
        'reviewed_by',
        'approved_by',
        'review_notes',
        'admin_decisions'
    ];

    protected $casts = [
        'scanned_summary' => 'array',
        'discrepancy_summary' => 'array',
        'admin_decisions' => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime'
    ];

    // Status Constants
    const STATUS_PENDING = 'pending';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PARTIALLY_APPROVED = 'partially_approved';

    /**
     * Generate unique submission code
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (!$model->submission_code) {
                $model->submission_code = 'SUB' . date('Ymd') . '-' . str_pad(
                    static::whereDate('created_at', today())->count() + 1, 
                    3, 
                    '0', 
                    STR_PAD_LEFT
                );
            }
            
            if (!$model->submitted_at) {
                $model->submitted_at = now();
            }
        });
    }

    // Relationships
    public function session(): BelongsTo
    {
        return $this->belongsTo(StockCheckSession::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', self::STATUS_UNDER_REVIEW);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    // Methods
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isUnderReview(): bool
    {
        return $this->status === self::STATUS_UNDER_REVIEW;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function canBeReviewed(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_UNDER_REVIEW]);
    }

    /**
     * Start review process
     */
    public function startReview($userId)
    {
        $this->update([
            'status' => self::STATUS_UNDER_REVIEW,
            'reviewed_by' => $userId,
            'reviewed_at' => now()
        ]);
    }

    /**
     * Approve submission
     */
    public function approve($userId, $adminDecisions = null, $notes = null)
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $userId,
            'approved_at' => now(),
            'admin_decisions' => $adminDecisions,
            'review_notes' => $notes
        ]);
    }

    /**
     * Reject submission
     */
    public function reject($userId, $notes)
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'reviewed_by' => $userId,
            'reviewed_at' => now(),
            'review_notes' => $notes
        ]);
    }

    /**
     * Get status text
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'รอตรวจสอบ',
            self::STATUS_UNDER_REVIEW => 'กำลังตรวจสอบ',
            self::STATUS_APPROVED => 'อนุมัติแล้ว',
            self::STATUS_REJECTED => 'ปฏิเสธ',
            self::STATUS_PARTIALLY_APPROVED => 'อนุมัติบางส่วน',
            default => 'ไม่ทราบสถานะ'
        };
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_UNDER_REVIEW => 'info',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_PARTIALLY_APPROVED => 'primary',
            default => 'secondary'
        };
    }

    /**
     * Generate summary for submission
     */
    public static function generateSummaryFromSession(StockCheckSession $session): array
    {
        $items = $session->checkItems()->with(['product.category'])->get();
        $missingItems = $session->getMissingItems();
        
        return [
            'scanned_items' => $items->map(function($item) {
                return [
                    'barcode' => $item->barcode,
                    'product_name' => $item->product?->name,
                    'category_name' => $item->product?->category?->name,
                    'status' => $item->status,
                    'scanned_count' => $item->scanned_count,
                    'stock_item_id' => $item->stock_item_id,
                    'location_found' => $item->location_found
                ];
            })->toArray(),
            
            'missing_items' => $missingItems->map(function($item) {
                return [
                    'barcode' => $item->barcode,
                    'product_name' => $item->product?->name,
                    'category_name' => $item->product?->category?->name,
                    'stock_item_id' => $item->id,
                    'location_code' => $item->location_code
                ];
            })->toArray(),
            
            'statistics' => [
                'total_scanned' => $items->count(),
                'found_in_system' => $items->whereIn('status', ['found', 'duplicate'])->count(),
                'not_in_system' => $items->where('status', 'not_in_system')->count(),
                'missing_from_scan' => $missingItems->count(),
                'multi_scans' => $items->where('scanned_count', '>', 1)->count()
            ]
        ];
    }
}