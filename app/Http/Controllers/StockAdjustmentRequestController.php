<?php

namespace App\Http\Controllers;

use App\Models\StockAdjustmentRequest;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class StockAdjustmentRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = StockAdjustmentRequest::with(['product', 'warehouse', 'requestedBy', 'approvedBy']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('product', function($productQuery) use ($search) {
                      $productQuery->where('name', 'like', "%{$search}%")
                                  ->orWhere('sku', 'like', "%{$search}%");
                  });
            });
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(20);

        // Statistics
        $stats = [
            'pending' => StockAdjustmentRequest::where('status', 'pending')->count(),
            'approved' => StockAdjustmentRequest::where('status', 'approved')->count(),
            'completed' => StockAdjustmentRequest::where('status', 'completed')->count(),
            'rejected' => StockAdjustmentRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.stock-adjustments.index', compact('requests', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('admin.stock-adjustments.create', compact('products', 'warehouses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:in,out,adjustment',
            'reason' => 'required|in:purchase,production,sales,damage,expired,lost,found,correction,other',
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'requested_quantity' => 'required|integer|min:1',
            'description' => 'required|string|max:1000',
            'reference_document' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // ตรวจสอบสต็อกปัจจุบัน
        $warehouse = Warehouse::findOrFail($request->warehouse_id);
        $currentStock = $warehouse->getProductStock($request->product_id);

        // สำหรับการลดสต็อก ตรวจสอบว่าสต็อกเพียงพอ
        if ($request->type === 'out' && $currentStock < $request->requested_quantity) {
            return redirect()->back()
                           ->with('error', 'สต็อกในคลังไม่เพียงพอ (มีเพียง ' . number_format($currentStock) . ' หน่วย)')
                           ->withInput();
        }

        $stockAdjustment = StockAdjustmentRequest::create([
            'request_number' => StockAdjustmentRequest::generateRequestNumber(),
            'type' => $request->type,
            'reason' => $request->reason,
            'product_id' => $request->product_id,
            'warehouse_id' => $request->warehouse_id,
            'current_quantity' => $currentStock,
            'requested_quantity' => $request->requested_quantity,
            'description' => $request->description,
            'reference_document' => $request->reference_document,
            'requested_by' => Auth::id(),
        ]);

        return redirect()->route('admin.stock-adjustments.show', $stockAdjustment)
                        ->with('success', 'สร้างคำขอปรับปรุงสต็อกเรียบร้อยแล้ว รอการอนุมัติ');
    }

    /**
     * Display the specified resource.
     */
    public function show(StockAdjustmentRequest $stockAdjustment)
    {
        $stockAdjustment->load(['product', 'warehouse', 'requestedBy', 'approvedBy', 'processedBy']);
        
        return view('admin.stock-adjustments.show', compact('stockAdjustment'));
    }

    /**
     * อนุมัติคำขอ
     */
    public function approve(Request $request, StockAdjustmentRequest $stockAdjustment)
    {
        $validator = Validator::make($request->all(), [
            'final_quantity' => 'nullable|integer|min:1',
            'approval_notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        if ($stockAdjustment->approve($request->final_quantity, $request->approval_notes)) {
            return redirect()->back()->with('success', 'อนุมัติคำขอเรียบร้อยแล้ว');
        }

        return redirect()->back()->with('error', 'ไม่สามารถอนุมัติคำขอได้');
    }

    /**
     * ปฏิเสธคำขอ
     */
    public function reject(Request $request, StockAdjustmentRequest $stockAdjustment)
    {
        $validator = Validator::make($request->all(), [
            'approval_notes' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        if ($stockAdjustment->reject($request->approval_notes)) {
            return redirect()->back()->with('success', 'ปฏิเสธคำขอเรียบร้อยแล้ว');
        }

        return redirect()->back()->with('error', 'ไม่สามารถปฏิเสธคำขอได้');
    }

    /**
     * ดำเนินการคำขอ (อัปเดตสต็อกจริง)
     */
    public function process(StockAdjustmentRequest $stockAdjustment)
    {
        if ($stockAdjustment->process()) {
            return redirect()->back()->with('success', 'ดำเนินการปรับปรุงสต็อกเรียบร้อยแล้ว');
        }

        return redirect()->back()->with('error', 'ไม่สามารถดำเนินการได้');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StockAdjustmentRequest $stockAdjustment)
    {
        if ($stockAdjustment->status !== StockAdjustmentRequest::STATUS_PENDING) {
            return redirect()->back()->with('error', 'ไม่สามารถลบคำขอที่ดำเนินการแล้วได้');
        }

        $stockAdjustment->delete();
        
        return redirect()->route('admin.stock-adjustments.index')
                        ->with('success', 'ลบคำขอเรียบร้อยแล้ว');
    }

    /**
     * Get warehouse stock for a specific product
     */
    public function getWarehouseStock($warehouseId, $productId)
    {
        $warehouse = Warehouse::findOrFail($warehouseId);
        $product = Product::findOrFail($productId);
        
        $stock = $warehouse->getProductStock($productId);
        
        return response()->json([
            'stock' => $stock,
            'unit' => $product->unit,
            'warehouse' => $warehouse->name,
            'product' => $product->name
        ]);
    }
}
