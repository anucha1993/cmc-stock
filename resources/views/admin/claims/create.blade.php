@extends('adminlte::page')

@section('title', 'สร้างใบเคลมสินค้า - CMC-STOCK')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-plus-circle text-primary"></i> สร้างใบเคลมสินค้า</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.claims.index') }}">การเคลมสินค้า</a></li>
                <li class="breadcrumb-item active">สร้างใบเคลม</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<form action="{{ route('admin.claims.store') }}" method="POST" id="claimForm">
    @csrf
    <input type="hidden" name="scanned_items" id="scannedItemsInput" value="[]">

    {{-- ========== STEP 1: เลือกประเภทเคลม ========== --}}
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-tag"></i> ขั้นตอนที่ 1: เลือกประเภทการเคลม</h3>
        </div>
        <div class="card-body">
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="card source-card {{ $defaultSource === 'delivery_note' ? 'border-primary bg-light' : '' }}" 
                         id="source-delivery_note" onclick="selectSource('delivery_note')" style="cursor: pointer;">
                        <div class="card-body text-center py-4">
                            <i class="fas fa-file-invoice fa-3x text-info mb-3"></i>
                            <h4>เคลมจากใบตัดสต็อก/ขาย</h4>
                            <p class="text-muted mb-0">สินค้าที่ขายไปแล้วมีปัญหา ลูกค้านำมาเคลม</p>
                            <input type="radio" name="claim_source" value="delivery_note" 
                                   {{ $defaultSource === 'delivery_note' ? 'checked' : '' }} class="d-none">
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card source-card {{ $defaultSource === 'stock_damage' ? 'border-warning bg-light' : '' }}" 
                         id="source-stock_damage" onclick="selectSource('stock_damage')" style="cursor: pointer;">
                        <div class="card-body text-center py-4">
                            <i class="fas fa-warehouse fa-3x text-warning mb-3"></i>
                            <h4>ชำรุดจากสต็อก</h4>
                            <p class="text-muted mb-0">สินค้าในสต็อกชำรุดเอง ยังไม่ได้ขาย</p>
                            <input type="radio" name="claim_source" value="stock_damage"
                                   {{ $defaultSource === 'stock_damage' ? 'checked' : '' }} class="d-none">
                        </div>
                    </div>
                </div>
            </div>
            @error('claim_source') <div class="text-danger text-center mt-2">{{ $message }}</div> @enderror
        </div>
    </div>

    {{-- ========== ส่วนเลือกใบตัดสต็อก (แสดงเมื่อเลือก delivery_note) ========== --}}
    <div class="card card-info card-outline" id="delivery-note-section" style="{{ $defaultSource === 'delivery_note' ? '' : 'display:none' }}">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-file-invoice"></i> เลือกใบตัดสต็อก/ขาย</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-search"></i> เลือกใบตัดสต็อก <span class="text-danger">*</span></label>
                        <select name="delivery_note_id" id="deliveryNoteSelect" class="form-control select2">
                            <option value="">-- เลือกใบตัดสต็อก --</option>
                            @foreach($deliveryNotes as $dn)
                                <option value="{{ $dn->id }}" data-customer="{{ $dn->customer_name }}" data-phone="{{ $dn->customer_phone }}">
                                    {{ $dn->delivery_number }} - {{ $dn->customer_name }} ({{ $dn->delivery_date?->format('d/m/Y') }})
                                </option>
                            @endforeach
                        </select>
                        @error('delivery_note_id') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-info btn-block" id="loadDnBtn" onclick="loadDeliveryNote()">
                            <i class="fas fa-download"></i> ดึงข้อมูล
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group" id="dn-info" style="display:none;">
                        <label>ข้อมูลลูกค้า</label>
                        <p class="mb-0"><strong id="dn-customer"></strong></p>
                        <small class="text-muted" id="dn-phone"></small>
                    </div>
                </div>
            </div>

            {{-- รายการสินค้าจากใบตัดสต็อก --}}
            <div id="dn-items-section" style="display:none;">
                <hr>
                <h5><i class="fas fa-list"></i> รายการสินค้าจากใบตัดสต็อก <small class="text-muted">(คลิกเพื่อเลือกสินค้าที่ต้องการเคลม)</small></h5>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th width="40"><input type="checkbox" id="selectAllDnItems"></th>
                                <th>สินค้า</th>
                                <th>Barcode</th>
                                <th>S/N</th>
                            </tr>
                        </thead>
                        <tbody id="dn-items-body">
                            {{-- Filled by AJAX --}}
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-primary" onclick="addSelectedDnItems()">
                    <i class="fas fa-plus"></i> เพิ่มรายการที่เลือกไปยังใบเคลม
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- ========== คอลัมน์ซ้าย: Barcode Scanner + ข้อมูลเคลม ========== --}}
        <div class="col-md-6">
            {{-- Barcode Scanner --}}
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-barcode"></i> สแกน Barcode เพิ่มสินค้า</h3>
                </div>
                <div class="card-body">
                    <div class="form-group mb-2">
                        <label><i class="fas fa-crosshairs"></i> ยิง Barcode ที่นี่</label>
                        <div class="input-group">
                            <input type="text" id="barcode-input" class="form-control form-control-lg" 
                                   placeholder="สแกนหรือพิมพ์ Barcode แล้วกด Enter" autofocus>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-success btn-lg" onclick="manualScan()">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id="scan-result" class="mt-2"></div>
                    <div class="alert alert-light border mt-2 mb-0 py-2">
                        <small>
                            <i class="fas fa-info-circle text-info"></i> 
                            ยิง Barcode สินค้าแล้วกด <kbd>Enter</kbd> เพื่อเพิ่มรายการเคลม | 
                            หรือเลือกจากใบตัดสต็อกด้านบน
                        </small>
                    </div>
                </div>
            </div>

            {{-- ข้อมูลเคลม --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-alt"></i> ข้อมูลเคลม</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ประเภทการเคลม <span class="text-danger">*</span></label>
                                <select name="claim_type" class="form-control" required>
                                    <option value="defective" {{ old('claim_type', 'defective') == 'defective' ? 'selected' : '' }}>สินค้าชำรุด</option>
                                    <option value="damaged" {{ old('claim_type') == 'damaged' ? 'selected' : '' }}>สินค้าเสียหาย</option>
                                    <option value="wrong_item" {{ old('claim_type') == 'wrong_item' ? 'selected' : '' }}>สินค้าผิดรายการ</option>
                                    <option value="missing_item" {{ old('claim_type') == 'missing_item' ? 'selected' : '' }}>สินค้าขาดหาย</option>
                                    <option value="warranty" {{ old('claim_type') == 'warranty' ? 'selected' : '' }}>เคลมประกัน</option>
                                    <option value="other" {{ old('claim_type') == 'other' ? 'selected' : '' }}>อื่นๆ</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ลำดับความสำคัญ <span class="text-danger">*</span></label>
                                <select name="priority" class="form-control" required>
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>ต่ำ</option>
                                    <option value="normal" {{ old('priority', 'normal') == 'normal' ? 'selected' : '' }}>ปกติ</option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>สูง</option>
                                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>เร่งด่วน</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>วันที่เคลม <span class="text-danger">*</span></label>
                                <input type="date" name="claim_date" class="form-control" value="{{ old('claim_date', date('Y-m-d')) }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>คลังสินค้าสำหรับของชำรุด</label>
                                <select name="damaged_warehouse_id" class="form-control">
                                    <option value="">-- ไม่ระบุ --</option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}" {{ old('damaged_warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>รายละเอียดปัญหา <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="3" required placeholder="อธิบายปัญหาที่พบ...">{{ old('description') }}</textarea>
                        @error('description') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    {{-- ข้อมูลลูกค้า (ซ่อนได้สำหรับ stock_damage) --}}
                    <div id="customer-section">
                        <hr>
                        <h6><i class="fas fa-user"></i> ข้อมูลลูกค้า <small class="text-muted">(ไม่จำเป็น สำหรับเคลมจากสต็อก)</small></h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>ชื่อลูกค้า</label>
                                    <input type="text" name="customer_name" id="customerName" class="form-control" value="{{ old('customer_name') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>เบอร์โทร</label>
                                    <input type="text" name="customer_phone" id="customerPhone" class="form-control" value="{{ old('customer_phone') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>อีเมล</label>
                                    <input type="email" name="customer_email" class="form-control" value="{{ old('customer_email') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>เลขที่อ้างอิง</label>
                                    <input type="text" name="reference_number" class="form-control" value="{{ old('reference_number') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========== คอลัมน์ขวา: รายการสินค้าที่สแกนแล้ว ========== --}}
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
                    <div id="no-items-msg" class="text-center text-muted py-5">
                        <i class="fas fa-barcode fa-3x mb-3 text-secondary"></i>
                        <p>ยังไม่มีรายการ<br>สแกน Barcode เพื่อเพิ่มสินค้า</p>
                    </div>
                    <div id="scanned-items-list">
                        {{-- Dynamic items --}}
                    </div>
                </div>
                <div class="card-footer">
                    @error('scanned_items') <div class="text-danger mb-2">{{ $message }}</div> @enderror
                    <button type="submit" class="btn btn-primary btn-lg btn-block" id="submitBtn" disabled>
                        <i class="fas fa-save"></i> บันทึกใบเคลม
                    </button>
                    <a href="{{ route('admin.claims.index') }}" class="btn btn-secondary btn-block">
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
    .source-card {
        transition: all 0.3s ease;
        border: 2px solid #dee2e6;
    }
    .source-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .source-card.border-primary {
        border-color: #007bff !important;
        background-color: #f0f7ff !important;
    }
    .source-card.border-warning {
        border-color: #ffc107 !important;
        background-color: #fffdf0 !important;
    }

    #barcode-input {
        font-size: 1.3rem;
        font-weight: bold;
        text-align: center;
    }

    .scanned-item-card {
        border-left: 4px solid #28a745;
        margin-bottom: 0;
        transition: all 0.3s;
    }
    .scanned-item-card:hover {
        background-color: #f8f9fa;
    }
    .scanned-item-card.duplicate {
        border-left-color: #ffc107;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateX(-20px); }
        to { opacity: 1; transform: translateX(0); }
    }
    .slide-in {
        animation: slideIn 0.3s ease-out;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    .pulse {
        animation: pulse 0.3s ease-in-out;
    }

    .dn-item-row {
        cursor: pointer;
    }
    .dn-item-row:hover {
        background-color: #f0f7ff;
    }
</style>
@stop

@section('js')
<script>
// ===== State =====
let scannedItems = [];
let currentSource = '{{ $defaultSource }}';

// ===== Source Selection =====
function selectSource(source) {
    currentSource = source;
    document.querySelectorAll('.source-card').forEach(card => {
        card.classList.remove('border-primary', 'border-warning', 'bg-light');
        card.style.borderColor = '#dee2e6';
    });

    const selectedCard = document.getElementById('source-' + source);
    if (source === 'delivery_note') {
        selectedCard.classList.add('border-primary');
        selectedCard.style.borderColor = '#007bff';
    } else {
        selectedCard.classList.add('border-warning');
        selectedCard.style.borderColor = '#ffc107';
    }
    selectedCard.querySelector('input[type="radio"]').checked = true;

    // Toggle delivery note section
    document.getElementById('delivery-note-section').style.display = 
        source === 'delivery_note' ? '' : 'none';
}

// ===== Barcode Scanner =====
document.getElementById('barcode-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        manualScan();
    }
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
        data: {
            barcode: barcode,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                addScannedItem(response.data);
                showScanResult('success', '<i class="fas fa-check-circle"></i> ' + response.message);
                playSound('success');
            } else {
                showScanResult('danger', '<i class="fas fa-times-circle"></i> ' + response.message);
                playSound('error');
            }
        },
        error: function(xhr) {
            const msg = xhr.responseJSON?.message || 'เกิดข้อผิดพลาดในการค้นหา';
            showScanResult('danger', '<i class="fas fa-exclamation-triangle"></i> ' + msg);
            playSound('error');
        },
        complete: function() {
            const input = document.getElementById('barcode-input');
            input.disabled = false;
            input.focus();
        }
    });
}

