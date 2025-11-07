@extends('adminlte::page')

@section('title', 'สต็อกสินค้าในคลัง')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>สต็อกสินค้าในคลัง: {{ $warehouse->name }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.warehouses.index') }}">คลังสินค้า</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.warehouses.show', $warehouse) }}">{{ $warehouse->name }}</a></li>
                <li class="breadcrumb-item active">สต็อกสินค้า</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Statistics -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total_products'] }}</h3>
                    <p>ชนิดสินค้า</p>
                </div>
                <div class="icon">
                    <i class="fas fa-cube"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($stats['total_quantity']) }}</h3>
                    <p>จำนวนรวม</p>
                </div>
                <div class="icon">
                    <i class="fas fa-cubes"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['low_stock'] }}</h3>
                    <p>สต็อกต่ำ</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['out_of_stock'] }}</h3>
                    <p>สินค้าหมด</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">ค้นหาและกรองข้อมูล</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.warehouses.stock', $warehouse) }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">ค้นหา</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="search" 
                                   name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="ชื่อสินค้า, รหัส...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="category_id">หมวดหมู่</label>
                            <select class="form-control" id="category_id" name="category_id">
                                <option value="">ทั้งหมด</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="stock_status">สถานะสต็อก</label>
                            <select class="form-control" id="stock_status" name="stock_status">
                                <option value="">ทั้งหมด</option>
                                <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>มีสต็อก</option>
                                <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>สต็อกต่ำ</option>
                                <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>สินค้าหมด</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label><br>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> ค้นหา
                            </button>
                            <a href="{{ route('admin.warehouses.stock', $warehouse) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> ล้าง
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Stock List -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">รายการสต็อกสินค้า ({{ $stocks->total() }} รายการ)</h3>
            <div class="card-tools">
                <a href="{{ route('admin.warehouses.bulk-stock', $warehouse) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-layer-group"></i> จัดการสต็อกแบบกลุ่ม
                </a>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>รหัสสินค้า</th>
                        <th>ชื่อสินค้า</th>
                        <th>หมวดหมู่</th>
                        <th>จำนวน</th>
                        <th>หน่วย</th>
                        <th>สถานะ</th>
                        <th>วันที่อัพเดต</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $warehouseProduct)
                        <tr>
                            <td>{{ $warehouseProduct->product->code }}</td>
                            <td>{{ $warehouseProduct->product->name }}</td>
                            <td>
                                @if($warehouseProduct->product->category)
                                    <span class="badge" style="background-color: {{ $warehouseProduct->product->category->color }}; color: {{ $warehouseProduct->product->category->getTextColor() }};">
                                        {{ $warehouseProduct->product->category->name }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $warehouseProduct->quantity > 0 ? 'badge-success' : 'badge-danger' }}">
                                    {{ number_format($warehouseProduct->quantity) }}
                                </span>
                            </td>
                            <td>{{ $warehouseProduct->product->unit }}</td>
                            <td>
                                @if($warehouseProduct->quantity <= 0)
                                    <span class="badge badge-danger">สินค้าหมด</span>
                                @elseif($warehouseProduct->quantity <= 10)
                                    <span class="badge badge-warning">สต็อกต่ำ</span>
                                @else
                                    <span class="badge badge-success">ปกติ</span>
                                @endif
                            </td>
                            <td>{{ $warehouseProduct->updated_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.products.show', $warehouseProduct->product) }}" 
                                       class="btn btn-sm btn-info" 
                                       title="ดูสินค้า">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-success" 
                                            onclick="quickAddStock({{ $warehouseProduct->product_id }}, '{{ $warehouseProduct->product->name }}', '{{ $warehouseProduct->product->unit }}')"
                                            title="เพิ่มสต็อกด่วน">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm btn-warning" 
                                            onclick="manageStock({{ $warehouseProduct->product_id }}, '{{ $warehouseProduct->product->name }}', {{ $warehouseProduct->quantity }}, '{{ $warehouseProduct->product->unit }}')"
                                            title="จัดการสต็อก">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">ไม่พบข้อมูลสต็อกสินค้า</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($stocks->hasPages())
            <div class="card-footer">
                {{ $stocks->links() }}
            </div>
        @endif
    </div>
@stop

@section('css')
    <style>
        .badge {
            font-size: 0.875em;
        }
        .table th {
            border-top: none;
        }
        .btn-group .btn {
            margin-right: 2px;
        }
    </style>
@stop

@section('js')
    <script>
        function quickAddStock(productId, productName, unit) {
            Swal.fire({
                title: 'เพิ่มสต็อกด่วน',
                html: `
                    <div class="text-left">
                        <p><strong>สินค้า:</strong> ${productName}</p>
                        <div class="form-group">
                            <label>จำนวนที่ต้องการเพิ่ม:</label>
                            <input type="number" id="quickQuantity" class="form-control" min="1" placeholder="ระบุจำนวน">
                            <small class="text-muted">หน่วย: ${unit}</small>
                        </div>
                        <div class="form-group">
                            <label>หมายเหตุ:</label>
                            <input type="text" id="quickNotes" class="form-control" placeholder="เหตุผลในการเพิ่มสต็อก (ไม่บังคับ)">
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-plus"></i> เพิ่มสต็อก',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#28a745',
                preConfirm: () => {
                    const quantity = document.getElementById('quickQuantity').value;
                    const notes = document.getElementById('quickNotes').value;
                    
                    if (!quantity || quantity < 1) {
                        Swal.showValidationMessage('กรุณาระบุจำนวนที่ถูกต้อง');
                        return false;
                    }
                    
                    return { quantity: quantity, notes: notes };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // ส่งข้อมูลผ่าน AJAX
                    fetch('{{ route("admin.warehouses.quick-add-stock", $warehouse) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            product_id: productId,
                            quantity: parseInt(result.value.quantity),
                            notes: result.value.notes
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'สำเร็จ!',
                                text: data.message,
                                icon: 'success',
                                timer: 2000
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('เกิดข้อผิดพลาด!', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('เกิดข้อผิดพลาด!', 'ไม่สามารถเพิ่มสต็อกได้', 'error');
                    });
                }
            });
        }

        function manageStock(productId, productName, currentStock, unit) {
            Swal.fire({
                title: 'จัดการสต็อกสินค้า',
                html: `
                    <div class="text-left">
                        <p><strong>สินค้า:</strong> ${productName}</p>
                        <p><strong>สต็อกปัจจุบัน:</strong> ${new Intl.NumberFormat().format(currentStock)} ${unit}</p>
                        
                        <form id="stockManageForm">
                            <div class="form-group">
                                <label>การดำเนินการ:</label>
                                <select id="stockType" class="form-control">
                                    <option value="in">เพิ่มสต็อก</option>
                                    <option value="out">ลดสต็อก</option>
                                    <option value="adjustment">ปรับปรุงสต็อก (ตั้งค่าใหม่)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>จำนวน:</label>
                                <input type="number" id="stockQuantity" class="form-control" min="1" placeholder="ระบุจำนวน">
                                <small class="text-muted">หน่วย: ${unit}</small>
                            </div>
                            <div class="form-group">
                                <label>หมายเหตุ:</label>
                                <textarea id="stockNotes" class="form-control" rows="3" placeholder="เหตุผลหรือรายละเอียดเพิ่มเติม"></textarea>
                            </div>
                        </form>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-save"></i> บันทึก',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#007bff',
                width: '500px',
                preConfirm: () => {
                    const type = document.getElementById('stockType').value;
                    const quantity = document.getElementById('stockQuantity').value;
                    const notes = document.getElementById('stockNotes').value;
                    
                    if (!quantity || quantity < 1) {
                        Swal.showValidationMessage('กรุณาระบุจำนวนที่ถูกต้อง');
                        return false;
                    }
                    
                    if (type === 'out' && parseInt(quantity) > currentStock) {
                        Swal.showValidationMessage(`ไม่สามารถลดสต็อกได้ มีเพียง ${new Intl.NumberFormat().format(currentStock)} ${unit}`);
                        return false;
                    }
                    
                    return { type: type, quantity: quantity, notes: notes };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // สร้างฟอร์มและส่งข้อมูล
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("admin.warehouses.update-stock", $warehouse) }}';
                    
                    // CSRF Token
                    const csrfField = document.createElement('input');
                    csrfField.type = 'hidden';
                    csrfField.name = '_token';
                    csrfField.value = '{{ csrf_token() }}';
                    form.appendChild(csrfField);
                    
                    // Product ID
                    const productField = document.createElement('input');
                    productField.type = 'hidden';
                    productField.name = 'product_id';
                    productField.value = productId;
                    form.appendChild(productField);
                    
                    // Type
                    const typeField = document.createElement('input');
                    typeField.type = 'hidden';
                    typeField.name = 'type';
                    typeField.value = result.value.type;
                    form.appendChild(typeField);
                    
                    // Quantity
                    const quantityField = document.createElement('input');
                    quantityField.type = 'hidden';
                    quantityField.name = 'quantity';
                    quantityField.value = result.value.quantity;
                    form.appendChild(quantityField);
                    
                    // Notes
                    const notesField = document.createElement('input');
                    notesField.type = 'hidden';
                    notesField.name = 'notes';
                    notesField.value = result.value.notes;
                    form.appendChild(notesField);
                    
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
@stop