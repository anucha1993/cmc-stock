<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\PackageProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PackageController extends Controller
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
        $query = Package::with(['supplier', 'category'])
                        ->withCount('products');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $packages = $query->orderBy('sort_order')
                         ->orderBy('name')
                         ->paginate(15);

        $suppliers = Supplier::active()->get();
        $categories = Category::active()->get();

        // Statistics
        $stats = [
            'total_packages' => Package::count(),
            'active_packages' => Package::where('is_active', true)->count(),
            'inactive_packages' => Package::where('is_active', false)->count(),
            'total_products' => PackageProduct::distinct('product_id')->count(),
        ];

        return view('admin.packages.index', compact('packages', 'suppliers', 'categories', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = Supplier::active()->get();
        $categories = Category::active()->get();
        $products = Product::active()->with('category')->get();

        return view('admin.packages.create', compact('suppliers', 'categories', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'package_quantity' => 'required|integer|min:1',
            'length_per_package' => 'nullable|numeric|min:0',
            'length_unit' => 'required|string|max:20',
            'items_per_package' => 'required|integer|min:1',
            'item_unit' => 'required|string|max:20',
            'weight_per_package' => 'nullable|numeric|min:0',
            'weight_unit' => 'required|string|max:20',
            'selling_price_per_package' => 'nullable|numeric|min:0',
            'color' => 'nullable|string|max:7',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity_per_package' => 'required|integer|min:1',
            'products.*.unit' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // หาหมวดหมู่จากสินค้าแรก (สินค้าหลัก)
            $firstProduct = \App\Models\Product::find($request->products[0]['product_id']);
            $categoryId = $firstProduct ? $firstProduct->category_id : null;
            
            // คำนวณ sort_order อัตโนมัติ
            $maxSortOrder = Package::max('sort_order') ?? 0;

            $package = Package::create([
                'name' => $request->name,
                'code' => Package::generateCode(),
                'description' => $request->description,
                'package_quantity' => $request->package_quantity,
                'length_per_package' => $request->length_per_package,
                'length_unit' => $request->length_unit,
                'items_per_package' => $request->items_per_package,
                'item_unit' => $request->item_unit,
                'weight_per_package' => $request->weight_per_package,
                'weight_unit' => $request->weight_unit,
                'cost_per_package' => null, // ไม่ใช้ต้นทุนต่อแพ
                'selling_price_per_package' => $request->selling_price_per_package,
                'color' => $request->color ?: '#007bff',
                'sort_order' => $maxSortOrder + 1, // อัตโนมัติ
                'is_active' => $request->has('is_active'),
                'supplier_id' => $request->supplier_id,
                'category_id' => $categoryId, // จากสินค้าแรก
            ]);

            // เพิ่มสินค้าในแพ
            foreach ($request->products as $index => $productData) {
                $product = \App\Models\Product::find($productData['product_id']);
                
                PackageProduct::create([
                    'package_id' => $package->id,
                    'product_id' => $productData['product_id'],
                    'quantity_per_package' => $productData['quantity_per_package'],
                    'unit' => $product->unit, // ใช้หน่วยจากสินค้า
                    'length_per_unit' => null,
                    'weight_per_unit' => null,
                    'cost_per_unit' => null,
                    'selling_price_per_unit' => null,
                    'grade' => null,
                    'size' => null,
                    'specifications' => null,
                    'sort_order' => $index,
                    'is_main_product' => false, // ไม่ใช้ระบบสินค้าหลัก
                ]);
            }

            DB::commit();

            return redirect()->route('admin.packages.index')
                           ->with('success', 'แพสินค้าถูกสร้างเรียบร้อยแล้ว');

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
    public function show(Package $package)
    {
        $package->load(['supplier', 'category', 'products.category', 'packageProducts.product']);

        // คำนวณสถิติ
        $stats = [
            'total_products' => $package->products->count(),
            'total_quantity' => $package->calculateTotalProducts(),
            'total_weight' => $package->calculateTotalWeight(),
            'total_cost' => $package->calculateTotalCost(),
            'total_selling_price' => $package->calculateTotalSellingPrice(),
            'profit_margin' => $package->calculateTotalSellingPrice() > 0 ? 
                             (($package->calculateTotalSellingPrice() - $package->calculateTotalCost()) / $package->calculateTotalSellingPrice()) * 100 : 0,
        ];

        return view('admin.packages.show', compact('package', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Package $package)
    {
        $package->load(['products', 'packageProducts.product']);
        $suppliers = Supplier::active()->get();
        $categories = Category::active()->get();
        $products = Product::active()->with('category')->get();

        return view('admin.packages.edit', compact('package', 'suppliers', 'categories', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Package $package)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:packages,code,' . $package->id,
            'description' => 'nullable|string',
            'package_quantity' => 'required|integer|min:1',
            'length_per_package' => 'nullable|numeric|min:0',
            'length_unit' => 'required|string|max:20',
            'items_per_package' => 'required|integer|min:1',
            'item_unit' => 'required|string|max:20',
            'weight_per_package' => 'nullable|numeric|min:0',
            'weight_unit' => 'required|string|max:20',
            'selling_price_per_package' => 'nullable|numeric|min:0',
            'color' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'category_id' => 'nullable|exists:categories,id',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity_per_package' => 'required|numeric|min:0.01',
            'products.*.specifications' => 'nullable|string',
            'products.*.is_main_product' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            Log::error('Package Update Validation Failed:', [
                'errors' => $validator->errors()->toArray(),
                'input' => $request->all()
            ]);
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Debug: ตรวจสอบข้อมูลที่ส่งมา
        Log::info('Package Update Data:', [
            'request_data' => $request->all(),
            'package_id' => $package->id,
            'products_count' => count($request->products ?? [])
        ]);

        try {
            DB::beginTransaction();

            $package->update([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'package_quantity' => $request->package_quantity,
                'length_per_package' => $request->length_per_package,
                'length_unit' => $request->length_unit,
                'items_per_package' => $request->items_per_package,
                'item_unit' => $request->item_unit,
                'weight_per_package' => $request->weight_per_package,
                'weight_unit' => $request->weight_unit,
                'cost_per_package' => null,
                'selling_price_per_package' => $request->selling_price_per_package,
                'color' => $request->color ?: '#007bff',
                'sort_order' => $request->sort_order ?: 0,
                'is_active' => $request->has('is_active'),
                'supplier_id' => $request->supplier_id,
                'category_id' => $request->category_id,
            ]);

            // ลบสินค้าเดิมในแพ
            $package->packageProducts()->delete();

            // เพิ่มสินค้าใหม่ในแพ
            foreach ($request->products as $index => $productData) {
                $product = \App\Models\Product::find($productData['product_id']);
                
                PackageProduct::create([
                    'package_id' => $package->id,
                    'product_id' => $productData['product_id'],
                    'quantity_per_package' => $productData['quantity_per_package'],
                    'unit' => $product->unit, // ใช้หน่วยจากสินค้าโดยตรง
                    'length_per_unit' => null,
                    'weight_per_unit' => null,
                    'cost_per_unit' => null,
                    'selling_price_per_unit' => null,
                    'grade' => null,
                    'size' => null,
                    'specifications' => $productData['specifications'] ?? null,
                    'sort_order' => $index,
                    'is_main_product' => !empty($productData['is_main_product']),
                ]);
            }

            DB::commit();

            return redirect()->route('admin.packages.show', $package)
                           ->with('success', 'แพสินค้าถูกอัปเดตเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                           ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Package $package)
    {
        try {
            DB::beginTransaction();

            // ลบสินค้าในแพ
            $package->packageProducts()->delete();
            
            // ลบแพ
            $package->delete();

            DB::commit();

            return redirect()->route('admin.packages.index')
                           ->with('success', 'แพสินค้าถูกลบเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                           ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * แสดงหน้าสำหรับนำเข้าแพไปคลัง
     */
    public function import(Package $package)
    {
        $warehouses = Warehouse::active()->get();
        
        return view('admin.packages.import', compact('package', 'warehouses'));
    }

    /**
     * นำเข้าแพไปคลัง
     */
    public function processImport(Request $request, Package $package)
    {
        $validator = Validator::make($request->all(), [
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $warehouse = Warehouse::find($request->warehouse_id);
            $quantity = $request->quantity;
            $notes = $request->notes;

            $results = [];

            // นำเข้าสินค้าแต่ละรายการในแพ
            foreach ($package->getProductList() as $item) {
                $product = $item['product'];
                $importQuantity = $item['quantity'] * $quantity; // คูณด้วยจำนวนแพที่นำเข้า

                // อัปเดตสต็อกในคลัง
                $warehouseProduct = $warehouse->warehouseProducts()
                                             ->where('product_id', $product->id)
                                             ->first();

                if ($warehouseProduct) {
                    $oldQuantity = $warehouseProduct->quantity;
                    $warehouseProduct->increment('quantity', $importQuantity);
                    $warehouseProduct->increment('available_quantity', $importQuantity);
                } else {
                    $oldQuantity = 0;
                    $warehouseProduct = $warehouse->warehouseProducts()->create([
                        'product_id' => $product->id,
                        'quantity' => $importQuantity,
                        'available_quantity' => $importQuantity,
                        'reserved_quantity' => 0,
                    ]);
                }

                // สร้าง inventory transaction
                $product->inventoryTransactions()->create([
                    'transaction_code' => \App\Models\InventoryTransaction::generateTransactionCode(),
                    'type' => 'import_package',
                    'quantity' => $importQuantity,
                    'before_quantity' => $oldQuantity,
                    'after_quantity' => $oldQuantity + $importQuantity,
                    'notes' => "นำเข้าจากแพ: {$package->name} ({$package->code}) จำนวน {$quantity} แพ" . ($notes ? " - {$notes}" : ""),
                    'reference_type' => 'package',
                    'reference_id' => $package->id,
                    'user_id' => Auth::id(),
                    'transaction_date' => now()
                ]);

                $results[] = [
                    'product' => $product,
                    'quantity' => $importQuantity,
                    'unit' => $item['unit'],
                    'success' => true
                ];
            }

            DB::commit();

            return redirect()->route('admin.packages.show', $package)
                           ->with('success', "นำเข้าแพ {$package->name} จำนวน {$quantity} แพ ไปคลัง {$warehouse->name} เรียบร้อยแล้ว")
                           ->with('import_results', $results);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                           ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * คัดลอกแพ
     */
    public function duplicate(Package $package)
    {
        try {
            DB::beginTransaction();

            $newPackage = Package::create([
                'name' => $package->name . ' (Copy)',
                'code' => Package::generateCode(),
                'description' => $package->description,
                'package_quantity' => $package->package_quantity,
                'length_per_package' => $package->length_per_package,
                'length_unit' => $package->length_unit,
                'items_per_package' => $package->items_per_package,
                'item_unit' => $package->item_unit,
                'weight_per_package' => $package->weight_per_package,
                'weight_unit' => $package->weight_unit,
                'cost_per_package' => $package->cost_per_package,
                'selling_price_per_package' => $package->selling_price_per_package,
                'color' => $package->color,
                'sort_order' => $package->sort_order,
                'is_active' => false, // ปิดใช้งานไว้ก่อน
                'supplier_id' => $package->supplier_id,
                'category_id' => $package->category_id,
            ]);

            // คัดลอกสินค้าในแพ
            foreach ($package->packageProducts as $packageProduct) {
                PackageProduct::create([
                    'package_id' => $newPackage->id,
                    'product_id' => $packageProduct->product_id,
                    'quantity_per_package' => $packageProduct->quantity_per_package,
                    'unit' => $packageProduct->unit,
                    'length_per_unit' => $packageProduct->length_per_unit,
                    'weight_per_unit' => $packageProduct->weight_per_unit,
                    'cost_per_unit' => $packageProduct->cost_per_unit,
                    'selling_price_per_unit' => $packageProduct->selling_price_per_unit,
                    'grade' => $packageProduct->grade,
                    'size' => $packageProduct->size,
                    'specifications' => $packageProduct->specifications,
                    'sort_order' => $packageProduct->sort_order,
                    'is_main_product' => $packageProduct->is_main_product,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.packages.edit', $newPackage)
                           ->with('success', 'แพถูกคัดลอกเรียบร้อยแล้ว กรุณาปรับแต่งข้อมูลและเปิดใช้งาน');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                           ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * รายงานแพ
     */
    public function report(Request $request)
    {
        $query = Package::with(['supplier', 'category'])->withCount('products');

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $packages = $query->get();

        // สถิติ
        $stats = [
            'total_packages' => $packages->count(),
            'active_packages' => $packages->where('is_active', true)->count(),
            'total_package_value' => $packages->sum(function($package) {
                return $package->calculateTotalSellingPrice();
            }),
            'total_cost_value' => $packages->sum(function($package) {
                return $package->calculateTotalCost();
            }),
        ];

        $suppliers = Supplier::active()->get();
        $categories = Category::active()->get();

        return view('admin.packages.report', compact('packages', 'stats', 'suppliers', 'categories'));
    }
}