function showScanResult(type, html) {
    const el = document.getElementById('scan-result');
    el.innerHTML = `<div class="alert alert-${type} py-2 mb-0 slide-in">${html}</div>`;
    setTimeout(() => { el.innerHTML = ''; }, 4000);
}

// ===== Add/Remove Scanned Items =====
function addScannedItem(data, reason = 'broken') {
    // Check duplicate by stock_item_id
    if (data.stock_item_id) {
        const exists = scannedItems.find(i => i.stock_item_id === data.stock_item_id);
        if (exists) {
            showScanResult('warning', '<i class="fas fa-exclamation-triangle"></i> สินค้านี้ถูกเพิ่มแล้ว: ' + data.product_name);
            playSound('error');
            return;
        }
    }

    const item = {
        id: Date.now() + Math.random(),
        product_id: data.product_id,
        product_name: data.product_name,
        product_sku: data.product_sku || '',
        stock_item_id: data.stock_item_id || null,
        barcode: data.barcode || '',
        serial_number: data.serial_number || '',
        warehouse_name: data.warehouse_name || '',
        quantity: 1,
        reason: reason,
        description: ''
    };

    scannedItems.push(item);
    renderItems();
    updateHiddenInput();
}

function removeItem(id) {
    scannedItems = scannedItems.filter(i => i.id !== id);
    renderItems();
    updateHiddenInput();
}

