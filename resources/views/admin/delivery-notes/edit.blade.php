@extends('adminlte::page')

@section('title', 'แก้ไขใบตัดสต็อก - CMC-STOCK')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>แก้ไขใบตัดสต็อก</h1>
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

@section('content')
    <form action="{{ route('admin.delivery-notes.update', $deliveryNote->id) }}" method="POST" id="delivery-note-form">
        @csrf
        @method('PUT')
        
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-edit"></i> {{ $deliveryNote->delivery_number }}</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- ข้อมูลลูกค้า -->
                    <div class="col-md-6">
                        <h5><i class="fas fa-user"></i> ข้อมูลลูกค้า</h5>
                        
                        <div class="form-group">
                            <label for="customer_name">ชื่อลูกค้า <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('customer_name') is-invalid @enderror" 
                                   id="customer_name" 
                                   name="customer_name" 
                                   value="{{ old('customer_name', $deliveryNote->customer_name) }}"
                                   required>
                            @error('customer_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="customer_phone">เบอร์โทร</label>
                            <input type="text" 
                                   class="form-control @error('customer_phone') is-invalid @enderror" 
                                   id="customer_phone" 
                                   name="customer_phone" 
                                   value="{{ old('customer_phone', $deliveryNote->customer_phone) }}"
                                   placeholder="0812345678">
                            @error('customer_phone')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="customer_address">ที่อยู่จัดส่ง</label>
                            <textarea class="form-control @error('customer_address') is-invalid @enderror" 
                                      id="customer_address" 
                                      name="customer_address" 
                                      rows="3">{{ old('customer_address', $deliveryNote->customer_address) }}</textarea>
                            @error('customer_address')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- ข้อมูลการจัดส่ง -->
                    <div class="col-md-6">
                        <h5><i class="fas fa-truck"></i> ข้อมูลการจัดส่ง</h5>

                        <div class="form-group">
                            <label for="delivery_date">วันที่จัดส่ง <span class="text-danger">*</span></label>
                            <input type="date" 
                                   class="form-control @error('delivery_date') is-invalid @enderror" 
                                   id="delivery_date" 
                                   name="delivery_date" 
                                   value="{{ old('delivery_date', $deliveryNote->delivery_date->format('Y-m-d')) }}"
                                   required>
                            @error('delivery_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="quotation_number">เลขที่ใบเสนอราคา</label>
                            <input type="text" 
                                   class="form-control @error('quotation_number') is-invalid @enderror" 
                                   id="quotation_number" 
                                   name="quotation_number" 
                                   value="{{ old('quotation_number', $deliveryNote->quotation_number) }}"
                                   placeholder="QT-2024-001">
                            @error('quotation_number')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="notes">หมายเหตุ</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3">{{ old('notes', $deliveryNote->notes) }}</textarea>
                            @error('notes')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- รายการสินค้า -->
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-shopping-cart"></i> รายการสินค้า</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="items-table">
                        <thead class="bg-light">
                            <tr>
                                <th width="40%">สินค้า <span class="text-danger">*</span></th>
                                <th width="15%" class="text-center">จำนวน <span class="text-danger">*</span></th>
                                <th width="15%" class="text-right">ราคา/หน่วย</th>
                                <th width="20%" class="text-right">ยอดรวม</th>
                                <th width="10%" class="text-center">ลบ</th>
                            </tr>
                        </thead>
                        <tbody id="items-body">
                            @foreach(old('items', $deliveryNote->items) as $index => $item)
                            <tr class="item-row">
                                <td>
                                    <select class="form-control select2 product-select" 
                                            name="items[{{ $index }}][product_id]" 
                                            required>
                                        <option value="">-- เลือกสินค้า --</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" 
                                                    data-price="{{ $product->price }}"
                                                    {{ old("items.$index.product_id", is_object($item) ? $item->product_id : ($item['product_id'] ?? '')) == $product->id ? 'selected' : '' }}>
                                                {{ $product->name }} ({{ $product->sku }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" 
                                           class="form-control text-center quantity-input" 
                                           name="items[{{ $index }}][quantity]" 
                                           value="{{ old("items.$index.quantity", is_object($item) ? $item->quantity : ($item['quantity'] ?? 1)) }}"
                                           min="1" 
                                           required>
                                </td>
                                <td>
                                    <input type="number" 
                                           class="form-control text-right price-input" 
                                           name="items[{{ $index }}][unit_price]" 
                                           value="{{ old("items.$index.unit_price", is_object($item) ? $item->unit_price : ($item['unit_price'] ?? 0)) }}"
                                           step="0.01" 
                                           min="0" 
                                           required>
                                </td>
                                <td>
                                    <input type="text" 
                                           class="form-control text-right total-input" 
                                           value="{{ old("items.$index.total", is_object($item) ? number_format($item->total, 2) : number_format(($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0), 2)) }}"
                                           readonly>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger btn-sm remove-item">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5">
                                    <button type="button" class="btn btn-success" id="add-item">
                                        <i class="fas fa-plus"></i> เพิ่มรายการ
                                    </button>
                                </td>
                            </tr>
                            <tr class="bg-light">
                                <td colspan="3" class="text-right"><strong>ยอดรวมทั้งหมด:</strong></td>
                                <td class="text-right">
                                    <strong id="grand-total">0.00</strong> บาท
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- ปุ่มดำเนินการ -->
        <div class="card">
            <div class="card-body">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> บันทึกการแก้ไข
                </button>
                <a href="{{ route('admin.delivery-notes.show', $deliveryNote->id) }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> ยกเลิก
                </a>
            </div>
        </div>
    </form>
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
let itemIndex = {{ count(old('items', $deliveryNote->items)) }};

$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    // คำนวณยอดรวมเริ่มต้น
    calculateGrandTotal();

    // เพิ่มรายการ
    $('#add-item').click(function() {
        addItem();
    });

    // ลบรายการ
    $(document).on('click', '.remove-item', function() {
        if ($('.item-row').length > 1) {
            $(this).closest('.item-row').remove();
            calculateGrandTotal();
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'ไม่สามารถลบได้',
                text: 'ต้องมีอย่างน้อย 1 รายการ'
            });
        }
    });

    // เมื่อเลือกสินค้า
    $(document).on('change', '.product-select', function() {
        const $row = $(this).closest('.item-row');
        const price = $(this).find(':selected').data('price') || 0;
        $row.find('.price-input').val(parseFloat(price).toFixed(2));
        calculateRowTotal($row);
    });

    // เมื่อเปลี่ยนจำนวนหรือราคา
    $(document).on('input', '.quantity-input, .price-input', function() {
        const $row = $(this).closest('.item-row');
        calculateRowTotal($row);
    });
});

