<?php

namespace App\Http\Controllers;

use App\Models\ProductionOrder;
use App\Models\ProductionOrderItem;
use App\Models\Product;
use App\Models\Package;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProductionOrderController extends Controller
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
        $query = ProductionOrder::with(['product', 'targetWarehouse', 'requestedBy']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
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

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Date range
        if ($request->filled('date_from')) {
            $query->whereDate('due_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('due_date', '<=', $request->date_to);
        }

        $orders = $query->orderBy('priority', 'desc')
                       ->orderBy('due_date', 'asc')
                       ->paginate(15);

        return view('admin.production-orders.index', compact('orders'));
    }

        /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::where('is_active', true)->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $packages = Package::where('is_active', true)->with('category')->get();
        
        return view('admin.production-orders.create', compact('products', 'warehouses', 'packages'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation rules based on order type
        $rules = [
            'order_type' => 'required|in:single,package,multiple',
            'target_warehouse_id' => 'required|exists:warehouses,id',
            'due_date' => 'required|date|after_or_equal:today',
            'priority' => 'required|in:low,normal,high,urgent',
            'description' => 'nullable|string|max:500',
        ];

        // Type-specific validation
        if ($request->order_type === 'single') {
            $rules['product_id'] = 'required|exists:products,id';
            $rules['quantity'] = 'required|integer|min:1';
        } elseif ($request->order_type === 'package') {
            $rules['package_id'] = 'required|exists:packages,id';
            $rules['quantity'] = 'required|integer|min:1';
        } elseif ($request->order_type === 'multiple') {
            $rules['items'] = 'required|array|min:1';
            $rules['items.*.product_id'] = 'required|exists:products,id';
            $rules['items.*.quantity'] = 'required|integer|min:1';
            $rules['items.*.unit_cost'] = 'nullable|numeric|min:0';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // สร้าง Production Order
            $orderData = [
                'order_code' => ProductionOrder::generateOrderCode(),
                'order_type' => $request->order_type,
                'target_warehouse_id' => $request->target_warehouse_id,
                'priority' => $request->priority,
                'status' => ProductionOrder::STATUS_PENDING,
                'due_date' => $request->due_date,
                'description' => $request->description,
                'requested_by' => Auth::id(),
                'requested_at' => now()
            ];

            if ($request->order_type === 'single') {
                $orderData['product_id'] = $request->product_id;
                $orderData['quantity'] = $request->quantity;
            } elseif ($request->order_type === 'package') {
                $orderData['package_id'] = $request->package_id;
                $orderData['quantity'] = $request->quantity;
            }

            $order = ProductionOrder::create($orderData);

            // สร้าง items ตามประเภท
            if ($request->order_type === 'package') {
                $order->createItemsFromPackage();
            } elseif ($request->order_type === 'multiple') {
                foreach ($request->items as $item) {
                    $order->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_cost' => $item['unit_cost'] ?? null,
                        'total_cost' => isset($item['unit_cost']) ? $item['quantity'] * $item['unit_cost'] : null,
                    ]);
                }
                // อัพเดท quantity รวม
                $order->update(['quantity' => collect($request->items)->sum('quantity')]);
            }

            DB::commit();

            return redirect()->route('admin.production-orders.index')
                           ->with('success', 'ใบสั่งผลิตถูกสร้างเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                           ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductionOrder $productionOrder)
    {
        $productionOrder->load([
            'product.category', 
            'targetWarehouse', 
            'requestedBy', 
            'approvedBy', 
            'assignedTo',
            'package',
            'items.product'
        ]);
        
        return view('admin.production-orders.show', compact('productionOrder'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductionOrder $productionOrder)
    {
        if (!in_array($productionOrder->status, ['pending', 'in_production'])) {
            return redirect()->route('admin.production-orders.show', $productionOrder)
                           ->with('error', 'ไม่สามารถแก้ไขใบสั่งผลิตที่เสร็จสิ้นแล้ว');
        }

        $products = Product::where('is_active', true)->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $packages = Package::where('is_active', true)->get();
        
        return view('admin.production-orders.edit', compact('productionOrder', 'products', 'warehouses', 'packages'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductionOrder $productionOrder)
    {
        if (!in_array($productionOrder->status, ['pending', 'in_production'])) {
            return redirect()->route('admin.production-orders.show', $productionOrder)
                           ->with('error', 'ไม่สามารถแก้ไขใบสั่งผลิตที่เสร็จสิ้นแล้ว');
        }

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1|min:' . $productionOrder->produced_quantity,
            'due_date' => 'required|date|after_or_equal:today',
            'priority' => 'required|in:low,normal,high,urgent',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $productionOrder->update([
            'quantity' => $request->quantity,
            'due_date' => $request->due_date,
            'priority' => $request->priority,
            'notes' => $request->notes,
        ]);

        return redirect()->route('admin.production-orders.show', $productionOrder)
                       ->with('success', 'ใบสั่งผลิตถูกอัปเดตเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductionOrder $productionOrder)
    {
        if ($productionOrder->status !== 'pending') {
            return redirect()->back()->with('error', 'ไม่สามารถลบใบสั่งผลิตที่ดำเนินการแล้ว');
        }

        $productionOrder->delete();

        return redirect()->route('admin.production-orders.index')
                       ->with('success', 'ใบสั่งผลิตถูกลบเรียบร้อยแล้ว');
    }

    /**
     * เริ่มผลิต
     */
    public function start(ProductionOrder $productionOrder)
    {
        if ($productionOrder->status !== 'pending') {
            return redirect()->back()->with('error', 'ใบสั่งผลิตนี้ดำเนินการไปแล้ว');
        }

        $productionOrder->update([
            'status' => 'in_production',
            'start_date' => now(),
            'assigned_to' => Auth::id()
        ]);

        return redirect()->route('admin.production-orders.show', $productionOrder)
                       ->with('success', 'เริ่มกระบวนการผลิตแล้ว');
    }

    /**
     * อัปเดตสถานะการผลิต
     */
    public function updateStatus(Request $request, ProductionOrder $productionOrder)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,in_production,completed,cancelled',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $updateData = ['status' => $request->status];
            
            // อัพเดทข้อมูลตามสถานะ
            switch ($request->status) {
                case 'in_production':
                    $updateData['start_date'] = now();
                    $updateData['assigned_to'] = Auth::id();
                    break;
                    
                case 'completed':
                    $updateData['completion_date'] = now();
                    break;
                    
                case 'cancelled':
                    // ไม่ต้องอัพเดทอะไรเพิ่ม
                    break;
            }
            
            // เพิ่มหมายเหตุถ้ามี
            if ($request->notes) {
                $existingNotes = $productionOrder->notes ? $productionOrder->notes . "\n" : '';
                $updateData['notes'] = $existingNotes . now()->format('Y-m-d H:i') . ": " . $request->notes;
            }
            
            $productionOrder->update($updateData);

            DB::commit();

            $statusTexts = [
                'pending' => 'รอดำเนินการ',
                'in_production' => 'กำลังผลิต', 
                'completed' => 'เสร็จแล้ว',
                'cancelled' => 'ยกเลิก'
            ];

            return redirect()->route('admin.production-orders.show', $productionOrder)
                           ->with('success', 'เปลี่ยนสถานะเป็น "' . $statusTexts[$request->status] . '" เรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * ยกเลิกใบสั่งผลิต
     */
    public function cancel(ProductionOrder $productionOrder)
    {
        if (!in_array($productionOrder->status, ['pending', 'in_production'])) {
            return redirect()->back()->with('error', 'ไม่สามารถยกเลิกใบสั่งผลิตที่เสร็จสิ้นแล้ว');
        }

        $productionOrder->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => Auth::id()
        ]);

        return redirect()->route('admin.production-orders.show', $productionOrder)
                       ->with('success', 'ใบสั่งผลิตถูกยกเลิกเรียบร้อยแล้ว');
    }

    /**
     * บันทึกจำนวนที่ผลิตจริง (สำหรับสถานะเสร็จแล้ว)
     */
    public function updateProducedQuantity(Request $request, ProductionOrder $productionOrder)
    {
        if ($productionOrder->status !== 'completed') {
            return redirect()->back()->with('error', 'สามารถบันทึกจำนวนที่ผลิตจริงได้เฉพาะใบสั่งที่เสร็จแล้วเท่านั้น');
        }

        $validator = Validator::make($request->all(), [
            'produced_quantity' => 'required|integer|min:0|max:' . $productionOrder->quantity,
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $updateData = ['produced_quantity' => $request->produced_quantity];
            
            // เพิ่มหมายเหตุถ้ามี
            if ($request->notes) {
                $existingNotes = $productionOrder->notes ? $productionOrder->notes . "\n" : '';
                $updateData['notes'] = $existingNotes . now()->format('Y-m-d H:i') . ": จำนวนที่ผลิตจริง " . number_format($request->produced_quantity) . " " . ($request->notes ? "- " . $request->notes : "");
            }
            
            $productionOrder->update($updateData);

            // อัพเดทสต๊อกตามที่ผลิตจริง
            $this->updateStockAfterProduction($productionOrder, $request->produced_quantity);

            DB::commit();

            return redirect()->route('admin.production-orders.show', $productionOrder)
                           ->with('success', 'บันทึกจำนวนที่ผลิตจริง: ' . number_format($request->produced_quantity) . ' รายการ');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * แสดงรายงานการผลิต
     */
    public function report(Request $request)
    {
        $query = ProductionOrder::with(['product', 'targetWarehouse']);

        // Date range
        $dateFrom = $request->date_from ?: now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?: now()->endOfMonth()->format('Y-m-d');
        
        $query->whereDate('order_date', '>=', $dateFrom)
              ->whereDate('order_date', '<=', $dateTo);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->get();

        // สถิติ
        $stats = [
            'total_orders' => $orders->count(),
            'completed_orders' => $orders->where('status', 'completed')->count(),
            'pending_orders' => $orders->where('status', 'pending')->count(),
            'in_progress_orders' => $orders->where('status', 'in_production')->count(),
            'cancelled_orders' => $orders->where('status', 'cancelled')->count(),
            'total_quantity' => $orders->where('status', 'completed')->sum('quantity'),
            'avg_completion_time' => $orders->where('status', 'completed')
                                           ->filter(function($order) {
                                               return $order->started_at && $order->completed_at;
                                           })
                                           ->avg(function($order) {
                                               return $order->started_at->diffInHours($order->completed_at);
                                           }),
        ];

        return view('admin.production-orders.report', compact('orders', 'stats', 'dateFrom', 'dateTo'));
    }

    /**
     * แสดงแดชบอร์ดการผลิต
     */
    public function dashboard()
    {
        $today = now();
        
        // ใบสั่งผลิตที่ต้องเริ่มวันนี้
        $todayOrders = ProductionOrder::where('due_date', $today->format('Y-m-d'))
                                     ->where('status', 'pending')
                                     ->orderBy('priority', 'desc')
                                     ->get();

        // ใบสั่งผลิตที่กำลังดำเนินการ
        $inProgressOrders = ProductionOrder::where('status', 'in_production')
                                          ->orderBy('priority', 'desc')
                                          ->orderBy('due_date', 'asc')
                                          ->get();

        // ใบสั่งผลิตที่เลยกำหนด
        $overdueOrders = ProductionOrder::where('due_date', '<', $today->format('Y-m-d'))
                                       ->whereIn('status', ['pending', 'in_production'])
                                       ->orderBy('due_date', 'asc')
                                       ->get();

        // สถิติสัปดาห์นี้
        $weekStats = [
            'completed_this_week' => ProductionOrder::where('status', 'completed')
                                                   ->whereBetween('completed_at', [
                                                       $today->startOfWeek(),
                                                       $today->endOfWeek()
                                                   ])->count(),
            'pending_urgent' => ProductionOrder::where('status', 'pending')
                                              ->where('priority', 'urgent')
                                              ->count(),
            'avg_progress' => ProductionOrder::where('status', 'in_production')
                                            ->avg('progress') ?: 0,
        ];

        return view('admin.production-orders.dashboard', compact(
            'todayOrders', 
            'inProgressOrders', 
            'overdueOrders', 
            'weekStats'
        ));
    }

    /**
     * API: ดึงข้อมูลสินค้าในแพ
     */
    public function getPackageProducts($packageId)
    {
        $package = Package::with(['packageProducts.product'])->find($packageId);
        
        if (!$package) {
            return response()->json(['error' => 'Package not found'], 404);
        }

        $products = $package->packageProducts->map(function ($packageProduct) use ($package) {
            return [
                'product_id' => $packageProduct->product_id,
                'product_name' => $packageProduct->product->name,
                'product_sku' => $packageProduct->product->sku,
                'quantity_per_package' => $packageProduct->quantity_per_package,
                'unit' => $packageProduct->unit,
                'unit_cost' => $packageProduct->cost_per_unit,
                'specifications' => $packageProduct->specifications
            ];
        });

        return response()->json([
            'package' => [
                'id' => $package->id,
                'name' => $package->name,
                'code' => $package->code
            ],
            'products' => $products
        ]);
    }

    /**
     * API: ค้นหาสินค้า
     */
    public function searchProducts(Request $request)
    {
        $query = $request->get('q', '');
        
        $products = Product::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%");
            })
            ->limit(20)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'unit' => $product->unit,
                    'cost' => $product->cost_price
                ];
            });

        return response()->json($products);
    }

    /**
     * อัพเดทสต๊อกหลังจากผลิตเสร็จ
     */
    private function updateStockAfterProduction(ProductionOrder $productionOrder, int $producedQuantity)
    {
        Log::info("UpdateStock: Starting with producedQuantity={$producedQuantity}, orderType={$productionOrder->order_type}");
        
        if ($producedQuantity <= 0) {
            Log::info("UpdateStock: Skipping - producedQuantity is 0 or less");
            return;
        }

        // ถ้าเป็นการสั่งผลิตแบบ package หรือ multiple items
        if (in_array($productionOrder->order_type, ['package', 'multiple'])) {
            Log::info("UpdateStock: Processing package/multiple items");
            foreach ($productionOrder->items as $item) {
                // คำนวณสัดส่วนที่ผลิตจริงสำหรับแต่ละ item
                $ratio = $producedQuantity / $productionOrder->quantity;
                $itemProducedQty = floor($item->quantity * $ratio);
                
                Log::info("UpdateStock: Item {$item->product_id} - quantity={$item->quantity}, ratio={$ratio}, itemProducedQty={$itemProducedQty}");
                
                if ($itemProducedQty > 0) {
                    try {
                        Log::info("UpdateStock: Adding stock for product {$item->product_id}, warehouse {$productionOrder->target_warehouse_id}, qty {$itemProducedQty}");
                        $this->addToStock($item->product_id, $productionOrder->target_warehouse_id, $itemProducedQty, 'production', $productionOrder->id);
                        Log::info("UpdateStock: Stock added successfully");
                    } catch (\Exception $e) {
                        Log::error("UpdateStock: Error adding stock - " . $e->getMessage());
                        throw $e;
                    }
                }
                
                // อัพเดท produced_quantity ของ item
                try {
                    Log::info("UpdateStock: Updating item produced_quantity to {$itemProducedQty}");
                    $item->update(['produced_quantity' => $itemProducedQty]);
                    Log::info("UpdateStock: Item updated successfully");
                } catch (\Exception $e) {
                    Log::error("UpdateStock: Error updating item - " . $e->getMessage());
                    throw $e;
                }
            }
        } else {
            // ถ้าเป็นการสั่งผลิตสินค้าเดี่ยว
            Log::info("UpdateStock: Processing single item");
            if ($productionOrder->product_id) {
                Log::info("UpdateStock: Adding stock for product {$productionOrder->product_id}, quantity={$producedQuantity}");
                $this->addToStock($productionOrder->product_id, $productionOrder->target_warehouse_id, $producedQuantity, 'production', $productionOrder->id);
            }
        }
        
        Log::info("UpdateStock: Completed");
    }

    /**
     * เพิ่มสต๊อกสินค้า - สร้าง StockItem แยกแต่ละชิ้น
     */
    private function addToStock(int $productId, int $warehouseId, int $quantity, string $type, int $referenceId)
    {
        Log::info("AddToStock: Creating {$quantity} individual StockItems for product {$productId}");
        
        $product = \App\Models\Product::find($productId);
        $warehouse = \App\Models\Warehouse::find($warehouseId);
        
        if (!$product || !$warehouse) {
            throw new \Exception("Product or Warehouse not found");
        }

        // สร้าง StockItem แยกแต่ละชิ้น
        $createdItems = [];
        for ($i = 1; $i <= $quantity; $i++) {
            try {
                // สร้าง Barcode เฉพาะสำหรับแต่ละชิ้น
                $barcode = $this->generateUniqueBarcode($product);
                
                $stockItem = \App\Models\StockItem::create([
                    'product_id' => $productId,
                    'warehouse_id' => $warehouseId,
                    'barcode' => $barcode,
                    'serial_number' => null, // อาจใส่ S/N ภายหลัง
                    'status' => 'available',
                    'cost_price' => $product->cost_price,
                    'notes' => "ผลิตจากใบสั่ง #{$referenceId}",
                    'received_date' => now(),
                    'location_code' => null
                ]);
                
                $createdItems[] = $stockItem;
                Log::info("AddToStock: Created StockItem ID {$stockItem->id} with barcode {$barcode}");
                
            } catch (\Exception $e) {
                Log::error("AddToStock: Error creating StockItem #{$i}: " . $e->getMessage());
                throw $e;
            }
        }

        // อัปเดต WarehouseProduct สำหรับ aggregate count
        $warehouseProduct = \App\Models\WarehouseProduct::firstOrCreate([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId
        ], [
            'quantity' => 0,
            'reserved_quantity' => 0,
            'available_quantity' => 0
        ]);

        $beforeQuantity = (int) $warehouseProduct->quantity;
        $warehouseProduct->increment('quantity', $quantity);
        $warehouseProduct->refresh();

        // สร้าง InventoryTransaction
        $transactionCode = \App\Models\InventoryTransaction::generateTransactionCode();

        \App\Models\InventoryTransaction::create([
            'transaction_code' => $transactionCode,
            'product_id' => $productId,
            'type' => 'in',
            'quantity' => $quantity,
            'unit_cost' => $product->cost_price,
            'total_cost' => $product->cost_price * $quantity,
            'before_quantity' => $beforeQuantity,
            'after_quantity' => (int) $warehouseProduct->quantity,
            'notes' => "ผลิตเสร็จแล้ว - สร้าง {$quantity} รายการ StockItem",
            'reference_type' => 'production_order',
            'reference_id' => $referenceId,
            'user_id' => Auth::id(),
            'transaction_date' => now()
        ]);

        Log::info("AddToStock: Successfully created {$quantity} StockItems and updated WarehouseProduct");
        return $createdItems;
    }

    /**
     * สร้าง Barcode เฉพาะสำหรับแต่ละชิ้น
     */
    private function generateUniqueBarcode(\App\Models\Product $product): string
    {
        $baseBarcode = $product->barcode;
        $counter = 1;
        
        // ใช้รูปแบบ: ProductBarcode-XXXXX (เช่น CM_00000001-00001)
        do {
            $barcode = $baseBarcode . '-' . str_pad($counter, 5, '0', STR_PAD_LEFT);
            $exists = \App\Models\StockItem::where('barcode', $barcode)->exists();
            $counter++;
        } while ($exists && $counter < 100000); // ป้องกัน infinite loop
        
        if ($counter >= 100000) {
            throw new \Exception("Cannot generate unique barcode for product {$product->id}");
        }
        
        return $barcode;
    }
}
