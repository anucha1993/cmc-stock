<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockItem;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class StockItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = StockItem::with(['product', 'warehouse', 'package']);

        // ค้นหาตาม barcode
        if ($request->filled('barcode')) {
            $query->where('barcode', 'like', '%' . $request->barcode . '%');
        }

        // ค้นหาตาม product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // ค้นหาตาม warehouse
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // ค้นหาตาม status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // ค้นหาตาม lot number
        if ($request->filled('lot_number')) {
            $query->where('lot_number', 'like', '%' . $request->lot_number . '%');
        }

        // เรียงตามวันที่สร้างล่าสุด
        $stockItems = $query->orderBy('created_at', 'desc')->paginate(20);

        // ข้อมูลสำหรับ filters
        $products = Product::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        return view('admin.stock-items.index', compact('stockItems', 'products', 'warehouses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $products = Product::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        $packages = Package::with('products')->orderBy('name')->get();

        // Pre-fill product if specified
        $selectedProduct = null;
        if ($request->filled('product_id')) {
            $selectedProduct = Product::find($request->product_id);
        }

        return view('admin.stock-items.create', compact('products', 'warehouses', 'packages', 'selectedProduct'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'package_id' => 'nullable|exists:packages,id',
            'barcode' => 'nullable|string|unique:stock_items,barcode',
            'serial_number' => 'nullable|string',
            'lot_number' => 'nullable|string',
            'batch_number' => 'nullable|string',
            'location_code' => 'nullable|string',
            'status' => 'required|in:available,reserved,sold,damaged,expired,returned',
            'manufacture_date' => 'nullable|date',
            'expire_date' => 'nullable|date|after:manufacture_date',
            'received_date' => 'nullable|date',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'grade' => 'nullable|string',
            'size' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        
        // สร้าง barcode อัตโนมัติถ้าไม่ได้ระบุ
        if (empty($data['barcode'])) {
            $data['barcode'] = StockItem::generateBarcode($data['product_id'], $data['warehouse_id']);
        }

        // สร้าง serial number อัตโนมัติถ้าไม่ได้ระบุ
        if (empty($data['serial_number'])) {
            $data['serial_number'] = StockItem::generateSerialNumber($data['product_id']);
        }

        $data['created_by'] = Auth::id();

        StockItem::create($data);

        return redirect()->route('admin.stock-items.index')
            ->with('success', 'เพิ่มรายการสินค้าเรียบร้อยแล้ว');
    }

    /**
     * Display the specified resource.
     */
    public function show(StockItem $stockItem)
    {
        $stockItem->load(['product', 'warehouse', 'package', 'creator', 'updater']);
        
        return view('admin.stock-items.show', compact('stockItem'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StockItem $stockItem)
    {
        $products = Product::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        $packages = Package::with('products')->orderBy('name')->get();

        return view('admin.stock-items.edit', compact('stockItem', 'products', 'warehouses', 'packages'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StockItem $stockItem)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'package_id' => 'nullable|exists:packages,id',
            'barcode' => 'required|string|unique:stock_items,barcode,' . $stockItem->id,
            'serial_number' => 'nullable|string',
            'lot_number' => 'nullable|string',
            'batch_number' => 'nullable|string',
            'location_code' => 'nullable|string',
            'status' => 'required|in:available,reserved,sold,damaged,expired,returned',
            'manufacture_date' => 'nullable|date',
            'expire_date' => 'nullable|date|after:manufacture_date',
            'received_date' => 'nullable|date',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'grade' => 'nullable|string',
            'size' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['updated_by'] = Auth::id();

        $stockItem->update($data);

        return redirect()->route('admin.stock-items.index')
            ->with('success', 'แก้ไขรายการสินค้าเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StockItem $stockItem)
    {
        $stockItem->delete();

        return redirect()->route('admin.stock-items.index')
            ->with('success', 'ลบรายการสินค้าเรียบร้อยแล้ว');
    }

    /**
     * สร้าง Barcode สำหรับสินค้า
     */
    public function generateBarcode(StockItem $stockItem)
    {
        return response()->json([
            'success' => true,
            'barcode' => $stockItem->barcode,
            'product' => $stockItem->product->name
        ]);
    }

    /**
     * ค้นหาสินค้าด้วย barcode
     */
    public function findByBarcode(Request $request)
    {
        $barcode = $request->input('barcode');
        
        if (!$barcode) {
            return response()->json([
                'success' => false,
                'message' => 'กรุณาระบุ barcode'
            ]);
        }

        $stockItem = StockItem::with(['product', 'warehouse', 'package'])
            ->where('barcode', $barcode)
            ->first();

        if (!$stockItem) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบสินค้าที่มี barcode นี้'
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $stockItem
        ]);
    }

    /**
     * เปลี่ยนสถานะสินค้า
     */
    public function changeStatus(Request $request, StockItem $stockItem)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:available,reserved,sold,damaged,expired,returned',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'ข้อมูลไม่ถูกต้อง'
            ]);
        }

        $stockItem->changeStatus($request->status, $request->notes);

        return response()->json([
            'success' => true,
            'message' => 'เปลี่ยนสถานะเรียบร้อยแล้ว'
        ]);
    }

    /**
     * ย้ายสินค้าไปคลังอื่น
     */
    public function moveWarehouse(Request $request, StockItem $stockItem)
    {
        $validator = Validator::make($request->all(), [
            'warehouse_id' => 'required|exists:warehouses,id',
            'location_code' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'ข้อมูลไม่ถูกต้อง'
            ]);
        }

        $stockItem->moveToWarehouse($request->warehouse_id, $request->location_code);

        return response()->json([
            'success' => true,
            'message' => 'ย้ายสินค้าเรียบร้อยแล้ว'
        ]);
    }

    /**
     * แสดงประวัติการเคลื่อนไหวสต๊อก
     */
    public function transactions(Request $request)
    {
        $query = \App\Models\InventoryTransaction::with(['product', 'warehouse'])
            ->orderBy('transaction_date', 'desc');

        // กรองตามวันที่
        if ($request->filled('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        // กรองตามประเภท
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        // กรองตามคลัง
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // ค้นหา
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('product', function($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%");
                })
                ->orWhere('notes', 'like', "%{$search}%")
                ->orWhere('transaction_code', 'like', "%{$search}%");
            });
        }

        $transactions = $query->paginate(20);
        $warehouses = \App\Models\Warehouse::all();

        return view('admin.inventory-transactions.index', compact('transactions', 'warehouses'));
    }
}
