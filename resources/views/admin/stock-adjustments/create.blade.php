@extends('adminlte::page')

@section('title', 'สร้างคำขอปรับปรุงสต็อก')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>สร้างคำขอปรับปรุงสต็อก</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.stock-adjustments.index') }}">คำขอปรับปรุงสต็อก</a></li>
                <li class="breadcrumb-item active">สร้างใหม่</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">ข้อมูลคำขอปรับปรุงสต็อก</h3>
        </div>
        <form action="{{ route('admin.stock-adjustments.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>หมายเหตุ:</strong> คำขอจะต้องได้รับการอนุมัติก่อนดำเนินการปรับปรุงสต็อกจริง
                </div>

                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="type">ประเภทการปรับปรุง <span class="text-danger">*</span></label>
                            <select class="form-control @error('type') is-invalid @enderror" 
                                    id="type" 
                                    name="type" 
                                    required
                                    onchange="updateReasonOptions()">
                                <option value="">เลือกประเภท</option>
                                <option value="in" {{ old('type') == 'in' ? 'selected' : '' }}>เพิ่มสต็อก</option>
                                <option value="out" {{ old('type') == 'out' ? 'selected' : '' }}>ลดสต็อก</option>
                                <option value="adjustment" {{ old('type') == 'adjustment' ? 'selected' : '' }}>ปรับปรุงสต็อก (ตั้งค่าใหม่)</option>
                            </select>
                            @error('type')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="reason">เหตุผล <span class="text-danger">*</span></label>
                            <select class="form-control @error('reason') is-invalid @enderror" 
                                    id="reason" 
                                    name="reason" 
                                    required>
                                <option value="">เลือกเหตุผล</option>
                                <!-- Options will be populated by JavaScript -->
                            </select>
                            @error('reason')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="product_id">สินค้า <span class="text-danger">*</span></label>
                                    <select class="form-control @error('product_id') is-invalid @enderror" 
                                            id="product_id" 
                                            name="product_id" 
                                            required
                                            onchange="updateProductInfo()">
                                        <option value="">เลือกสินค้า</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" 
                                                    {{ old('product_id', request('product_id')) == $product->id ? 'selected' : '' }}
                                                    data-unit="{{ $product->unit }}">
                                                {{ $product->name }} ({{ $product->sku }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('product_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="warehouse_id">คลัง <span class="text-danger">*</span></label>
                                    <select class="form-control @error('warehouse_id') is-invalid @enderror" 
                                            id="warehouse_id" 
                                            name="warehouse_id" 
                                            required
                                            onchange="updateWarehouseStock()">
                                        <option value="">เลือกคลัง</option>
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                                {{ $warehouse->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('warehouse_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="requested_quantity">จำนวน <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control @error('requested_quantity') is-invalid @enderror" 
                                       id="requested_quantity" 
                                       name="requested_quantity" 
                                       value="{{ old('requested_quantity') }}" 
                                       min="1"
                                       placeholder="ระบุจำนวน"
                                       required>
                                <div class="input-group-append">
                                    <span class="input-group-text" id="unit-display">หน่วย</span>
                                </div>
                            </div>
                            @error('requested_quantity')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">รายละเอียด <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="4"
                                      placeholder="ระบุรายละเอียดเหตุผลในการปรับปรุงสต็อก"
                                      required>{{ old('description') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="reference_document">เอกสารอ้างอิง</label>
                            <input type="text" 
                                   class="form-control @error('reference_document') is-invalid @enderror" 
                                   id="reference_document" 
                                   name="reference_document" 
                                   value="{{ old('reference_document') }}" 
                                   placeholder="เช่น ใบสั่งซื้อ, ใบรับของ, หมายเลขเอกสาร">
                            @error('reference_document')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Right Column - Information -->
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h5>ข้อมูลสินค้า</h5>
                            </div>
                            <div class="card-body">
                                <div id="product-info" style="display: none;">
                                    <div class="mb-3">
                                        <strong>สินค้า:</strong>
                                        <span id="product-name">-</span>
                                    </div>
                                    <div class="mb-3">
                                        <strong>รหัสสินค้า:</strong>
                                        <span id="product-sku">-</span>
                                    </div>
                                    <div class="mb-3">
                                        <strong>หน่วย:</strong>
                                        <span id="product-unit">-</span>
                                    </div>
                                </div>
                                <div id="no-product" class="text-muted text-center">
                                    <i class="fas fa-box-open fa-2x"></i>
                                    <p class="mt-2">เลือกสินค้าเพื่อดูข้อมูล</p>
                                </div>
                            </div>
                        </div>

                        <div class="card bg-light">
                            <div class="card-header">
                                <h5>สต็อกปัจจุบัน</h5>
                            </div>
                            <div class="card-body">
                                <div id="stock-info" style="display: none;">
                                    <div class="mb-3">
                                        <strong>คลัง:</strong>
                                        <span id="warehouse-name">-</span>
                                    </div>
                                    <div class="mb-3">
                                        <strong>จำนวนปัจจุบัน:</strong>
                                        <span id="current-stock" class="badge badge-info">-</span>
                                    </div>
                                </div>
                                <div id="no-stock" class="text-muted text-center">
                                    <i class="fas fa-warehouse fa-2x"></i>
                                    <p class="mt-2">เลือกสินค้าและคลังเพื่อดูสต็อก</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-6">
                        <a href="{{ route('admin.stock-adjustments.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> ยกเลิก
                        </a>
                    </div>
                    <div class="col-md-6 text-right">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> สร้างคำขอ
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop

@section('js')
    <script>
        const reasonOptions = {
            'in': [
                { value: 'purchase', text: 'รับซื้อ' },
                { value: 'production', text: 'ผลิต' },
                { value: 'found', text: 'พบเพิ่ม' },
                { value: 'correction', text: 'แก้ไขข้อผิดพลาด' },
                { value: 'other', text: 'อื่นๆ' }
            ],
            'out': [
                { value: 'sales', text: 'ขาย' },
                { value: 'damage', text: 'ชำรุด' },
                { value: 'expired', text: 'หมดอายุ' },
                { value: 'lost', text: 'สูญหาย' },
                { value: 'correction', text: 'แก้ไขข้อผิดพลาด' },
                { value: 'other', text: 'อื่นๆ' }
            ],
            'adjustment': [
                { value: 'correction', text: 'แก้ไขข้อผิดพลาด' },
                { value: 'other', text: 'อื่นๆ' }
            ]
        };

        function updateReasonOptions() {
            const type = document.getElementById('type').value;
            const reasonSelect = document.getElementById('reason');
            
            // Clear current options
            reasonSelect.innerHTML = '<option value="">เลือกเหตุผล</option>';
            
            if (type && reasonOptions[type]) {
                reasonOptions[type].forEach(option => {
                    const optionElement = document.createElement('option');
                    optionElement.value = option.value;
                    optionElement.textContent = option.text;
                    reasonSelect.appendChild(optionElement);
                });
            }
        }

        function updateProductInfo() {
            const productSelect = document.getElementById('product_id');
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            
            if (selectedOption.value) {
                const productName = selectedOption.textContent.split(' (')[0];
                const productSku = selectedOption.textContent.match(/\(([^)]+)\)$/)?.[1] || '';
                const productUnit = selectedOption.getAttribute('data-unit') || '';
                
                document.getElementById('product-name').textContent = productName;
                document.getElementById('product-sku').textContent = productSku;
                document.getElementById('product-unit').textContent = productUnit;
                document.getElementById('unit-display').textContent = productUnit;
                
                document.getElementById('product-info').style.display = 'block';
                document.getElementById('no-product').style.display = 'none';
            } else {
                document.getElementById('product-info').style.display = 'none';
                document.getElementById('no-product').style.display = 'block';
                document.getElementById('unit-display').textContent = 'หน่วย';
            }
            
            updateWarehouseStock();
        }

        function updateWarehouseStock() {
            const productId = document.getElementById('product_id').value;
            const warehouseId = document.getElementById('warehouse_id').value;
            
            if (productId && warehouseId) {
                // Here you would typically make an AJAX call to get the current stock
                // For now, we'll just show placeholder
                const warehouseName = document.getElementById('warehouse_id').options[document.getElementById('warehouse_id').selectedIndex].text;
                
                document.getElementById('warehouse-name').textContent = warehouseName;
                document.getElementById('current-stock').textContent = 'กำลังโหลด...';
                
                document.getElementById('stock-info').style.display = 'block';
                document.getElementById('no-stock').style.display = 'none';
                
                // AJAX call to get current stock
                fetch(`/admin/api/warehouse-stock/${warehouseId}/${productId}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('current-stock').textContent = data.stock + ' ' + data.unit;
                    })
                    .catch(error => {
                        document.getElementById('current-stock').textContent = '0 หน่วย';
                    });
            } else {
                document.getElementById('stock-info').style.display = 'none';
                document.getElementById('no-stock').style.display = 'block';
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateReasonOptions();
            updateProductInfo();
        });
    </script>
@stop