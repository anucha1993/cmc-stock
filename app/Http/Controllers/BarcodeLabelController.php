<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockItem;
use Illuminate\Http\Request;

class BarcodeLabelController extends Controller
{
    /**
     * แสดงหน้าเลือกสินค้าสำหรับพิมพ์ label
     */
    public function index()
    {
        // Show only products that have StockItems (individual items with barcodes)
        $products = Product::with([
            'category',
            'stockItems' => function($query) {
                $query->where('status', '!=', 'sold');
            }
        ])
        ->whereHas('stockItems', function($query) {
            $query->where('status', '!=', 'sold');
        })
        ->where('is_active', true)
        ->orderBy('name')
        ->get();

        return view('admin.barcode-labels.index', compact('products'));
    }

    /**
     * แสดงรายการ Stock Items ของสินค้าที่เลือก
     */
    public function show(Product $product)
    {
        $stockItems = $product->stockItems()
            ->with(['warehouse'])
            ->where('status', '!=', 'sold')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.barcode-labels.show', compact('product', 'stockItems'));
    }

    /**
     * พิมพ์ label ของ Stock Items ที่เลือก
     */
    public function print(Request $request)
    {
        $request->validate([
            'stock_item_ids' => 'required|array|min:1',
            'stock_item_ids.*' => 'exists:stock_items,id',
            'label_size' => 'required|in:small,medium,large',
            'copies_per_item' => 'required|integer|min:1|max:10',
        ]);

        $stockItems = StockItem::with(['product', 'warehouse'])
            ->whereIn('id', $request->stock_item_ids)
            ->orderBy('id')
            ->get();

        $labelSize = $request->label_size;
        $copiesPerItem = $request->copies_per_item;

        return view('admin.barcode-labels.print', compact('stockItems', 'labelSize', 'copiesPerItem'));
    }

    /**
     * API: ได้รายการ Stock Items ของสินค้า
     */
    public function getStockItems(Product $product)
    {
        $stockItems = $product->stockItems()
            ->with(['warehouse'])
            ->where('status', '!=', 'sold')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'barcode' => $item->barcode,
                    'serial_number' => $item->serial_number,
                    'warehouse_name' => $item->warehouse->name,
                    'status' => $item->status_text,
                    'status_color' => $item->status_color,
                    'location_code' => $item->location_code,
                    'received_date' => $item->received_date ? $item->received_date->format('d/m/Y') : null,
                ];
            });

        return response()->json($stockItems);
    }

    /**
     * พิมพ์ label แบบ product-level (เมื่อไม่มี StockItem แต่มีสต๊อกเป็นจำนวน)
     * รับค่า: product_id, total_labels (จำนวนป้ายที่ต้องการพิมพ์)
     */
    public function printProduct(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'total_labels' => 'required|integer|min:1|max:1000',
            'label_size' => 'required|in:small,medium,large',
            'copies_per_item' => 'required|integer|min:1|max:10',
        ]);

        $product = Product::findOrFail($request->product_id);

        // Create synthetic stock-like items to render in the print view
        $total = (int) $request->total_labels;
        $items = collect();

        // choose a representative warehouse if exists
        $warehouseProduct = $product->warehouseProducts()->where('quantity', '>', 0)->first();
        $warehouse = $warehouseProduct ? \App\Models\Warehouse::find($warehouseProduct->warehouse_id) : null;

        for ($i = 0; $i < $total; $i++) {
            $obj = new \stdClass();
            $obj->id = 'product-' . $product->id . '-' . ($i+1);
            $obj->barcode = $product->barcode;
            $obj->serial_number = null;
            $obj->warehouse = $warehouse;
            $obj->status_text = 'product-level';
            $obj->status_color = 'info';
            $obj->location_code = null;
            $obj->received_date = null;
            $items->push($obj);
        }

        $labelSize = $request->label_size;
        $copiesPerItem = $request->copies_per_item;

        return view('admin.barcode-labels.print', [
            'stockItems' => $items,
            'labelSize' => $labelSize,
            'copiesPerItem' => $copiesPerItem,
        ]);
    }
}