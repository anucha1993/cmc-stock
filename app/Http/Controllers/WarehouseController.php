<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

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
        $warehouses = Warehouse::paginate(10);
        
        // Add real-time stock count to each warehouse
        $warehouses->getCollection()->transform(function($wh) {
            $wh->stock_count_items = $wh->getTotalStockCount();
            return $wh;
        });

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
            'address' => 'nullable|string',
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
            'address' => $request->address,
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
        // Load stock items with product and category info
        $stockItems = $warehouse->stockItems()
            ->where('status', 'available')
            ->with(['product.category'])
            ->get();
        
        return view('admin.warehouses.show', compact('warehouse', 'stockItems'));
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
            'address' => 'nullable|string',
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
            'address' => $request->address,
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
        try {
            // ตรวจสอบว่าเป็นคลังหลักหรือไม่
            if ($warehouse->is_main) {
                return redirect()->back()->with('error', 'ไม่สามารถลบคลังหลักได้');
            }

            // ตรวจสอบว่ามีข้อมูลที่เกี่ยวข้องหรือไม่
            $relatedData = [];
            
            if ($warehouse->stockItems()->exists()) {
                $relatedData[] = 'รายการสต็อก (' . $warehouse->stockItems()->count() . ' รายการ)';
            }
            
            if ($warehouse->warehouseProducts()->exists()) {
                $totalStock = $warehouse->warehouseProducts()->sum('quantity');
                if ($totalStock > 0) {
                    $relatedData[] = 'สต็อกสินค้า (' . number_format($totalStock) . ' หน่วย)';
                }
            }
            
            // ตรวจสอบ stock check sessions
            if ($warehouse->stockCheckSessions()->exists()) {
                $relatedData[] = 'เซสชั่นตรวจนับสต็อก (' . $warehouse->stockCheckSessions()->count() . ' ครั้ง)';
            }
            
            // ตรวจสอบ transfers
            $transfersFrom = \App\Models\Transfer::where('from_warehouse_id', $warehouse->id)->exists();
            $transfersTo = \App\Models\Transfer::where('to_warehouse_id', $warehouse->id)->exists();
            if ($transfersFrom || $transfersTo) {
                $count = \App\Models\Transfer::where('from_warehouse_id', $warehouse->id)
                    ->orWhere('to_warehouse_id', $warehouse->id)->count();
                $relatedData[] = 'การโยกย้ายสินค้า (' . $count . ' รายการ)';
            }
            
            // ตรวจสอบ production orders
            if ($warehouse->productionOrders()->exists()) {
                $relatedData[] = 'คำสั่งผลิต (' . $warehouse->productionOrders()->count() . ' รายการ)';
            }
            
            // ถ้ามีข้อมูลที่เกี่ยวข้อง ให้แสดงข้อความแจ้งเตือน
            if (!empty($relatedData)) {
                return redirect()->back()->with('error', 
                    'ไม่สามารถลบคลัง "' . $warehouse->name . '" ได้ เนื่องจากมีข้อมูลที่เกี่ยวข้อง: ' . 
                    implode(', ', $relatedData) . '<br>กรุณาลบข้อมูลที่เกี่ยวข้องก่อน หรือปิดการใช้งานคลังแทน'
                );
            }

            // ถ้าไม่มีข้อมูลที่เกี่ยวข้อง ก็ลบได้
            $warehouse->delete();
            return redirect()->route('admin.warehouses.index')->with('success', 'คลังสินค้าถูกลบเรียบร้อยแล้ว');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * แสดงสต็อกสินค้าในคลัง
     */
    public function stock(Warehouse $warehouse, Request $request)
    {
        $query = $warehouse->stockItems()
            ->where('status', 'available')
            ->with(['product.category']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        $stockItems = $query->get();

        // Group by product and calculate quantity
        $stocks = $stockItems->groupBy('product_id')->map(function($items) {
            $product = $items->first()->product;
            $quantity = $items->count();
            
            return (object)[
                'id' => $items->first()->id,
                'product_id' => $product->id,
                'product' => $product,
                'quantity' => $quantity,
                'location_code' => $items->first()->location_code,
                'updated_at' => $items->max('updated_at'),
                'status' => $quantity > 0 ? 'in_stock' : 'out_of_stock'
            ];
        })->values();

        // Apply stock status filter
        if ($request->filled('stock_status')) {
            $stocks = $stocks->filter(function($stock) use ($request) {
                switch ($request->stock_status) {
                    case 'in_stock':
                        return $stock->quantity > 0;
                    case 'low_stock':
                        return $stock->quantity > 0 && $stock->quantity <= 10;
                    case 'out_of_stock':
                        return $stock->quantity <= 0;
                }
                return true;
            });
        }

        // Paginate manually using LengthAwarePaginator so view can call ->total(), ->links(), etc.
        $page = (int) request()->get('page', 1);
        $perPage = 15;
        $total = $stocks->count();
        $itemsForPage = $stocks->forPage($page, $perPage)->values();
        $stocks = new LengthAwarePaginator($itemsForPage, $total, $perPage, $page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);

        // Get categories for filter
        $categories = \App\Models\Category::where('is_active', true)->get();

        // Statistics - real-time count
        $allStockItems = $warehouse->stockItems()->where('status', 'available')->get();
        $stats = [
            'total_products' => $allStockItems->groupBy('product_id')->count(),
            'total_quantity' => $allStockItems->count(),
            'low_stock' => $allStockItems->groupBy('product_id')->filter(function($items) {
                return $items->count() > 0 && $items->count() <= 10;
            })->count(),
            'out_of_stock' => 0 // No products actually out of stock since we filter by available
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
            DB::beginTransaction();

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
                DB::rollBack();
                return redirect()->back()
                               ->withErrors(['bulk_errors' => $errors])
                               ->withInput();
            }

            DB::commit();

            $successMessage = "ดำเนินการสำเร็จ " . count($results) . " รายการ:\n" . implode("\n", $results);
            
            return redirect()->route('admin.warehouses.stock', $warehouse)
                           ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }
}
