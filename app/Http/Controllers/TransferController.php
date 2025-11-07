<?php

namespace App\Http\Controllers;

use App\Models\Transfer;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransferController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:master-admin,admin']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Transfer::with(['fromWarehouse', 'toWarehouse', 'product', 'user']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('transfer_code', 'like', "%{$search}%")
                  ->orWhereHas('product', function($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by warehouse
        if ($request->filled('warehouse')) {
            $query->where(function($q) use ($request) {
                $q->where('from_warehouse_id', $request->warehouse)
                  ->orWhere('to_warehouse_id', $request->warehouse);
            });
        }

        // Date range
        if ($request->filled('date_from')) {
            $query->whereDate('transfer_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('transfer_date', '<=', $request->date_to);
        }

        $transfers = $query->orderBy('created_at', 'desc')->paginate(15);
        $warehouses = Warehouse::where('is_active', true)->get();

        return view('admin.transfers.index', compact('transfers', 'warehouses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $warehouses = Warehouse::where('is_active', true)->get();
        $products = Product::with(['warehouseProducts.warehouse'])->get();
        
        return view('admin.transfers.create', compact('warehouses', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
            'priority' => 'required|in:low,normal,high,urgent',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // ตรวจสอบสต็อกที่มีอยู่
        $fromWarehouse = Warehouse::find($request->from_warehouse_id);
        $warehouseProduct = $fromWarehouse->warehouseProducts()
                                         ->where('product_id', $request->product_id)
                                         ->first();

        if (!$warehouseProduct || $warehouseProduct->available_quantity < $request->quantity) {
            return redirect()->back()->with('error', 'สต็อกสินค้าในคลังต้นทางไม่เพียงพอ');
        }

        try {
            DB::beginTransaction();

            $transfer = Transfer::create([
                'transfer_code' => Transfer::generateTransferCode(),
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id' => $request->to_warehouse_id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'status' => 'pending',
                'priority' => $request->priority,
                'notes' => $request->notes,
                'transfer_date' => now(),
                'requested_by' => Auth::id(),
                'user_id' => Auth::id()
            ]);

            // จองสต็อกในคลังต้นทาง
            $warehouseProduct->increment('reserved_quantity', $request->quantity);
            $warehouseProduct->decrement('available_quantity', $request->quantity);

            DB::commit();

            return redirect()->route('admin.transfers.index')->with('success', 'ใบโอนสินค้าถูกสร้างเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Transfer $transfer)
    {
        $transfer->load(['fromWarehouse', 'toWarehouse', 'product.category', 'user', 'approvedByUser', 'completedByUser']);
        
        return view('admin.transfers.show', compact('transfer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transfer $transfer)
    {
        if ($transfer->status !== 'pending') {
            return redirect()->route('admin.transfers.show', $transfer)
                           ->with('error', 'ไม่สามารถแก้ไขใบโอนที่ดำเนินการแล้ว');
        }

        $warehouses = Warehouse::where('is_active', true)->get();
        $products = Product::with(['warehouseProducts.warehouse'])->get();
        
        return view('admin.transfers.edit', compact('transfer', 'warehouses', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transfer $transfer)
    {
        if ($transfer->status !== 'pending') {
            return redirect()->route('admin.transfers.show', $transfer)
                           ->with('error', 'ไม่สามารถแก้ไขใบโอนที่ดำเนินการแล้ว');
        }

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
            'priority' => 'required|in:low,normal,high,urgent',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // คืนการจองเดิม
            $fromWarehouse = $transfer->fromWarehouse;
            $warehouseProduct = $fromWarehouse->warehouseProducts()
                                             ->where('product_id', $transfer->product_id)
                                             ->first();

            $warehouseProduct->decrement('reserved_quantity', $transfer->quantity);
            $warehouseProduct->increment('available_quantity', $transfer->quantity);

            // ตรวจสอบสต็อกใหม่
            if ($warehouseProduct->available_quantity < $request->quantity) {
                return redirect()->back()->with('error', 'สต็อกสินค้าในคลังต้นทางไม่เพียงพอ');
            }

            // จองใหม่
            $warehouseProduct->increment('reserved_quantity', $request->quantity);
            $warehouseProduct->decrement('available_quantity', $request->quantity);

            // อัปเดตใบโอน
            $transfer->update([
                'quantity' => $request->quantity,
                'notes' => $request->notes,
                'priority' => $request->priority,
            ]);

            DB::commit();

            return redirect()->route('admin.transfers.show', $transfer)
                           ->with('success', 'ใบโอนสินค้าถูกอัปเดตเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transfer $transfer)
    {
        if ($transfer->status !== 'pending') {
            return redirect()->back()->with('error', 'ไม่สามารถลบใบโอนที่ดำเนินการแล้ว');
        }

        try {
            DB::beginTransaction();

            // คืนการจอง
            $fromWarehouse = $transfer->fromWarehouse;
            $warehouseProduct = $fromWarehouse->warehouseProducts()
                                             ->where('product_id', $transfer->product_id)
                                             ->first();

            if ($warehouseProduct) {
                $warehouseProduct->decrement('reserved_quantity', $transfer->quantity);
                $warehouseProduct->increment('available_quantity', $transfer->quantity);
            }

            $transfer->delete();

            DB::commit();

            return redirect()->route('admin.transfers.index')->with('success', 'ใบโอนสินค้าถูกลบเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * อนุมัติใบโอน
     */
    public function approve(Transfer $transfer)
    {
        if ($transfer->status !== 'pending') {
            return redirect()->back()->with('error', 'ใบโอนนี้ดำเนินการไปแล้ว');
        }

        try {
            DB::beginTransaction();

            $result = $transfer->approve(Auth::id());
            
            if (!$result) {
                return redirect()->back()->with('error', 'ไม่สามารถอนุมัติได้ เนื่องจากสต็อกไม่เพียงพอ');
            }

            DB::commit();

            return redirect()->route('admin.transfers.show', $transfer)
                           ->with('success', 'ใบโอนถูกอนุมัติและเริ่มขนส่งแล้ว');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * ยกเลิกใบโอน
     */
    public function cancel(Transfer $transfer)
    {
        if (!in_array($transfer->status, ['pending', 'in_transit'])) {
            return redirect()->back()->with('error', 'ไม่สามารถยกเลิกใบโอนที่เสร็จสิ้นแล้ว');
        }

        try {
            DB::beginTransaction();

            $transfer->cancel(Auth::id());

            DB::commit();

            return redirect()->route('admin.transfers.show', $transfer)
                           ->with('success', 'ใบโอนถูกยกเลิกเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * รับสินค้า (เสร็จสิ้นการโอน)
     */
    public function complete(Transfer $transfer)
    {
        if ($transfer->status !== 'in_transit') {
            return redirect()->back()->with('error', 'ใบโอนนี้ไม่อยู่ในสถานะขนส่ง');
        }

        try {
            DB::beginTransaction();

            $transfer->complete(Auth::id());

            DB::commit();

            return redirect()->route('admin.transfers.show', $transfer)
                           ->with('success', 'การโอนสินค้าเสร็จสิ้นเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * แสดงรายงานการโอน
     */
    public function report(Request $request)
    {
        $query = Transfer::with(['fromWarehouse', 'toWarehouse', 'product']);

        // Date range
        $dateFrom = $request->date_from ?: now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?: now()->endOfMonth()->format('Y-m-d');
        
        $query->whereDate('transfer_date', '>=', $dateFrom)
              ->whereDate('transfer_date', '<=', $dateTo);

        // Group by warehouse
        if ($request->filled('warehouse')) {
            $query->where(function($q) use ($request) {
                $q->where('from_warehouse_id', $request->warehouse)
                  ->orWhere('to_warehouse_id', $request->warehouse);
            });
        }

        $transfers = $query->get();

        // สถิติ
        $stats = [
            'total_transfers' => $transfers->count(),
            'completed_transfers' => $transfers->where('status', 'completed')->count(),
            'pending_transfers' => $transfers->where('status', 'pending')->count(),
            'in_transit_transfers' => $transfers->where('status', 'in_transit')->count(),
            'cancelled_transfers' => $transfers->where('status', 'cancelled')->count(),
            'total_quantity' => $transfers->where('status', 'completed')->sum('quantity'),
        ];

        $warehouses = Warehouse::where('is_active', true)->get();

        return view('admin.transfers.report', compact('transfers', 'stats', 'warehouses', 'dateFrom', 'dateTo'));
    }
}
