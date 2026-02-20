<?php

namespace App\Http\Controllers;

use App\Models\StockCheckSession;
use App\Models\StockCheckItem;
use App\Models\StockCheckSubmission;
use App\Models\StockItem;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StockCheckController extends Controller
{
    /**
     * Display a listing of stock check sessions
     */
    public function index(Request $request)
    {
        $query = StockCheckSession::with(['warehouse', 'category', 'creator'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by warehouse
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        $sessions = $query->paginate(20);
        $warehouses = Warehouse::where('is_active', true)->get();

        return view('admin.stock-checks.index', compact('sessions', 'warehouses'));
    }

    /**
     * Show the form for creating a new session
     */
    public function create()
    {
        $warehouses = Warehouse::where('is_active', true)->get();
        $categories = Category::where('is_active', true)->get();

        return view('admin.stock-checks.create', compact('warehouses', 'categories'));
    }

    /**
     * Store a newly created session
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'warehouse_id' => 'required|exists:warehouses,id',
            'category_id' => 'nullable|exists:categories,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $session = StockCheckSession::create([
            'title' => $request->title,
            'description' => $request->description,
            'warehouse_id' => $request->warehouse_id,
            'category_id' => $request->category_id,
            'created_by' => Auth::id()
        ]);

        return redirect()->route('admin.stock-checks.show', $session)
            ->with('success', 'สร้างเซสชันตรวจสต๊อกเรียบร้อยแล้ว');
    }

    /**
     * Display the specified session
     */
    public function show(StockCheckSession $stockCheck)
    {
        $stockCheck->load(['warehouse', 'category', 'creator', 'checkItems.product', 'checkItems.stockItem']);
        
        // Get summary statistics
        $stats = [
            'total_scanned' => $stockCheck->checkItems->count(), // unique items
            'found_in_system' => $stockCheck->checkItems->whereIn('status', ['found', 'duplicate'])->count(),
            'not_in_system' => $stockCheck->checkItems->where('status', 'not_in_system')->count(),
            'duplicates' => $stockCheck->checkItems->where('scanned_count', '>', 1)->count() // items scanned multiple times
        ];

        return view('admin.stock-checks.show', compact('stockCheck', 'stats'));
    }

    /**
     * Show the form for editing the specified session
     */
    public function edit(StockCheckSession $stockCheck)
    {
        // Only allow editing if session is active
        if ($stockCheck->status !== 'active') {
            return redirect()->route('admin.stock-checks.show', $stockCheck)
                ->with('error', 'ไม่สามารถแก้ไขเซสชันที่ไม่ได้อยู่ในสถานะดำเนินการได้');
        }

        $warehouses = Warehouse::all();
        $categories = Category::all();
        
        return view('admin.stock-checks.edit', compact('stockCheck', 'warehouses', 'categories'));
    }

    /**
     * Update the specified session
     */
    public function update(Request $request, StockCheckSession $stockCheck)
    {
        // Only allow updating if session is active
        if ($stockCheck->status !== 'active') {
            return redirect()->route('admin.stock-checks.show', $stockCheck)
                ->with('error', 'ไม่สามารถแก้ไขเซสชันที่ไม่ได้อยู่ในสถานะดำเนินการได้');
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'warehouse_id' => 'required|exists:warehouses,id',
            'category_id' => 'nullable|exists:categories,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $stockCheck->update([
            'title' => $request->title,
            'description' => $request->description,
            'warehouse_id' => $request->warehouse_id,
            'category_id' => $request->category_id,
        ]);

        return redirect()->route('admin.stock-checks.show', $stockCheck)
            ->with('success', 'อัพเดทข้อมูลเซสชันเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified session
     */
    public function destroy(StockCheckSession $stockCheck)
    {
        try {
            DB::beginTransaction();

            // Delete all related check items first
            $stockCheck->checkItems()->delete();
            
            // Delete the session
            $stockCheck->delete();
            
            DB::commit();

            return redirect()->route('admin.stock-checks.index')
                ->with('success', 'ลบเซสชันการตรวจสต๊อกเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('admin.stock-checks.show', $stockCheck)
                ->with('error', 'เกิดข้อผิดพลาดในการลบ: ' . $e->getMessage());
        }
    }

    /**
     * Show scanning interface
     */
    public function scan(StockCheckSession $stockCheck)
    {
        if (!$stockCheck->isActive()) {
            return redirect()->route('admin.stock-checks.show', $stockCheck)
                ->with('error', 'เซสชันนี้ไม่สามารถสแกนได้แล้ว');
        }

        return view('admin.stock-checks.scan', compact('stockCheck'));
    }

    /**
     * Process barcode scan
     */
    public function processScan(Request $request, StockCheckSession $stockCheck)
    {
        $validator = Validator::make($request->all(), [
            'barcode' => 'required|string',
            'location_found' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'ข้อมูลไม่ถูกต้อง'
            ], 400);
        }

        if (!$stockCheck->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'เซสชันนี้ไม่สามารถสแกนได้แล้ว'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $barcode = trim($request->barcode);
            
            // Check if already scanned in this session
            $existingItem = StockCheckItem::where('session_id', $stockCheck->id)
                ->where('barcode', $barcode)
                ->first();

            if ($existingItem) {
                // Update scan count and timestamp but don't change the count in statistics
                $existingItem->update([
                    'scanned_count' => $existingItem->scanned_count + 1,
                    'last_scanned_at' => now(),
                    'scanned_by' => Auth::id(),
                    'location_found' => $request->location_found ?? $existingItem->location_found
                ]);
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'พบการสแกนซ้ำ - รายการนี้มีอยู่แล้ว (ครั้งที่ ' . $existingItem->scanned_count . ')',
                    'status' => 'duplicate',
                    'item' => $existingItem->load(['product', 'stockItem'])
                ]);
            }

            // Look for the item in stock_items table
            $stockItem = StockItem::with(['product', 'warehouse'])
                ->where('barcode', $barcode)
                ->first();

            $status = $stockItem ? StockCheckItem::STATUS_FOUND : StockCheckItem::STATUS_NOT_IN_SYSTEM;
            
            // Create new check item
            $checkItem = StockCheckItem::create([
                'session_id' => $stockCheck->id,
                'barcode' => $barcode,
                'product_id' => $stockItem?->product_id,
                'stock_item_id' => $stockItem?->id,
                'location_found' => $request->location_found,
            'status' => $status,
            'first_scanned_at' => now(),
            'last_scanned_at' => now(),
            'scanned_by' => Auth::id()
        ]);

        DB::commit();

        $message = $stockItem 
            ? 'สแกนสำเร็จ: ' . $stockItem->product->name . ' (รายการใหม่)'
            : 'สแกนสำเร็จ: ไม่พบในระบบ (รายการใหม่)';

        return response()->json([
            'success' => true,
            'message' => $message,
            'status' => $status,
            'item' => $checkItem->load(['product', 'stockItem'])
        ]);        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a scanned item from the session
     */
    public function deleteScanItem(StockCheckSession $stockCheck, StockCheckItem $checkItem)
    {
        // Verify the item belongs to this session
        if ($checkItem->session_id !== $stockCheck->id) {
            return response()->json([
                'success' => false,
                'message' => 'รายการนี้ไม่ได้อยู่ในเซสชันนี้'
            ], 403);
        }

        if (!$stockCheck->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'เซสชันนี้ไม่สามารถแก้ไขได้แล้ว'
            ], 400);
        }

        try {
            $barcode = $checkItem->barcode;
            $checkItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'ลบรายการ ' . $barcode . ' เรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show discrepancy report
     */
    public function report(StockCheckSession $stockCheck)
    {
        $stockCheck->load(['warehouse', 'category']);
        
        // Get missing items (in system but not scanned)
        $missingItems = $stockCheck->getMissingItems();
        
        // Get extra items (scanned but not in system)
        $extraItems = $stockCheck->checkItems()
            ->where('status', StockCheckItem::STATUS_NOT_IN_SYSTEM)
            ->get();
            
        // Get duplicate scans
        $duplicateItems = $stockCheck->checkItems()
            ->where('status', StockCheckItem::STATUS_DUPLICATE)
            ->get();

        $summary = $stockCheck->generateSummary();

        return view('admin.stock-checks.report', compact(
            'stockCheck', 
            'missingItems', 
            'extraItems', 
            'duplicateItems',
            'summary'
        ));
    }

    /**
     * Complete stock check session
     */
    public function complete(StockCheckSession $stockCheck)
    {
        if (!$stockCheck->canBeCompleted()) {
            return redirect()->back()
                ->with('error', 'ไม่สามารถปิดเซสชันนี้ได้');
        }

        $stockCheck->complete(Auth::id());

        return redirect()->route('admin.stock-checks.show', $stockCheck)
            ->with('success', 'ปิดเซสชันตรวจสต๊อกเรียบร้อยแล้ว');
    }

    /**
     * Submit stock check for admin approval
     */
    public function submitForApproval(Request $request, StockCheckSession $stockCheck)
    {
        // Check session status
        if ($stockCheck->status !== StockCheckSession::STATUS_ACTIVE) {
            $message = 'ต้องปิดเซสชันก่อนจึงจะสามารถส่งขออนุมัติได้';
            
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return redirect()->back()->with('error', $message);
        }

        // Check if already submitted
        $existingSubmission = StockCheckSubmission::where('session_id', $stockCheck->id)
            ->whereIn('status', ['pending', 'under_review', 'approved', 'partially_approved'])
            ->first();
        
        if ($existingSubmission) {
            $message = 'ผลตรวจสต๊อกนี้ได้ส่งไปแล้ว';
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => $message,
                    'submission_id' => $existingSubmission->id
                ], 422);
            }
            return redirect()->route('admin.stock-check-submissions.show', $existingSubmission)
                ->with('info', $message);
        }

        try {
            DB::beginTransaction();

            // Check if there are any scanned items
            if ($stockCheck->checkItems()->count() === 0) {
                throw new \Exception('ไม่มีรายการที่สแกน กรุณาสแกนสินค้าก่อน');
            }

            // Generate summary data
            $summaryData = StockCheckSubmission::generateSummaryFromSession($stockCheck);
            
            // Create submission
            $submission = StockCheckSubmission::create([
                'session_id' => $stockCheck->id,
                'submission_code' => 'SUB' . date('YmdHis'),
                'submitted_by' => Auth::id(),
                'scanned_summary' => $summaryData['scanned_items'],
                'discrepancy_summary' => [
                    'missing_items' => $summaryData['missing_items'],
                    'extra_items' => collect($summaryData['scanned_items'])->where('status', 'not_in_system')->values()->toArray(),
                    'statistics' => $summaryData['statistics']
                ],
                'notes' => $request->notes ?? null,
                'submitted_at' => now()
            ]);

            // Mark session as completed
            $stockCheck->update([
                'status' => StockCheckSession::STATUS_COMPLETED,
                'completed_at' => now()
            ]);

            DB::commit();

            $successMessage = 'ส่งผลตรวจสต๊อกเพื่อขออนุมัติเรียบร้อยแล้ว';

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'submission_id' => $submission->id,
                    'redirect_url' => route('admin.stock-check-submissions.show', $submission)
                ]);
            }

            return redirect()->route('admin.stock-check-submissions.show', $submission)
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollback();
            $errorMessage = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
            
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMessage], 500);
            }
            return redirect()->back()->with('error', $errorMessage);
        }
    }

    /**
     * Generate stock adjustment from check results (เปลี่ยนเป็น deprecated)
     */
    public function generateAdjustment(StockCheckSession $stockCheck)
    {
        // Redirect to new submission flow
        return redirect()->route('admin.stock-checks.show', $stockCheck)
            ->with('info', 'กรุณาใช้ระบบส่งขออนุมัติใหม่');
    }

    /**
     * API: Get session statistics
     */
    public function getStats(StockCheckSession $stockCheck)
    {
        // Get expected stock items count
        $expectedItems = $stockCheck->getMissingItems();
        
        return response()->json([
            'total_scanned' => $stockCheck->checkItems->count(), // unique barcodes
            'found_in_system' => $stockCheck->checkItems->whereIn('status', ['found', 'duplicate'])->count(),
            'not_in_system' => $stockCheck->checkItems->where('status', 'not_in_system')->count(),
            'duplicates' => $stockCheck->checkItems->where('scanned_count', '>', 1)->count(), // items with multiple scans
            'expected_count' => $expectedItems->count(), // expected stock items to check
            'last_scan' => $stockCheck->checkItems->max('last_scanned_at')
        ]);
    }

    /**
     * API: Get recent scans
     */
    public function getRecentScans(StockCheckSession $stockCheck)
    {
        $items = $stockCheck->checkItems()
            ->with(['product', 'stockItem'])
            ->orderBy('last_scanned_at', 'desc')
            ->get();

        return response()->json($items);
    }
}