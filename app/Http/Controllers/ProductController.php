<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\BarcodeSequence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'supplier']);

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter by size type
        if ($request->filled('size_type')) {
            $query->where('size_type', $request->size_type);
        }

        // Filter by stock status
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'low_stock':
                    $query->whereColumn('stock_quantity', '<=', 'min_stock');
                    break;
                case 'out_of_stock':
                    $query->where('stock_quantity', 0);
                    break;
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $products = $query->latest()->paginate(15);
        $categories = Category::active()->get();
        $suppliers = Supplier::active()->get();

        return view('admin.products.index', compact('products', 'categories', 'suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::active()->get();
        $suppliers = Supplier::active()->get();
        return view('admin.products.create', compact('categories', 'suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:50|unique:products,sku',
            'barcode' => 'nullable|string|max:50|unique:products,barcode',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'unit' => 'required|string|max:50',
            'stock_quantity' => 'nullable|integer|min:0',
            'min_stock_quantity' => 'required|integer|min:0',
            'max_stock_quantity' => 'required|integer|min:0',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'size_type' => 'required|in:standard,custom',
            'custom_size_options' => 'nullable|json',
            'allow_custom_order' => 'boolean',
            'length' => 'nullable|numeric|min:0',
            'thickness' => 'nullable|numeric|min:0',
            'steel_type' => 'required|in:not_specified,wire_4,wire_5,wire_6,wire_7',
            'side_steel_type' => 'required|in:not_specified,no_side_steel,show_side_steel',
            'measurement_unit' => 'required|in:meter,centimeter,millimeter',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $product = Product::create([
            'name' => $request->name,
            'sku' => $request->sku ?: Product::generateSKU($request->name, $request->category_id),
            'barcode' => $request->barcode ?: BarcodeSequence::generateUniqueBarcode('product', 'CMC'),
            'description' => $request->description,
            'category_id' => $request->category_id,
            'supplier_id' => $request->supplier_id,
            'unit' => $request->unit,
            'stock_quantity' => $request->stock_quantity ?: 0,
            'min_stock' => $request->min_stock_quantity,
            'max_stock' => $request->max_stock_quantity,
            'location' => $request->location,
            'is_active' => $request->has('is_active'),
            'size_type' => $request->size_type,
            'custom_size_options' => $request->size_type === 'custom' ? $request->custom_size_options : null,
            'allow_custom_order' => $request->has('allow_custom_order'),
            'length' => $request->length,
            'thickness' => $request->thickness,
            'steel_type' => $request->steel_type,
            'side_steel_type' => $request->side_steel_type,
            'measurement_unit' => $request->measurement_unit,
        ]);

        return redirect()->route('admin.products.index')->with('success', 'สินค้าถูกสร้างเรียบร้อยแล้ว (Barcode: ' . $product->barcode . ')');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load([
            'category', 
            'supplier', 
            'stockItems' => function($query) {
                $query->with(['warehouse'])->orderBy('created_at', 'desc');
            },
            'warehouseProducts.warehouse',
            'inventoryTransactions' => function($query) {
                $query->latest()->take(10);
            }
        ]);
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $categories = Category::active()->get();
        $suppliers = Supplier::active()->get();
        return view('admin.products.edit', compact('product', 'categories', 'suppliers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:products,sku,' . $product->id,
            'barcode' => 'required|string|max:50|unique:products,barcode,' . $product->id,
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'unit' => 'required|string|max:50',
            'min_stock_quantity' => 'required|integer|min:0',
            'max_stock_quantity' => 'required|integer|min:0',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'size_type' => 'required|in:standard,custom',
            'custom_size_options' => 'nullable|json',
            'allow_custom_order' => 'boolean',
            'length' => 'nullable|numeric|min:0',
            'thickness' => 'nullable|numeric|min:0',
            'steel_type' => 'required|in:not_specified,wire_4,wire_5,wire_6,wire_7',
            'side_steel_type' => 'required|in:not_specified,no_side_steel,show_side_steel',
            'measurement_unit' => 'required|in:meter,centimeter,millimeter',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $product->update([
            'name' => $request->name,
            'sku' => $request->sku,
            'barcode' => $request->barcode,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'supplier_id' => $request->supplier_id,
            'unit' => $request->unit,
            'min_stock' => $request->min_stock_quantity,
            'max_stock' => $request->max_stock_quantity,
            'location' => $request->location,
            'is_active' => $request->has('is_active'),
            'size_type' => $request->size_type,
            'custom_size_options' => $request->size_type === 'custom' ? $request->custom_size_options : null,
            'allow_custom_order' => $request->has('allow_custom_order'),
            'length' => $request->length,
            'thickness' => $request->thickness,
            'steel_type' => $request->steel_type,
            'side_steel_type' => $request->side_steel_type,
            'measurement_unit' => $request->measurement_unit,
        ]);

        return redirect()->route('admin.products.index')->with('success', 'สินค้าถูกอัปเดตเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'สินค้าถูกลบเรียบร้อยแล้ว');
    }

    /**
     * Update stock quantity
     */
    public function updateStock(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:in,out,adjustment',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $warehouse = \App\Models\Warehouse::findOrFail($request->warehouse_id);
            $warehouseProduct = $warehouse->warehouseProducts()
                                        ->where('product_id', $product->id)
                                        ->first();

            $oldQuantity = $warehouseProduct ? $warehouseProduct->quantity : 0;

            // ตรวจสอบสต็อกเพียงพอสำหรับการลด
            if ($request->type === 'out' && $oldQuantity < $request->quantity) {
                return redirect()->back()->with('error', "สต็อกในคลัง {$warehouse->name} ไม่เพียงพอ (มีอยู่ {$oldQuantity} {$product->unit})");
            }

            // อัปเดตสต็อกในคลัง
            $product->updateWarehouseStock(
                $request->warehouse_id,
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

            return redirect()->back()->with('success', "{$actionText}ในคลัง {$warehouse->name} เรียบร้อยแล้ว");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Search product by barcode
     */
    public function searchByBarcode(Request $request)
    {
        $barcode = $request->get('barcode');
        $product = Product::where('barcode', $barcode)->with(['category', 'supplier'])->first();

        if ($product) {
            return response()->json([
                'success' => true,
                'product' => $product
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'ไม่พบสินค้าที่มี Barcode นี้'
        ]);
    }
}
