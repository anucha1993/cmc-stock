<?php

namespace App\Http\Controllers;

use App\Models\StockCheckSubmission;
use App\Models\StockCheckSession;
use App\Models\StockItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StockCheckSubmissionController extends Controller
{
    /**
     * Display a listing of submissions
     */
    public function index(Request $request)
    {
        $query = StockCheckSubmission::with(['session.warehouse', 'submittedBy', 'reviewedBy'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by user (for non-admin)
        if (!Auth::user()->hasRole(['master-admin', 'admin'])) {
            $query->where('submitted_by', Auth::id());
        }

        $submissions = $query->paginate(20);

        return view('admin.stock-check-submissions.index', compact('submissions'));
    }

    /**
     * Display the specified submission
     */
    public function show(StockCheckSubmission $submission)
    {
        $submission->load(['session.warehouse', 'submittedBy', 'reviewedBy', 'approvedBy']);
        
        // Check permission
        if (!Auth::user()->hasAnyRole(['master-admin', 'admin']) && $submission->submitted_by !== Auth::id()) {
            abort(403, 'ไม่มีสิทธิ์เข้าถึงข้อมูลนี้');
        }

        return view('admin.stock-check-submissions.show', compact('submission'));
    }

    /**
     * Show admin review interface
     */
    public function review(StockCheckSubmission $submission)
    {
        // Only admin can review
        if (!Auth::user()->hasAnyRole(['master-admin', 'admin'])) {
            abort(403, 'ไม่มีสิทธิ์ในการตรวจสอบ');
        }

        if (!$submission->canBeReviewed()) {
            return redirect()->route('admin.stock-check-submissions.show', $submission)
                ->with('error', 'ไม่สามารถตรวจสอบรายการนี้ได้');
        }

        // Start review if pending
        if ($submission->isPending()) {
            $submission->startReview(Auth::id());
        }

        $submission->load(['session.warehouse']);
        
        // Get detailed analysis
        $analysis = $this->generateDetailedAnalysis($submission);

        return view('admin.stock-check-submissions.review', compact('submission', 'analysis'));
    }

    /**
     * Process admin decision
     */
    public function processDecision(Request $request, StockCheckSubmission $submission)
    {
        // Only admin can process
        if (!Auth::user()->hasAnyRole(['master-admin', 'admin'])) {
            abort(403, 'ไม่มีสิทธิ์ในการตัดสินใจ');
        }

        $validator = Validator::make($request->all(), [
            'decision' => 'required|in:approve,reject,partial',
            'review_notes' => 'required|string|max:1000',
            'item_decisions' => 'nullable|array',
            'item_decisions.*.action' => 'required|in:add_to_system,remove_from_list,mark_missing,update_location',
            'item_decisions.*.notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $decision = $request->decision;
            $reviewNotes = $request->review_notes;
            $itemDecisions = $request->item_decisions ?? [];

            if ($decision === 'approve') {
                // Full approval - process all changes
                $this->processApproval($submission, $itemDecisions);
                $submission->approve(Auth::id(), $itemDecisions, $reviewNotes);
                
                $message = 'อนุมัติและปรับปรุงสต๊อกเรียบร้อยแล้ว';
                
            } elseif ($decision === 'partial') {
                // Partial approval - process only approved items
                $approvedDecisions = collect($itemDecisions)->filter(function($item) {
                    return in_array($item['action'], ['add_to_system', 'update_location']);
                })->toArray();
                
                $this->processApproval($submission, $approvedDecisions);
                $submission->update([
                    'status' => StockCheckSubmission::STATUS_PARTIALLY_APPROVED,
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'admin_decisions' => $itemDecisions,
                    'review_notes' => $reviewNotes
                ]);
                
                $message = 'อนุมัติบางส่วนและปรับปรุงสต๊อกเรียบร้อยแล้ว';
                
            } else {
                // Rejection
                $submission->reject(Auth::id(), $reviewNotes);
                $message = 'ปฏิเสธรายการเรียบร้อยแล้ว';
            }

            DB::commit();

            return redirect()->route('admin.stock-check-submissions.show', $submission)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Process approval and update stock
     */
    private function processApproval(StockCheckSubmission $submission, array $itemDecisions)
    {
        foreach ($itemDecisions as $decision) {
            try {
                switch ($decision['action']) {
                    case 'add_to_system':
                        $this->addItemToSystem($decision, $submission);
                        break;
                        
                    case 'update_location':
                        $this->updateItemLocation($decision);
                        break;
                        
                    case 'mark_missing':
                        $this->markItemMissing($decision);
                        break;
                        
                    // 'remove_from_list' doesn't need processing
                }
            } catch (\Exception $e) {
                // Log error but continue processing other items
                \Log::error("Failed to process decision for item: " . json_encode($decision) . " Error: " . $e->getMessage());
            }
        }
    }

    /**
     * Add new item to system
     */
    private function addItemToSystem(array $decision, StockCheckSubmission $submission)
    {
        // This would create a new Product and StockItem
        // Implementation depends on business requirements
        
        // For now, just log the action
        \Log::info("Adding new item to system: " . json_encode($decision));
    }

    /**
     * Update item location
     */
    private function updateItemLocation(array $decision)
    {
        if (isset($decision['stock_item_id']) && isset($decision['new_location'])) {
            StockItem::where('id', $decision['stock_item_id'])
                ->update(['location_code' => $decision['new_location']]);
        }
    }

    /**
     * Mark item as missing
     */
    private function markItemMissing(array $decision)
    {
        if (isset($decision['stock_item_id'])) {
            StockItem::where('id', $decision['stock_item_id'])
                ->update(['status' => 'missing']);
        }
    }

    /**
     * Generate detailed analysis for admin review
     */
    private function generateDetailedAnalysis(StockCheckSubmission $submission): array
    {
        $scannedItems = collect($submission->scanned_summary ?? []);
        $missingItems = collect($submission->discrepancy_summary['missing_items'] ?? []);
        $extraItems = collect($submission->discrepancy_summary['extra_items'] ?? []);
        
        // Ensure category_name is present by querying database if needed
        $scannedItems = $scannedItems->map(function($item) {
            if (empty($item['category_name'])) {
                $product = Product::where('barcode', $item['barcode'])->first();
                $item['category_name'] = $product?->category?->name;
            }
            return $item;
        });
        
        $missingItems = $missingItems->map(function($item) {
            if (empty($item['category_name'])) {
                $product = Product::where('barcode', $item['barcode'])->first();
                $item['category_name'] = $product?->category?->name;
            }
            return $item;
        });
        
        return [
            'total_issues' => $missingItems->count() + $extraItems->count(),
            'missing_items' => $missingItems,
            'extra_items' => $extraItems,
            'found_items' => $scannedItems->whereIn('status', ['found', 'duplicate']),
            'multi_scan_items' => $scannedItems->where('scanned_count', '>', 1),
            'recommendations' => $this->generateRecommendations($missingItems, $extraItems)
        ];
    }

    /**
     * Generate recommendations for admin
     */
    private function generateRecommendations($missingItems, $extraItems): array
    {
        $recommendations = [];
        
        if ($missingItems->count() > 0) {
            $recommendations[] = [
                'type' => 'warning',
                'title' => 'สินค้าขาดหาย',
                'description' => "มีสินค้า {$missingItems->count()} รายการที่มีในระบบแต่ไม่พบในการตรวจนับ",
                'action' => 'ควรตรวจสอบว่าสินค้าเหล่านี้ถูกย้ายไปที่อื่นหรือสูญหาย'
            ];
        }
        
        if ($extraItems->count() > 0) {
            $recommendations[] = [
                'type' => 'info',
                'title' => 'สินค้าที่ไม่มีในระบบ',
                'description' => "พบสินค้า {$extraItems->count()} รายการที่ไม่มีในระบบ",
                'action' => 'ควรตรวจสอบว่าเป็นสินค้าใหม่ที่ต้องเพิ่มเข้าระบบหรือ barcode ผิด'
            ];
        }
        
        return $recommendations;
    }

    /**
     * Recheck stock (send back for recount)
     */
    public function requestRecheck(StockCheckSubmission $submission)
    {
        // Only admin can request recheck
        if (!Auth::user()->hasRole(['master-admin', 'admin'])) {
            abort(403);
        }

        $submission->update([
            'status' => 'rejected',
            'review_notes' => 'ขอให้ตรวจนับใหม่อีกครั้ง',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now()
        ]);

        return redirect()->route('admin.stock-check-submissions.show', $submission)
            ->with('info', 'ส่งกลับให้ตรวจนับใหม่เรียบร้อยแล้ว');
    }
}