function clearAllItems() {
    if (scannedItems.length === 0) return;
    if (!confirm('ยืนยันล้างรายการทั้งหมด?')) return;
    scannedItems = [];
    renderItems();
    updateHiddenInput();
}

function updateItemReason(id, reason) {
    const item = scannedItems.find(i => i.id === id);
    if (item) item.reason = reason;
    updateHiddenInput();
}

function updateItemDescription(id, desc) {
    const item = scannedItems.find(i => i.id === id);
    if (item) item.description = desc;
    updateHiddenInput();
}

function updateItemQuantity(id, qty) {
    const item = scannedItems.find(i => i.id === id);
    if (item) item.quantity = parseInt(qty) || 1;
    updateHiddenInput();
}

function renderItems() {
    const container = document.getElementById('scanned-items-list');
    const noMsg = document.getElementById('no-items-msg');
    const countBadge = document.getElementById('itemCount');
    const submitBtn = document.getElementById('submitBtn');

    countBadge.textContent = scannedItems.length;

    if (scannedItems.length === 0) {
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
        <div class="scanned-item-card border-bottom p-3 slide-in" id="item-${item.id}">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <strong>${idx + 1}. ${item.product_name}</strong>
                    <br><small class="text-muted">SKU: ${item.product_sku}</small>
                    ${item.barcode ? '<br><small><i class="fas fa-barcode"></i> ' + item.barcode + '</small>' : ''}
                    ${item.serial_number ? '<br><small><i class="fas fa-hashtag"></i> SN: ' + item.serial_number + '</small>' : ''}
                    ${item.warehouse_name ? '<br><small><i class="fas fa-warehouse"></i> ' + item.warehouse_name + '</small>' : ''}
                </div>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeItem(${item.id})" title="ลบ">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="row mt-2">
                <div class="col-4">
                    <input type="number" class="form-control form-control-sm" value="${item.quantity}" min="1"
                           onchange="updateItemQuantity(${item.id}, this.value)" placeholder="จำนวน"
                           ${item.stock_item_id ? 'readonly' : ''}>
                    <small class="text-muted">จำนวน</small>
                </div>
                <div class="col-8">
                    <select class="form-control form-control-sm" onchange="updateItemReason(${item.id}, this.value)">
                        <option value="broken" ${item.reason === 'broken' ? 'selected' : ''}>แตก/หัก</option>
                        <option value="deformed" ${item.reason === 'deformed' ? 'selected' : ''}>ผิดรูป/บิดงอ</option>
                        <option value="rust" ${item.reason === 'rust' ? 'selected' : ''}>เป็นสนิม</option>
                        <option value="wrong_size" ${item.reason === 'wrong_size' ? 'selected' : ''}>ขนาดไม่ตรง</option>
                        <option value="wrong_spec" ${item.reason === 'wrong_spec' ? 'selected' : ''}>สเปคไม่ตรง</option>
                        <option value="missing" ${item.reason === 'missing' ? 'selected' : ''}>ขาดหาย</option>
                        <option value="quality" ${item.reason === 'quality' ? 'selected' : ''}>คุณภาพไม่ได้มาตรฐาน</option>
                        <option value="other" ${item.reason === 'other' ? 'selected' : ''}>อื่นๆ</option>
                    </select>
                    <small class="text-muted">สาเหตุ</small>
                </div>
            </div>
            <div class="mt-1">
                <input type="text" class="form-control form-control-sm" 
                       value="${item.description || ''}" 
                       onchange="updateItemDescription(${item.id}, this.value)"
                       placeholder="หมายเหตุเพิ่มเติม (ไม่บังคับ)">
            </div>
        </div>`;
    });

    container.innerHTML = html;
}

function updateHiddenInput() {
    const submitData = scannedItems.map(item => ({
        product_id: item.product_id,
        stock_item_id: item.stock_item_id,
        quantity: item.quantity,
        reason: item.reason,
        description: item.description
    }));
    document.getElementById('scannedItemsInput').value = JSON.stringify(submitData);
}

// ===== Delivery Note Loading =====
function loadDeliveryNote() {
    const dnId = document.getElementById('deliveryNoteSelect').value;
    if (!dnId) {
        alert('กรุณาเลือกใบตัดสต็อก');
        return;
    }

    const btn = document.getElementById('loadDnBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังโหลด...';

    $.ajax({
        url: '{{ route("admin.claims.delivery-note-data") }}',
        method: 'GET',
        data: { delivery_note_id: dnId },
        success: function(response) {
            if (response.success) {
                fillDeliveryNoteInfo(response.data);
            } else {
                alert(response.message);
            }
        },
        error: function() {
            alert('เกิดข้อผิดพลาดในการโหลดข้อมูล');
        },
        complete: function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-download"></i> ดึงข้อมูล';
        }
    });
}

function fillDeliveryNoteInfo(data) {
    // Fill customer info
    document.getElementById('dn-info').style.display = '';
    document.getElementById('dn-customer').textContent = data.customer_name;
    document.getElementById('dn-phone').textContent = data.customer_phone || '-';
    document.getElementById('customerName').value = data.customer_name || '';
    document.getElementById('customerPhone').value = data.customer_phone || '';

    // Fill items table
    const tbody = document.getElementById('dn-items-body');
    let html = '';
    data.items.forEach((item, idx) => {
        html += `
        <tr class="dn-item-row">
            <td><input type="checkbox" class="dn-item-check" data-index="${idx}" 
                 data-product-id="${item.product_id}" 
                 data-product-name="${item.product_name}" 
                 data-product-sku="${item.product_sku}"
                 data-stock-item-id="${item.stock_item_id || ''}" 
                 data-barcode="${item.barcode || ''}" 
                 data-serial-number="${item.serial_number || ''}"></td>
            <td><strong>${item.product_name}</strong><br><small class="text-muted">${item.product_sku}</small></td>
            <td>${item.barcode ? '<code>' + item.barcode + '</code>' : '<span class="text-muted">-</span>'}</td>
            <td>${item.serial_number || '-'}</td>
        </tr>`;
    });
    tbody.innerHTML = html;
    document.getElementById('dn-items-section').style.display = '';
}

function addSelectedDnItems() {
    const checkboxes = document.querySelectorAll('.dn-item-check:checked');
    if (checkboxes.length === 0) {
        alert('กรุณาเลือกอย่างน้อย 1 รายการ');
        return;
    }

    let addedCount = 0;
    checkboxes.forEach(cb => {
        const data = {
            product_id: parseInt(cb.dataset.productId),
            product_name: cb.dataset.productName,
            product_sku: cb.dataset.productSku,
            stock_item_id: cb.dataset.stockItemId ? parseInt(cb.dataset.stockItemId) : null,
            barcode: cb.dataset.barcode,
            serial_number: cb.dataset.serialNumber,
            warehouse_name: ''
        };

        // Check for duplicate
        if (data.stock_item_id) {
            const exists = scannedItems.find(i => i.stock_item_id === data.stock_item_id);
            if (exists) return;
        }

        addScannedItem(data);
        addedCount++;
        cb.checked = false;
    });

    if (addedCount > 0) {
        showScanResult('success', `<i class="fas fa-check"></i> เพิ่ม ${addedCount} รายการจากใบตัดสต็อก`);
    } else {
        showScanResult('warning', '<i class="fas fa-exclamation-triangle"></i> รายการที่เลือกถูกเพิ่มแล้วทั้งหมด');
    }

    document.getElementById('selectAllDnItems').checked = false;
}

// Select all DN items
document.getElementById('selectAllDnItems').addEventListener('change', function() {
    document.querySelectorAll('.dn-item-check').forEach(cb => {
        cb.checked = this.checked;
    });
});

// ===== Sound Effects =====
function playSound(type) {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioCtx.createOscillator();
        const gainNode = audioCtx.createGain();
        oscillator.connect(gainNode);
        gainNode.connect(audioCtx.destination);
        gainNode.gain.value = 0.1;

        if (type === 'success') {
            oscillator.frequency.value = 880;
            oscillator.type = 'sine';
        } else {
            oscillator.frequency.value = 300;
            oscillator.type = 'square';
        }

        oscillator.start();
        setTimeout(() => oscillator.stop(), 150);
    } catch(e) {}
}

// ===== Form Validation =====
document.getElementById('claimForm').addEventListener('submit', function(e) {
    if (scannedItems.length === 0) {
        e.preventDefault();
        alert('กรุณาสแกนสินค้าอย่างน้อย 1 รายการ');
        return false;
    }
    updateHiddenInput();
});

// ===== Keep barcode input focused =====
document.addEventListener('click', function(e) {
    const barcodeInput = document.getElementById('barcode-input');
    if (e.target !== barcodeInput && !e.target.closest('input, select, textarea, button, a, .source-card')) {
        barcodeInput.focus();
    }
});

// Init
document.addEventListener('DOMContentLoaded', function() {
    selectSource(currentSource);
    document.getElementById('barcode-input').focus();
});
</script>
@stop
