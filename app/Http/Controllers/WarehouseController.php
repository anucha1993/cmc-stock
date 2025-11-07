<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WarehouseController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:master-admin,admin']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $warehouses = Warehouse::withCount(['warehouseProducts'])
                               ->withSum('warehouseProducts', 'quantity')
                               ->paginate(10);
        return view('admin.warehouses.index', compact('warehouses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.warehouses.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'location' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // ถ้าเป็นคลังหลัก ให้ยกเลิกคลังหลักเดิม
        if ($request->has('is_main')) {
            Warehouse::where('is_main', true)->update(['is_main' => false]);
        }

        $warehouse = Warehouse::create([
            'name' => $request->name,
            'code' => Warehouse::generateCode($request->name),
            'location' => $request->location,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
            'is_main' => $request->has('is_main'),
        ]);

        return redirect()->route('admin.warehouses.index')->with('success', 'คลังสินค้าถูกสร้างเรียบร้อยแล้ว');
    }

    /**
     * Display the specified resource.
     */
    public function show(Warehouse $warehouse)
    {
        $warehouse->load(['warehouseProducts.product.category']);
        
        return view('admin.warehouses.show', compact('warehouse'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Warehouse $warehouse)
    {
        return view('admin.warehouses.edit', compact('warehouse'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Warehouse $warehouse)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:warehouses,code,' . $warehouse->id,
            'location' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // ถ้าเป็นคลังหลัก ให้ยกเลิกคลังหลักเดิม
        if ($request->has('is_main') && !$warehouse->is_main) {
            Warehouse::where('is_main', true)->update(['is_main' => false]);
        }

        $warehouse->update([
            'name' => $request->name,
            'code' => $request->code,
            'location' => $request->location,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
            'is_main' => $request->has('is_main'),
        ]);

        return redirect()->route('admin.warehouses.index')->with('success', 'คลังสินค้าถูกอัปเดตเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Warehouse $warehouse)
    {
        // ตรวจสอบว่ามีสินค้าในคลังหรือไม่
        if ($warehouse->warehouseProducts()->where('quantity', '>', 0)->count() > 0) {
            return redirect()->back()->with('error', 'ไม่สามารถลบคลังที่มีสินค้าอยู่ได้');
        }

        // ตรวจสอบว่าเป็นคลังหลักหรือไม่
        if ($warehouse->is_main) {
            return redirect()->back()->with('error', 'ไม่สามารถลบคลังหลักได้');
        }

        $warehouse->delete();
        return redirect()->route('admin.warehouses.index')->with('success', 'คลังสินค้าถูกลบเรียบร้อยแล้ว');
    }

    /**
     * แสดงสต็อกสินค้าในคลัง
     */
    public function stock(Warehouse $warehouse, Request $request)
    {
        $query = $warehouse->warehouseProducts()->with(['product.category']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        // Filter by stock status
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'in_stock':
                    $query->where('quantity', '>', 0);
                    break;
                case 'low_stock':
                    $query->where('quantity', '>', 0)
                          ->where('quantity', '<=', 10); // สมมติว่าต่ำกว่า 10 คือ low stock
                    break;
                case 'out_of_stock':
                    $query->where('quantity', '<=', 0);
                    break;
            }
        }

        $stocks = $query->paginate(15);

        // Get categories for filter
        $categories = \App\Models\Category::where('is_active', true)->get();

        // Statistics
        $stats = [
            'total_products' => $warehouse->warehouseProducts()->count(),
            'total_quantity' => $warehouse->warehouseProducts()->sum('quantity'),
            'low_stock' => $warehouse->warehouseProducts()->where('quantity', '>', 0)->where('quantity', '<=', 10)->count(),
            'out_of_stock' => $warehouse->warehouseProducts()->where('quantity', '<=', 0)->count(),
        ];

        return view('admin.warehouses.stock', compact('warehouse', 'stocks', 'categories', 'stats'));
    }

    /**
     * ปรับปรุงสต็อกในคลัง
     */
    public function updateStock(Request $request, Warehouse $warehouse)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer|min:1',
            'location_code' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $product = \App\Models\Product::findOrFail($request->product_id);
            
            // อัปเดตสต็อกในคลัง
            $product->updateWarehouseStock(
                $warehouse->id,
                $request->quantity,
                $request->type,
                $request->notes,
                \Illuminate\Support\Facades\Auth::id()
            );

            $actionText = match($request->type) {
                'in' => 'เพิ่มสต็อก',
                'out' => 'ลดสต็อก',
                'adjustment' => 'ปรับปรุงสต็อก'
            };

            return redirect()->back()->with('success', "{$actionText} {$product->name} ในคลัง {$warehouse->name} เรียบร้อยแล้ว");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * เพิ่มสต็อกแบบ Quick Add
     */
    public function quickAddStock(Request $request, Warehouse $warehouse)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'ข้อมูลไม่ถูกต้อง']);
        }

        try {
            $product = \App\Models\Product::findOrFail($request->product_id);
            
            $product->updateWarehouseStock(
                $warehouse->id,
                $request->quantity,
                'in',
                $request->notes ?: 'เพิ่มสต็อกด่วน',
                \Illuminate\Support\Facades\Auth::id()
            );

            $warehouseProduct = $warehouse->warehouseProducts()
                                        ->where('product_id', $request->product_id)
                                        ->first();

            return response()->json([
                'success' => true,
                'message' => "เพิ่มสต็อก {$product->name} เรียบร้อยแล้ว",
                'new_quantity' => $warehouseProduct ? $warehouseProduct->quantity : 0
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * หน้าจัดการสต็อกแบบกลุ่ม
     */
    public function bulkStock(Warehouse $warehouse)
    {
        $products = \App\Models\Product::with(['category'])
                                     ->where('is_active', true)
                                     ->get()
                                     ->map(function($product) use ($warehouse) {
                                         $warehouseProduct = $warehouse->warehouseProducts()
                                                                      ->where('product_id', $product->id)
                                                                      ->first();
                                         return [
                                             'id' => $product->id,
                                             'name' => $product->name,
                                             'sku' => $product->sku,
                                             'unit' => $product->unit,
                                             'current_stock' => $warehouseProduct ? $warehouseProduct->quantity : 0
                                         ];
                                     });

        return view('admin.warehouses.bulk-stock', compact('warehouse', 'products'));
    }

    /**
     * ประมวลผลการจัดการสต็อกแบบกลุ่ม
     */
    public function bulkUpdateStock(Request $request, Warehouse $warehouse)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.type' => 'required|in:in,out,adjustment',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $results = [];
        $errors = [];

        try {
            \DB::beginTransaction();

            foreach ($request->items as $item) {
                try {
                    $product = \App\Models\Product::findOrFail($item['product_id']);
                    
                    // ตรวจสอบสต็อกเพียงพอสำหรับการลด
                    if ($item['type'] === 'out') {
                        $warehouseProduct = $warehouse->warehouseProducts()
                                                    ->where('product_id', $item['product_id'])
                                                    ->first();
                        $currentStock = $warehouseProduct ? $warehouseProduct->quantity : 0;
                        
                        if ($currentStock < $item['quantity']) {
                            throw new \Exception("สต็อก {$product->name} ไม่เพียงพอ (มีเพียง {$currentStock} {$product->unit})");
                        }
                    }

                    // อัปเดตสต็อก
                    $product->updateWarehouseStock(
                        $warehouse->id,
                        $item['quantity'],
                        $item['type'],
                        $item['notes'] ?: "การจัดการสต็อกแบบกลุ่ม",
                        \Illuminate\Support\Facades\Auth::id()
                    );

                    $actionText = match($item['type']) {
                        'in' => 'เพิ่มสต็อก',
                        'out' => 'ลดสต็อก',
                        'adjustment' => 'ปรับปรุงสต็อก'
                    };

                    $results[] = "{$actionText} {$product->name}: {$item['quantity']} {$product->unit}";

                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }

            if (count($errors) > 0) {
                \DB::rollBack();
                return redirect()->back()
                               ->withErrors(['bulk_errors' => $errors])
                               ->withInput();
            }

            \DB::commit();

            $successMessage = "ดำเนินการสำเร็จ " . count($results) . " รายการ:\n" . implode("\n", $results);
            
            return redirect()->route('admin.warehouses.stock', $warehouse)
                           ->with('success', $successMessage);

        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }
}
