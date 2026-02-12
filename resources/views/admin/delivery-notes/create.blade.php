@extends('adminlte::page')

@section('title', 'สร้างใบตัดสต็อกใหม่ - CMC-STOCK')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>สร้างใบตัดสต็อก/ขาย</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">แดชบอร์ด</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.delivery-notes.index') }}">ใบตัดสต็อก</a></li>
                <li class="breadcrumb-item active">สร้างใหม่</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <form action="{{ route('admin.delivery-notes.store') }}" method="POST" id="delivery-note-form">
        @csrf
        
        <!-- ข้อมูลทั่วไป -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">ข้อมูลทั่วไป</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>ชื่อลูกค้า <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('customer_name') is-invalid @enderror" 
                                   name="customer_name" value="{{ old('customer_name') }}" required>
                            @error('customer_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label>เบอร์โทรศัพท์</label>
                        <input type="text" class="form-control @error('customer_phone') is-invalid @enderror" 
                               name="customer_phone" value="{{ old('customer_phone') }}">
                        @error('customer_phone')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>ที่อยู่ลูกค้า</label>
                            <textarea class="form-control @error('customer_address') is-invalid @enderror" 
                                      name="customer_address" rows="2">{{ old('customer_address') }}</textarea>
                            @error('customer_address')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>เลขที่ใบสั่งขาย</label>
                            <input type="text" class="form-control @error('sales_order_number') is-invalid @enderror" 
                                   name="sales_order_number" value="{{ old('sales_order_number') }}" 
                                   placeholder="SO-XXX">
                            @error('sales_order_number')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>เลขที่ใบเสนอราคา</label>
                            <input type="text" class="form-control @error('quotation_number') is-invalid @enderror" 
                                   name="quotation_number" value="{{ old('quotation_number') }}" 
                                   placeholder="QT-XXX">
                            @error('quotation_number')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>วันที่จัดส่ง <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('delivery_date') is-invalid @enderror" 
                                   name="delivery_date" value="{{ old('delivery_date', date('Y-m-d')) }}" required>
                            @error('delivery_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>หมายเหตุ</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      name="notes" rows="2">{{ old('notes') }}</textarea>
                            @error('notes')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- รายการสินค้า -->
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">รายการสินค้า</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-light" onclick="addItem()">
                        <i class="fas fa-plus"></i> เพิ่มรายการ
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="items-table">
                        <thead class="bg-light">
                            <tr>
                                <th width="50%">สินค้า</th>
                                <th width="20%" class="text-center">จำนวน</th>
                                <th width="20%">หมายเหตุ</th>
                                <th width="10%" class="text-center">ลบ</th>
                            </tr>
                        </thead>
                        <tbody id="items-body">
                            <!-- Items will be added here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ปุ่มบันทึก -->
        <div class="card">
            <div class="card-body">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> บันทึกใบตัดสต็อก
                </button>
                <a href="{{ route('admin.delivery-notes.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> ยกเลิก
                </a>
            </div>
        </div>
    </form>
@stop

@section('js')
<script>
let itemIndex = 0;
const products = @json($products);

// เพิ่มรายการ
function addItem() {
    const row = `
        <tr id="item-${itemIndex}">
            <td>
                <select class="form-control select-product" name="items[${itemIndex}][product_id]" required>
                    <option value="">-- เลือกสินค้า --</option>
                    ${products.map(p => `<option value="${p.id}">${p.name} (${p.sku})</option>`).join('')}
                </select>
            </td>
            <td>
                <input type="number" class="form-control text-center item-quantity" name="items[${itemIndex}][quantity]" 
                       value="1" min="1" required>
            </td>
            <td>
                <input type="text" class="form-control" name="items[${itemIndex}][notes]" placeholder="หมายเหตุ">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeItem(${itemIndex})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
    
    $('#items-body').append(row);
    itemIndex++;
}

// ลบรายการ
function removeItem(index) {
    $(`#item-${index}`).remove();
}

// Validation ก่อน submit
$('#delivery-note-form').on('submit', function(e) {
    if ($('#items-body tr').length === 0) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'กรุณาเพิ่มรายการสินค้า',
            text: 'ต้องมีรายการสินค้าอย่างน้อย 1 รายการ'
        });
        return false;
    }
});

// เพิ่มรายการแรกอัตโนมัติ
$(document).ready(function() {
    addItem();
});
</script>
@stop
