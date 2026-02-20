@extends('adminlte::page')

@section('title', 'แก้ไขใบเคลม ' . $claim->claim_number . ' - CMC-STOCK')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-edit text-warning"></i> แก้ไขใบเคลม {{ $claim->claim_number }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.claims.index') }}">การเคลมสินค้า</a></li>
                <li class="breadcrumb-item active">แก้ไข {{ $claim->claim_number }}</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<form action="{{ route('admin.claims.update', $claim) }}" method="POST" id="claimForm">
    @csrf
    @method('PUT')
    <input type="hidden" name="scanned_items" id="scannedItemsInput" value="[]">

    {{-- ประเภทเคลม --}}
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-tag"></i> ประเภทการเคลม</h3>
        </div>
        <div class="card-body">
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="card source-card {{ $claim->claim_source === 'delivery_note' ? 'border-primary' : '' }}" 
                         id="source-delivery_note" onclick="selectSource('delivery_note')" style="cursor: pointer;">
                        <div class="card-body text-center py-3">
                            <i class="fas fa-file-invoice fa-2x text-info mb-2"></i>
                            <h5>เคลมจากใบตัดสต็อก/ขาย</h5>
                            <input type="radio" name="claim_source" value="delivery_note" 
                                   {{ $claim->claim_source === 'delivery_note' ? 'checked' : '' }} class="d-none">
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card source-card {{ $claim->claim_source === 'stock_damage' ? 'border-warning' : '' }}" 
                         id="source-stock_damage" onclick="selectSource('stock_damage')" style="cursor: pointer;">
                        <div class="card-body text-center py-3">
                            <i class="fas fa-warehouse fa-2x text-warning mb-2"></i>
                            <h5>ชำรุดจากสต็อก</h5>
                            <input type="radio" name="claim_source" value="stock_damage"
                                   {{ $claim->claim_source === 'stock_damage' ? 'checked' : '' }} class="d-none">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Delivery note selector --}}
    <div class="card card-info card-outline" id="delivery-note-section" style="{{ $claim->claim_source === 'delivery_note' ? '' : 'display:none' }}">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-file-invoice"></i> ใบตัดสต็อก/ขาย</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>เลือกใบตัดสต็อก</label>
                        <select name="delivery_note_id" id="deliveryNoteSelect" class="form-control">
                            <option value="">-- ไม่ระบุ --</option>
                            @foreach($deliveryNotes as $dn)
                                <option value="{{ $dn->id }}" {{ old('delivery_note_id', $claim->delivery_note_id) == $dn->id ? 'selected' : '' }}>
                                    {{ $dn->delivery_number }} - {{ $dn->customer_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Left column --}}
        <div class="col-md-6">
            {{-- Barcode Scanner --}}
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-barcode"></i> สแกน Barcode เพิ่มสินค้า</h3>
                </div>
                <div class="card-body">
                    <div class="input-group">
                        <input type="text" id="barcode-input" class="form-control form-control-lg" 
                               placeholder="สแกน Barcode แล้วกด Enter" autofocus>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-success btn-lg" onclick="manualScan()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div id="scan-result" class="mt-2"></div>
                </div>
            </div>

            {{-- Claim info --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-alt"></i> ข้อมูลเคลม</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ประเภท <span class="text-danger">*</span></label>
                                <select name="claim_type" class="form-control" required>
                                    <option value="defective" {{ old('claim_type', $claim->claim_type) == 'defective' ? 'selected' : '' }}>สินค้าชำรุด</option>
                                    <option value="damaged" {{ old('claim_type', $claim->claim_type) == 'damaged' ? 'selected' : '' }}>สินค้าเสียหาย</option>
                                    <option value="wrong_item" {{ old('claim_type', $claim->claim_type) == 'wrong_item' ? 'selected' : '' }}>สินค้าผิดรายการ</option>
                                    <option value="missing_item" {{ old('claim_type', $claim->claim_type) == 'missing_item' ? 'selected' : '' }}>สินค้าขาดหาย</option>
                                    <option value="warranty" {{ old('claim_type', $claim->claim_type) == 'warranty' ? 'selected' : '' }}>เคลมประกัน</option>
                                    <option value="other" {{ old('claim_type', $claim->claim_type) == 'other' ? 'selected' : '' }}>อื่นๆ</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ความสำคัญ <span class="text-danger">*</span></label>
                                <select name="priority" class="form-control" required>
                                    <option value="low" {{ old('priority', $claim->priority) == 'low' ? 'selected' : '' }}>ต่ำ</option>
                                    <option value="normal" {{ old('priority', $claim->priority) == 'normal' ? 'selected' : '' }}>ปกติ</option>
                                    <option value="high" {{ old('priority', $claim->priority) == 'high' ? 'selected' : '' }}>สูง</option>
                                    <option value="urgent" {{ old('priority', $claim->priority) == 'urgent' ? 'selected' : '' }}>เร่งด่วน</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>วันที่เคลม <span class="text-danger">*</span></label>
                                <input type="date" name="claim_date" class="form-control" value="{{ old('claim_date', $claim->claim_date->format('Y-m-d')) }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>คลังสินค้าชำรุด</label>
                                <select name="damaged_warehouse_id" class="form-control">
                                    <option value="">-- ไม่ระบุ --</option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}" {{ old('damaged_warehouse_id', $claim->damaged_warehouse_id) == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>รายละเอียด <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="3" required>{{ old('description', $claim->description) }}</textarea>
                    </div>
                    <hr>
                    <h6><i class="fas fa-user"></i> ข้อมูลลูกค้า</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ชื่อ</label>
                                <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', $claim->customer_name) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>โทร</label>
                                <input type="text" name="customer_phone" class="form-control" value="{{ old('customer_phone', $claim->customer_phone) }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>อีเมล</label>
                                <input type="email" name="customer_email" class="form-control" value="{{ old('customer_email', $claim->customer_email) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>เลขที่อ้างอิง</label>
                                <input type="text" name="reference_number" class="form-control" value="{{ old('reference_number', $claim->reference_number) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right column - Scanned items --}}
        <div class="col-md-6">
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-boxes"></i> รายการเคลม 
                        <span class="badge badge-warning" id="itemCount">0</span> รายการ
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-danger btn-xs" onclick="clearAllItems()">
                            <i class="fas fa-trash"></i> ล้างทั้งหมด
                        </button>
                    </div>
                </div>
                <div class="card-body p-0" style="max-height: 600px; overflow-y: auto;">
                    <div id="no-items-msg" class="text-center text-muted py-5" style="display:none;">
                        <i class="fas fa-barcode fa-3x mb-3 text-secondary"></i>
                        <p>ยังไม่มีรายการ</p>
                    </div>
                    <div id="scanned-items-list"></div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-lg btn-block" id="submitBtn">
                        <i class="fas fa-save"></i> บันทึกการแก้ไข
                    </button>
                    <a href="{{ route('admin.claims.show', $claim) }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-times"></i> ยกเลิก
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>
@stop

@section('css')
<style>
    .source-card { transition: all 0.3s ease; border: 2px solid #dee2e6; }
    .source-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
    .source-card.border-primary { border-color: #007bff !important; background-color: #f0f7ff !important; }
    .source-card.border-warning { border-color: #ffc107 !important; background-color: #fffdf0 !important; }
    #barcode-input { font-size: 1.3rem; font-weight: bold; text-align: center; }
    .scanned-item-card { border-left: 4px solid #28a745; margin-bottom: 0; transition: all 0.3s; }
    @keyframes slideIn { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }
    .slide-in { animation: slideIn 0.3s ease-out; }
</style>
@stop

@section('js')
<script>
let scannedItems = [];
let currentSource = '{{ $claim->claim_source }}';

// Load existing items
const existingItems = @json($claim->items->map(function($item) {
    return [
        'product_id' => $item->product_id,
        'product_name' => $item->product->full_name ?? 'N/A',
        'product_sku' => $item->product->sku ?? '',
        'stock_item_id' => $item->stock_item_id,
        'barcode' => $item->stockItem->barcode ?? '',
        'serial_number' => $item->stockItem->serial_number ?? '',
        'warehouse_name' => '',
        'quantity' => $item->quantity,
        'reason' => $item->reason,
        'description' => $item->description ?? ''
    ];
}));

function selectSource(source) {
    currentSource = source;
    document.querySelectorAll('.source-card').forEach(card => {
        card.classList.remove('border-primary', 'border-warning');
        card.style.borderColor = '#dee2e6';
    });
    const sc = document.getElementById('source-' + source);
    sc.classList.add(source === 'delivery_note' ? 'border-primary' : 'border-warning');
    sc.querySelector('input[type="radio"]').checked = true;
    document.getElementById('delivery-note-section').style.display = source === 'delivery_note' ? '' : 'none';
}

document.getElementById('barcode-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); manualScan(); }
});

function manualScan() {
    const input = document.getElementById('barcode-input');
    const barcode = input.value.trim();
    if (!barcode) return;
    input.disabled = true;
    scanBarcode(barcode);
    input.value = '';
}

function scanBarcode(barcode) {
    showScanResult('info', '<i class="fas fa-spinner fa-spin"></i> กำลังค้นหา...');
    $.ajax({
        url: '{{ route("admin.claims.scan-barcode") }}',
        method: 'POST',
        data: { barcode: barcode, _token: '{{ csrf_token() }}' },
        success: function(r) {
            if (r.success) {
                addScannedItem(r.data);
                showScanResult('success', '<i class="fas fa-check-circle"></i> ' + r.message);
            } else {
                showScanResult('danger', '<i class="fas fa-times-circle"></i> ' + r.message);
            }
        },
        error: function(xhr) {
            showScanResult('danger', '<i class="fas fa-exclamation-triangle"></i> ' + (xhr.responseJSON?.message || 'เกิดข้อผิดพลาด'));
        },
        complete: function() {
            document.getElementById('barcode-input').disabled = false;
            document.getElementById('barcode-input').focus();
        }
    });
}

function showScanResult(type, html) {
    const el = document.getElementById('scan-result');
    el.innerHTML = `<div class="alert alert-${type} py-2 mb-0 slide-in">${html}</div>`;
    setTimeout(() => { el.innerHTML = ''; }, 4000);
}

function addScannedItem(data, reason = 'broken') {
    if (data.stock_item_id) {
        const exists = scannedItems.find(i => i.stock_item_id === data.stock_item_id);
        if (exists) {
            showScanResult('warning', '<i class="fas fa-exclamation-triangle"></i> สินค้านี้ถูกเพิ่มแล้ว');
            return;
        }
    }
    scannedItems.push({
        id: Date.now() + Math.random(),
        product_id: data.product_id,
        product_name: data.product_name,
        product_sku: data.product_sku || '',
        stock_item_id: data.stock_item_id || null,
        barcode: data.barcode || '',
        serial_number: data.serial_number || '',
        warehouse_name: data.warehouse_name || '',
        quantity: data.quantity || 1,
        reason: data.reason || reason,
        description: data.description || ''
    });
    renderItems();
    updateHiddenInput();
}

function removeItem(id) {
    scannedItems = scannedItems.filter(i => i.id !== id);
    renderItems();
    updateHiddenInput();
}

function clearAllItems() {
    if (!scannedItems.length || !confirm('ล้างทั้งหมด?')) return;
    scannedItems = [];
    renderItems();
    updateHiddenInput();
}

function updateItemReason(id, v) { const i = scannedItems.find(x => x.id === id); if (i) i.reason = v; updateHiddenInput(); }
function updateItemDescription(id, v) { const i = scannedItems.find(x => x.id === id); if (i) i.description = v; updateHiddenInput(); }
function updateItemQuantity(id, v) { const i = scannedItems.find(x => x.id === id); if (i) i.quantity = parseInt(v) || 1; updateHiddenInput(); }

function renderItems() {
    const container = document.getElementById('scanned-items-list');
    const noMsg = document.getElementById('no-items-msg');
    const countBadge = document.getElementById('itemCount');
    const submitBtn = document.getElementById('submitBtn');
    countBadge.textContent = scannedItems.length;

    if (!scannedItems.length) {
        noMsg.style.display = '';
        container.innerHTML = '';
        submitBtn.disabled = true;
        return;
    }
    noMsg.style.display = 'none';
    submitBtn.disabled = false;

    let html = '';
    scannedItems.forEach((item, idx) => {
        html += `
        <div class="scanned-item-card border-bottom p-3 slide-in">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>${idx+1}. ${item.product_name}</strong>
                    <br><small class="text-muted">SKU: ${item.product_sku}</small>
                    ${item.barcode ? '<br><small><i class="fas fa-barcode"></i> ' + item.barcode + '</small>' : ''}
                    ${item.serial_number ? '<br><small><i class="fas fa-hashtag"></i> SN: ' + item.serial_number + '</small>' : ''}
                </div>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeItem(${item.id})"><i class="fas fa-times"></i></button>
            </div>
            <div class="row mt-2">
                <div class="col-4">
                    <input type="number" class="form-control form-control-sm" value="${item.quantity}" min="1"
                           onchange="updateItemQuantity(${item.id}, this.value)" ${item.stock_item_id ? 'readonly' : ''}>
                    <small class="text-muted">จำนวน</small>
                </div>
                <div class="col-8">
                    <select class="form-control form-control-sm" onchange="updateItemReason(${item.id}, this.value)">
                        <option value="broken" ${item.reason==='broken'?'selected':''}>แตก/หัก</option>
                        <option value="deformed" ${item.reason==='deformed'?'selected':''}>ผิดรูป/บิดงอ</option>
                        <option value="rust" ${item.reason==='rust'?'selected':''}>เป็นสนิม</option>
                        <option value="wrong_size" ${item.reason==='wrong_size'?'selected':''}>ขนาดไม่ตรง</option>
                        <option value="wrong_spec" ${item.reason==='wrong_spec'?'selected':''}>สเปคไม่ตรง</option>
                        <option value="missing" ${item.reason==='missing'?'selected':''}>ขาดหาย</option>
                        <option value="quality" ${item.reason==='quality'?'selected':''}>คุณภาพไม่ได้มาตรฐาน</option>
                        <option value="other" ${item.reason==='other'?'selected':''}>อื่นๆ</option>
                    </select>
                    <small class="text-muted">สาเหตุ</small>
                </div>
            </div>
            <div class="mt-1">
                <input type="text" class="form-control form-control-sm" value="${item.description||''}" 
                       onchange="updateItemDescription(${item.id}, this.value)" placeholder="หมายเหตุ">
            </div>
        </div>`;
    });
    container.innerHTML = html;
}

function updateHiddenInput() {
    document.getElementById('scannedItemsInput').value = JSON.stringify(
        scannedItems.map(i => ({
            product_id: i.product_id,
            stock_item_id: i.stock_item_id,
            quantity: i.quantity,
            reason: i.reason,
            description: i.description
        }))
    );
}

document.getElementById('claimForm').addEventListener('submit', function(e) {
    if (!scannedItems.length) { e.preventDefault(); alert('กรุณาเพิ่มสินค้าอย่างน้อย 1 รายการ'); return false; }
    updateHiddenInput();
});

// Init - load existing items
document.addEventListener('DOMContentLoaded', function() {
    selectSource(currentSource);
    existingItems.forEach(item => {
        addScannedItem(item, item.reason || 'broken');
    });
    document.getElementById('barcode-input').focus();
});
</script>
@stop
