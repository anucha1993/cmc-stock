<?php

namespace App\Http\Controllers;

use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\Product;
use App\Models\StockItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeliveryNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DeliveryNote::with(['creator', 'items.product']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('delivery_number', 'like', "%{$search}%")
                  ->orWhere('sales_order_number', 'like', "%{$search}%")
                  ->orWhere('quotation_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('delivery_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('delivery_date', '<=', $request->end_date);
        }

        $deliveryNotes = $query->latest()->paginate(20);

        return view('admin.delivery-notes.index', compact('deliveryNotes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // แสดงเฉพาะสินค้าที่มี StockItem สถานะ available
        $products = Product::active()
            ->with('category')
            ->whereHas('stockItems', function ($q) {
                $q->where('status', 'available');
            })
            ->withCount(['stockItems as available_stock' => function ($q) {
                $q->where('status', 'available');
            }])
            ->get();

        // คำนวณจำนวนจอง + จำนวนขายได้จริง
        $products->each(function ($product) {
            $product->reserved_count = Product::getReservedStock($product->id);
            $product->real_available = max(0, $product->available_stock - $product->reserved_count);
        });
        
        return view('admin.delivery-notes.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sales_order_number' => 'nullable|string|max:100',
            'quotation_number' => 'nullable|string|max:100',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'delivery_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // ตรวจสอบจำนวนไม่เกินสต็อกจริง (available - reserved)
        $stockErrors = [];
        // รวมจำนวนสินค้าเดียวกันที่อยู่หลายแถว
        $itemsByProduct = collect($request->items)->groupBy('product_id');
        
        foreach ($itemsByProduct as $productId => $rows) {
            $totalQty = $rows->sum('quantity');
            $product = Product::find($productId);
            if (!$product) continue;

            $availableCount = $product->stockItems()->where('status', 'available')->count();
            $reservedCount = Product::getReservedStock($productId);
            $realAvailable = $availableCount - $reservedCount;

            if ($totalQty > $realAvailable) {
                $stockErrors[] = "{$product->full_name} — ขอ {$totalQty} ชิ้น แต่พร้อมขาย {$realAvailable} ชิ้น"
                    . ($reservedCount > 0 ? " (จอง {$reservedCount})" : '');
            }
        }

        if (!empty($stockErrors)) {
            return redirect()->back()
                ->with('error', 'จำนวนเกินสต็อกที่พร้อมขาย:<br>' . implode('<br>', $stockErrors))
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // สร้างใบตัดสต็อก
            $deliveryNote = DeliveryNote::create([
                'sales_order_number' => $request->sales_order_number,
                'quotation_number' => $request->quotation_number,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'delivery_date' => $request->delivery_date,
                'notes' => $request->notes,
                'status' => 'pending',
                'created_by' => Auth::id()
            ]);

            // สร้างรายการสินค้า
            foreach ($request->items as $item) {
                DeliveryNoteItem::create([
                    'delivery_note_id' => $deliveryNote->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'] ?? null
                ]);
            }

            DB::commit();
            return redirect()->route('admin.delivery-notes.show', $deliveryNote->id)
                ->with('success', 'สร้างใบตัดสต็อกเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DeliveryNote $deliveryNote)
    {
        return redirect()->route('admin.delivery-notes.review', $deliveryNote->id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DeliveryNote $deliveryNote)
    {
        // อนุญาตให้แก้ไขเฉพาะสถานะ pending หรือ confirmed
        if (!in_array($deliveryNote->status, ['pending', 'confirmed'])) {
            return redirect()->route('admin.delivery-notes.show', $deliveryNote->id)
                ->with('error', 'ไม่สามารถแก้ไขใบตัดสต็อกที่สแกนหรืออนุมัติแล้วได้');
        }

        // แสดงเฉพาะสินค้าที่มี StockItem สถานะ available หรือสินค้าที่มีอยู่ในใบนี้แล้ว
        $existingProductIds = $deliveryNote->items->pluck('product_id')->toArray();
        $products = Product::active()
            ->with('category')
            ->where(function ($q) use ($existingProductIds) {
                $q->whereHas('stockItems', function ($sq) {
                    $sq->where('status', 'available');
                })->orWhereIn('id', $existingProductIds);
            })
            ->withCount(['stockItems as available_stock' => function ($q) {
                $q->where('status', 'available');
            }])
            ->get();

        // คำนวณจำนวนจอง (exclude ใบปัจจุบัน) + จำนวนขายได้จริง
        $products->each(function ($product) use ($deliveryNote) {
            $product->reserved_count = Product::getReservedStock($product->id, $deliveryNote->id);
            $product->real_available = max(0, $product->available_stock - $product->reserved_count);
        });

        $deliveryNote->load(['items.product']);

        return view('admin.delivery-notes.edit', compact('deliveryNote', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DeliveryNote $deliveryNote)
    {
        if (!in_array($deliveryNote->status, ['pending', 'confirmed'])) {
            return redirect()->route('admin.delivery-notes.show', $deliveryNote->id)
                ->with('error', 'ไม่สามารถแก้ไขใบตัดสต็อกที่สแกนหรืออนุมัติแล้วได้');
        }

        $validator = Validator::make($request->all(), [
            'sales_order_number' => 'nullable|string|max:100',
            'quotation_number' => 'nullable|string|max:100',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'delivery_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // ตรวจสอบสต็อกจริง (exclude ใบปัจจุบัน)
        $itemsByProduct = collect($request->items)->groupBy('product_id');
        $stockErrors = [];

        foreach ($itemsByProduct as $productId => $items) {
            $totalQty = $items->sum('quantity');

            $availableCount = \App\Models\StockItem::where('product_id', $productId)
                ->where('status', 'available')
                ->count();
            $reservedCount = Product::getReservedStock($productId, $deliveryNote->id);
            $realAvailable = max(0, $availableCount - $reservedCount);

            if ($totalQty > $realAvailable) {
                $product = Product::find($productId);
                $stockErrors[] = "สินค้า {$product->full_name} — ขอ {$totalQty} ชิ้น แต่พร้อมขาย {$realAvailable} ชิ้น" .
                    ($reservedCount > 0 ? " (ล็อก {$reservedCount})" : '');
            }
        }

        if (!empty($stockErrors)) {
            return redirect()->back()
                ->with('error', 'สต็อกไม่เพียงพอ: ' . implode(' | ', $stockErrors))
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // อัปเดตใบตัดสต็อก
            $deliveryNote->update([
                'sales_order_number' => $request->sales_order_number,
                'quotation_number' => $request->quotation_number,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'delivery_date' => $request->delivery_date,
                'notes' => $request->notes
            ]);

            // ลบรายการเดิมทั้งหมด
            $deliveryNote->items()->delete();

            // สร้างรายการใหม่
            foreach ($request->items as $item) {
                DeliveryNoteItem::create([
                    'delivery_note_id' => $deliveryNote->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'] ?? null
                ]);
            }

            DB::commit();
            return redirect()->route('admin.delivery-notes.show', $deliveryNote->id)
                ->with('success', 'แก้ไขใบตัดสต็อกเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeliveryNote $deliveryNote)
    {
        // อนุญาตให้ลบเฉพาะสถานะ pending
        if ($deliveryNote->status !== 'pending') {
            return redirect()->route('admin.delivery-notes.index')
                ->with('error', 'ไม่สามารถลบใบตัดสต็อกที่มีสถานะนอกเหนือจาก "รอยืนยัน" ได้');
        }

        try {
            $deliveryNote->delete();
            return redirect()->route('admin.delivery-notes.index')
                ->with('success', 'ลบใบตัดสต็อกเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * ยืนยันใบตัดสต็อก
     */
    public function confirm(DeliveryNote $deliveryNote)
    {
        if ($deliveryNote->status !== 'pending') {
            return redirect()->back()->with('error', 'สามารถยืนยันได้เฉพาะสถานะ "รอยืนยัน" เท่านั้น');
        }

        $deliveryNote->confirm(Auth::id());

        return redirect()->route('admin.delivery-notes.show', $deliveryNote->id)
            ->with('success', 'ยืนยันใบตัดสต็อกเรียบร้อยแล้ว คนขับรถสามารถเริ่มโหลดสินค้าได้');
    }

    /**
     * Redirect ไปหน้าสแกน Barcode สาธารณะ (ใช้หน้าเดียวกัน)
     */
    public function scan(DeliveryNote $deliveryNote)
    {
        if (!in_array($deliveryNote->status, ['pending', 'confirmed', 'scanned'])) {
            return redirect()->route('admin.delivery-notes.show', $deliveryNote->id)
                ->with('error', 'ไม่สามารถสแกนใบตัดสต็อกที่มีสถานะนี้ได้');
        }

        // สร้าง share URL แล้ว redirect ไปหน้าสแกนสาธารณะ
        $url = $deliveryNote->getShareUrl();

        return redirect()->away($url);
    }

    /**
     * หน้าตรวจสอบและอนุมัติ
     */
    public function review(DeliveryNote $deliveryNote)
    {
        $deliveryNote->load(['items.product', 'creator', 'confirmer', 'scanner', 'approver']);
        $discrepancies = $deliveryNote->checkDiscrepancies();

        return view('admin.delivery-notes.review', compact('deliveryNote', 'discrepancies'));
    }

    /**
     * อนุมัติและตัดสต็อก
     */
    public function approve(Request $request, DeliveryNote $deliveryNote)
    {
        // ตรวจสอบสิทธิ์ admin เท่านั้น
        if (!auth()->user()->can('manage-users')) {
            return redirect()->route('admin.delivery-notes.show', $deliveryNote->id)
                ->with('error', 'เฉพาะ Admin เท่านั้นที่สามารถอนุมัติและตัดสต็อกได้');
        }

        // ตรวจสอบว่ามีการสแกนหรือไม่ (ไม่ใช่เช็คสถานะ)
        $totalScanned = 0;
        foreach ($deliveryNote->items as $item) {
            $totalScanned += $item->scanned_quantity;
        }
        
        if ($totalScanned === 0) {
            return redirect()->back()->with('error', 'กรุณาสแกนสินค้าก่อนอนุมัติ');
        }

        // ตรวจสอบว่ามีการสแกนเกินหรือไม่
        $hasOverScanned = false;
        foreach ($deliveryNote->items as $item) {
            if ($item->scanned_quantity > $item->quantity) {
                $hasOverScanned = true;
                break;
            }
        }

        // ถ้าสแกนเกินและไม่ได้บังคับอนุมัติ ให้แจ้งเตือน
        if ($hasOverScanned && !$request->has('force_approve')) {
            return redirect()->back()->with('error', 'ไม่สามารถอนุมัติได้ เนื่องจากมีรายการที่สแกนเกินจำนวนที่กำหนด กรุณาลบรายการที่สแกนเกินออกก่อน หรือใช้ปุ่ม "บังคับอนุมัติ"');
        }

        $forceApprove = $request->has('force_approve');
        $result = $deliveryNote->approveAndCutStock(Auth::id(), $forceApprove);

        if ($result['success']) {
            $message = $result['message'];
            if (!empty($result['discrepancies'])) {
                $message .= ' (มีความไม่ตรงกัน ' . count($result['discrepancies']) . ' รายการ)';
            }
            
            return redirect()->route('admin.delivery-notes.show', $deliveryNote->id)
                ->with('success', $message);
        } else {
            if (isset($result['discrepancies'])) {
                return redirect()->route('admin.delivery-notes.review', $deliveryNote->id)
                    ->with('warning', $result['message']);
            } else {
                return redirect()->back()->with('error', $result['message']);
            }
        }
    }

    /**
     * รีเซ็ตข้อมูลการสแกน เพื่อสแกนใหม่ทั้งหมด
     */
    public function resetScan(DeliveryNote $deliveryNote)
    {
        if (!auth()->user()->can('manage-users')) {
            return redirect()->route('admin.delivery-notes.show', $deliveryNote->id)
                ->with('error', 'เฉพาะ Admin เท่านั้นที่สามารถรีเซ็ตการสแกนได้');
        }

        if (!in_array($deliveryNote->status, ['scanned'])) {
            return redirect()->route('admin.delivery-notes.show', $deliveryNote->id)
                ->with('error', 'ไม่สามารถรีเซ็ตได้ เนื่องจากสถานะปัจจุบันไม่ใช่ "สแกนแล้ว"');
        }

        DB::transaction(function () use ($deliveryNote) {
            // ล้างข้อมูลสแกนของแต่ละรายการ
            foreach ($deliveryNote->items as $item) {
                $item->update([
                    'scanned_items' => [],
                    'scanned_quantity' => 0,
                    'status' => 'pending',
                ]);
            }

            // รีเซ็ตสถานะใบตัดสต็อก
            $deliveryNote->update([
                'status' => 'confirmed',
                'scanned_by' => null,
                'scanned_at' => null,
            ]);
        });

        // Redirect ไปหน้า public scan
        $url = $deliveryNote->getShareUrl();

        return redirect()->away($url);
    }

    /**
     * สร้าง Share Link (Copy URL) สำหรับสแกน Barcode ภายนอก
     */
    public function generateShareLink(DeliveryNote $deliveryNote)
    {
        if ($deliveryNote->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'ใบตัดสต็อกนี้เสร็จสิ้นแล้ว ไม่สามารถสร้างลิงก์ได้',
            ]);
        }

        $url = $deliveryNote->getShareUrl();

        return response()->json([
            'success' => true,
            'url' => $url,
            'expires_at' => $deliveryNote->fresh()->share_token_expires_at->format('d/m/Y H:i'),
        ]);
    }

    /**
     * พิมพ์ใบตัดสต็อก
     */
    public function print(DeliveryNote $deliveryNote)
    {
        $deliveryNote->load(['items.product', 'warehouse', 'creator']);
        
        return view('admin.delivery-notes.print', compact('deliveryNote'));
    }
}