function addItem() {
    const row = `
        <tr class="item-row">
            <td>
                <select class="form-control select2 product-select" 
                        name="items[${itemIndex}][product_id]" 
                        required>
                    <option value="">-- เลือกสินค้า --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                            {{ $product->name }} ({{ $product->sku }})
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" 
                       class="form-control text-center quantity-input" 
                       name="items[${itemIndex}][quantity]" 
                       value="1" 
                       min="1" 
                       required>
            </td>
            <td>
                <input type="number" 
                       class="form-control text-right price-input" 
                       name="items[${itemIndex}][unit_price]" 
                       value="0.00" 
                       step="0.01" 
                       min="0" 
                       required>
            </td>
            <td>
                <input type="text" 
                       class="form-control text-right total-input" 
                       value="0.00" 
                       readonly>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm remove-item">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
    
    $('#items-body').append(row);
    
    // Initialize Select2 for new row
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
    
    itemIndex++;
}

function calculateRowTotal($row) {
    const quantity = parseFloat($row.find('.quantity-input').val()) || 0;
    const price = parseFloat($row.find('.price-input').val()) || 0;
    const total = quantity * price;
    
    $row.find('.total-input').val(total.toFixed(2));
    calculateGrandTotal();
}

function calculateGrandTotal() {
    let grandTotal = 0;
    
    $('.item-row').each(function() {
        const quantity = parseFloat($(this).find('.quantity-input').val()) || 0;
        const price = parseFloat($(this).find('.price-input').val()) || 0;
        grandTotal += quantity * price;
    });
    
    $('#grand-total').text(grandTotal.toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
}

// Validate form
$('#delivery-note-form').submit(function(e) {
    if ($('.item-row').length === 0) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'ไม่สามารถบันทึกได้',
            text: 'กรุณาเพิ่มรายการสินค้าอย่างน้อย 1 รายการ'
        });
        return false;
    }
});
</script>
@stop
