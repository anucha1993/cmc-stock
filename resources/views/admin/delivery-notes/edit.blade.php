@extends('adminlte::page')

@section('title', 'แก้ไขใบตัดสต็อก - CMC-STOCK')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-edit text-primary"></i> แก้ไขใบตัดสต็อก — {{ $deliveryNote->delivery_number }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">แดชบอร์ด</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.delivery-notes.index') }}">ใบตัดสต็อก</a></li>
                <li class="breadcrumb-item active">แก้ไข</li>
            </ol>
        </div>
    </div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-5-theme/1.3.0/select2-bootstrap-5-theme.min.css">
<style>
    .form-group label { font-weight: 600; font-size: .875rem; margin-bottom: .25rem; }
    .form-group { margin-bottom: .75rem; }
    .item-row { background: #f8f9fa; border-radius: .375rem; padding: .75rem; margin-bottom: .5rem; border: 1px solid #dee2e6; }
    .item-row:hover { border-color: #80bdff; }
    .item-row .item-number { width: 28px; height: 28px; border-radius: 50%; background: #007bff; color: #fff; display: flex; align-items: center; justify-content: center; font-size: .8rem; font-weight: 700; flex-shrink: 0; }
    .item-row .btn-remove { opacity: .5; transition: opacity .2s; }
    .item-row:hover .btn-remove { opacity: 1; }
    #items-container:empty::after {
        content: 'กดปุ่ม "+ เพิ่มสินค้า" เพื่อเพิ่มรายการ';
        display: block; text-align: center; color: #adb5bd; padding: 2rem; font-size: .9rem;
    }
    .stock-info { font-size: .78rem; margin-top: 2px; line-height: 1.4; }
    .stock-info .badge { font-size: .72rem; font-weight: 600; vertical-align: middle; }
    /* Select2 in item-row */
    .item-row .select2-container { width: 100% !important; }
    .item-row .select2-selection--single { height: 31px !important; font-size: .875rem; border-color: #ced4da; }
    .item-row .select2-selection__rendered { line-height: 29px !important; padding-left: 8px; }
    .item-row .select2-selection__arrow { height: 29px !important; }
    @media (max-width: 767.98px) {
        .item-row .row > div { margin-bottom: .5rem; }
    }
</style>
@stop

@section('content')
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <form action="{{ route('admin.delivery-notes.update', $deliveryNote->id) }}" method="POST" id="delivery-note-form">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- ===== คอลัมน์ซ้าย: ข้อมูลลูกค้า ===== --}}
            <div class="col-lg-5">
                <div class="card card-primary card-outline">
                    <div class="card-header py-2">
                        <h3 class="card-title"><i class="fas fa-user"></i> ข้อมูลลูกค้า</h3>
                    </div>
                    <div class="card-body pb-1">
                        <div class="form-group">
                            <label>ชื่อลูกค้า <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('customer_name') is-invalid @enderror"
                                   name="customer_name" value="{{ old('customer_name', $deliveryNote->customer_name) }}" placeholder="ชื่อลูกค้า / บริษัท" required autofocus>
                            @error('customer_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>เบอร์โทร</label>
                                    <input type="text" class="form-control @error('customer_phone') is-invalid @enderror"
                                           name="customer_phone" value="{{ old('customer_phone', $deliveryNote->customer_phone) }}" placeholder="08x-xxx-xxxx">
                                    @error('customer_phone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label>วันที่จัดส่ง <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('delivery_date') is-invalid @enderror"
                                           name="delivery_date" value="{{ old('delivery_date', $deliveryNote->delivery_date->format('Y-m-d')) }}" required>
                                    @error('delivery_date')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>เลขที่ใบสั่งขาย</label>
                                    <input type="text" class="form-control @error('sales_order_number') is-invalid @enderror"
                                           name="sales_order_number" value="{{ old('sales_order_number', $deliveryNote->sales_order_number) }}" placeholder="SO-XXX">
                                    @error('sales_order_number')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label>เลขที่ใบเสนอราคา</label>
                                    <input type="text" class="form-control @error('quotation_number') is-invalid @enderror"
                                           name="quotation_number" value="{{ old('quotation_number', $deliveryNote->quotation_number) }}" placeholder="QT-XXX">
                                    @error('quotation_number')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>หมายเหตุ</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      name="notes" rows="2" placeholder="หมายเหตุเพิ่มเติม (ถ้ามี)">{{ old('notes', $deliveryNote->notes) }}</textarea>
                            @error('notes')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== คอลัมน์ขวา: รายการสินค้า ===== --}}
            <div class="col-lg-7">
                <div class="card card-success card-outline">
                    <div class="card-header py-2">
                        <h3 class="card-title"><i class="fas fa-boxes"></i> รายการสินค้า</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-success btn-sm" onclick="addItem()">
                                <i class="fas fa-plus"></i> เพิ่มสินค้า
                            </button>
                        </div>
                    </div>
                    <div class="card-body" style="min-height: 280px;">
                        <div id="items-container">
                            {{-- items จะถูกโหลดจาก JS --}}
                        </div>
                    </div>
                    <div class="card-footer py-2 d-flex justify-content-between align-items-center">
                        <span class="text-muted" style="font-size:.85rem">
                            <i class="fas fa-list"></i> รายการ: <strong id="item-count">0</strong>
                        </span>
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="addItem()">
                            <i class="fas fa-plus"></i> เพิ่มสินค้า
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== ปุ่มบันทึก ===== --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body py-2 d-flex justify-content-between">
                        <a href="{{ route('admin.delivery-notes.show', $deliveryNote->id) }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> ยกเลิก
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save"></i> บันทึกการแก้ไข
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/th.js"></script>
<script>
let itemIndex = 0;
const products = @json($products);

// สร้าง product map สำหรับ lookup
const productMap = {};
products.forEach(p => { productMap[p.id] = p; });

// สร้าง options HTML ครั้งเดียว
const productOptions = products.map(p => {
    const cat = p.category ? ` [${p.category.name}]` : '';
    const reserved = p.reserved_count > 0 ? ` (จอง ${p.reserved_count})` : '';
    const label = `${p.name} (${p.sku})${cat} — พร้อมขาย ${p.real_available}${reserved}`;
    return `<option value="${p.id}" data-sku="${p.sku}" data-barcode="${p.barcode || ''}" data-cat="${p.category ? p.category.name : ''}" data-real="${p.real_available}" data-reserved="${p.reserved_count}">${label}</option>`;
}).join('');

// ฟังก์ชัน init Select2 บน element
function initSelect2(selectEl) {
    $(selectEl).select2({
        placeholder: 'พิมพ์ชื่อ, SKU หรือ barcode เพื่อค้นหา...',
        allowClear: true,
        width: '100%',
        language: 'th',
        matcher: function(params, data) {
            if ($.trim(params.term) === '') return data;
            const term = params.term.toLowerCase();
            const text = (data.text || '').toLowerCase();
            const sku = ($(data.element).data('sku') || '').toString().toLowerCase();
            const barcode = ($(data.element).data('barcode') || '').toString().toLowerCase();
            const cat = ($(data.element).data('cat') || '').toString().toLowerCase();
            if (text.indexOf(term) > -1 || sku.indexOf(term) > -1 || barcode.indexOf(term) > -1 || cat.indexOf(term) > -1) {
                return data;
            }
            return null;
        }
    });
}

function addItem(selectedProductId = null, quantity = 1, notes = '') {
    const idx = itemIndex;
    const num = document.querySelectorAll('#items-container .item-row').length + 1;

    const html = `
        <div class="item-row" id="item-${idx}">
            <div class="row align-items-center">
                <div class="col-auto d-none d-md-block">
                    <span class="item-number">${num}</span>
                </div>
                <div class="col-12 col-md">
                    <select class="form-control form-control-sm product-select" name="items[${idx}][product_id]" required>
                        <option value=""></option>
                        ${productOptions}
                    </select>
                    <div class="stock-info" id="stock-info-${idx}"></div>
                </div>
                <div class="col-5 col-md-2">
                    <input type="number" class="form-control form-control-sm text-center qty-input"
                           name="items[${idx}][quantity]" value="${quantity}" min="1" placeholder="จำนวน" required
                           id="qty-${idx}">
                </div>
                <div class="col-5 col-md-3">
                    <input type="text" class="form-control form-control-sm"
                           name="items[${idx}][notes]" placeholder="หมายเหตุ" value="${notes}">
                </div>
                <div class="col-2 col-md-auto text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm btn-remove" onclick="removeItem(${idx})" title="ลบ">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    `;

    document.getElementById('items-container').insertAdjacentHTML('beforeend', html);
    itemIndex++;
    updateItemCount();

    // Init Select2 บน select ตัวใหม่
    const newSelect = document.querySelector(`#item-${idx} .product-select`);
    initSelect2(newSelect);

    // set selected product if provided
    if (selectedProductId) {
        $(newSelect).val(selectedProductId).trigger('change.select2');
        updateStockInfo(idx, selectedProductId);
    }

    // เมื่อเลือกสินค้า → อัปเดต stock info + max
    $(newSelect).on('change', function() {
        updateStockInfo(idx, $(this).val());
    });

    if (!selectedProductId) {
        $(newSelect).select2('open');
    }
}

function updateStockInfo(idx, productId) {
    const infoEl = document.getElementById(`stock-info-${idx}`);
    const qtyEl = document.getElementById(`qty-${idx}`);
    if (!productId || !productMap[productId]) {
        infoEl.innerHTML = '';
        qtyEl.removeAttribute('max');
        return;
    }
    const p = productMap[productId];
    const realAvail = p.real_available;
    let badges = `<span class="text-success"><i class="fas fa-check-circle"></i> พร้อมขาย ${realAvail} ชิ้น</span>`;
    if (p.reserved_count > 0) {
        badges += ` <span class="badge bg-warning text-dark"><i class="fas fa-lock"></i> ล็อก ${p.reserved_count} ชิ้น</span>`;
    }
    if (realAvail === 0) {
        badges = `<span class="text-danger"><i class="fas fa-exclamation-circle"></i> สต็อกพร้อมขายหมด</span>`;
        if (p.reserved_count > 0) {
            badges += ` <span class="badge bg-warning text-dark"><i class="fas fa-lock"></i> ล็อก ${p.reserved_count} ชิ้น</span>`;
        }
    }
    infoEl.innerHTML = badges;
    qtyEl.setAttribute('max', realAvail);
    if (parseInt(qtyEl.value) > realAvail) {
        qtyEl.value = realAvail > 0 ? realAvail : 1;
    }
}

function removeItem(idx) {
    const el = document.getElementById(`item-${idx}`);
    if (el) {
        $(el).find('.product-select').select2('destroy');
        el.style.transition = 'opacity .2s, transform .2s';
        el.style.opacity = '0';
        el.style.transform = 'translateX(20px)';
        setTimeout(() => { el.remove(); renumberItems(); updateItemCount(); }, 200);
    }
}

function renumberItems() {
    document.querySelectorAll('#items-container .item-number').forEach((el, i) => {
        el.textContent = i + 1;
    });
}

function updateItemCount() {
    document.getElementById('item-count').textContent =
        document.querySelectorAll('#items-container .item-row').length;
}

// Validation
$('#delivery-note-form').on('submit', function(e) {
    const rows = $('#items-container .item-row');
    if (rows.length === 0) {
        e.preventDefault();
        Swal.fire({ icon: 'error', title: 'กรุณาเพิ่มรายการสินค้า', text: 'ต้องมีรายการสินค้าอย่างน้อย 1 รายการ' });
        return false;
    }

    // ตรวจสอบจำนวนไม่เกิน real_available (รวมรายการซ้ำ)
    const totals = {};
    rows.each(function() {
        const pid = $(this).find('.product-select').val();
        const qty = parseInt($(this).find('.qty-input').val()) || 0;
        if (pid) {
            totals[pid] = (totals[pid] || 0) + qty;
        }
    });
    const errors = [];
    for (const pid in totals) {
        const p = productMap[pid];
        if (p && totals[pid] > p.real_available) {
            errors.push(`${p.name} — ขอ ${totals[pid]} ชิ้น แต่พร้อมขาย ${p.real_available} ชิ้น`);
        }
    }
    if (errors.length > 0) {
        e.preventDefault();
        Swal.fire({ icon: 'error', title: 'สต็อกไม่เพียงพอ', html: errors.join('<br>') });
        return false;
    }
});

// โหลดรายการสินค้าที่มีอยู่แล้ว
$(document).ready(function() {
    const existingItems = @json($deliveryNote->items);
    if (existingItems.length > 0) {
        existingItems.forEach(item => {
            addItem(item.product_id, item.quantity, item.notes || '');
        });
    } else {
        addItem();
    }
});
</script>
@stop
