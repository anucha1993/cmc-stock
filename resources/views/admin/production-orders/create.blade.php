@extends('adminlte::page')

@section('title', 'สร้างใบสั่งผลิต')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>สร้างใบสั่งผลิต</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.production-orders.index') }}">ใบสั่งผลิต</a></li>
                <li class="breadcrumb-item active">สร้างใบสั่งผลิต</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">สร้างใบสั่งผลิต</h3>
                </div>
                <form action="{{ route('admin.production-orders.store') }}" method="POST" id="production-form">
                    @csrf
                    <div class="card-body">
                        <!-- ข้อมูลพื้นฐาน -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="target_warehouse_id">คลังปลายทาง <span class="text-danger">*</span></label>
                                    <select class="form-control @error('target_warehouse_id') is-invalid @enderror" 
                                            id="target_warehouse_id" 
                                            name="target_warehouse_id" 
                                            required>
                                        <option value="">เลือกคลังปลายทาง</option>
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}" 
                                                    {{ old('target_warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                                {{ $warehouse->name }} ({{ $warehouse->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('target_warehouse_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="storage_location">
                                        <i class="fas fa-map-marker-alt"></i> ตำแหน่งเก็บในคลัง
                                        <small class="text-muted">(ไม่บังคับ)</small>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('storage_location') is-invalid @enderror" 
                                           id="storage_location" 
                                           name="storage_location" 
                                           value="{{ old('storage_location') }}"
                                           placeholder="เช่น A1-01, SHELF-A-001, ZONE-B-05">
                                    <small class="form-text text-muted">
                                        ระบุตำแหน่งที่จะจัดเก็บสินค้าเมื่อผลิตเสร็จ
                                    </small>
                                    @error('storage_location')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="due_date">วันที่ต้องการ <span class="text-danger">*</span></label>
                                    <input type="date" 
                                           class="form-control @error('due_date') is-invalid @enderror" 
                                           id="due_date" 
                                           name="due_date" 
                                           value="{{ old('due_date', now()->addDays(7)->format('Y-m-d')) }}" 
                                           min="{{ now()->format('Y-m-d') }}"
                                           required>
                                    @error('due_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="priority">ความสำคัญ <span class="text-danger">*</span></label>
                                    <select class="form-control @error('priority') is-invalid @enderror" 
                                            id="priority" 
                                            name="priority" 
                                            required>
                                        <option value="normal" {{ old('priority', 'normal') == 'normal' ? 'selected' : '' }}>ปกติ</option>
                                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>ต่ำ</option>
                                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>สูง</option>
                                        <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>เร่งด่วน</option>
                                    </select>
                                    @error('priority')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- การเลือกประเภทการสั่งผลิต -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card card-tabs">
                                    <div class="card-header p-0 pt-1">
                                        <ul class="nav nav-tabs" id="production-tabs" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" 
                                                   id="package-tab" 
                                                   data-toggle="tab" 
                                                   href="#package-content" 
                                                   role="tab">
                                                    <i class="fas fa-box-open"></i> สั่งผลิตจากแพ
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" 
                                                   id="products-tab" 
                                                   data-toggle="tab" 
                                                   href="#products-content" 
                                                   role="tab">
                                                    <i class="fas fa-cubes"></i> สั่งผลิตหลายรายการ
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="card-body">
                                        <div class="tab-content" id="production-tabs-content">
                                            <!-- Tab 1: สั่งผลิตจากแพ -->
                                            <div class="tab-pane fade show active" 
                                                 id="package-content" 
                                                 role="tabpanel">
                                                <input type="hidden" name="order_type" value="package" id="order_type">
                                                
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <div class="form-group">
                                                            <label for="package_id">เลือกแพสินค้า <span class="text-danger">*</span></label>
                                                            <select class="form-control select2 @error('package_id') is-invalid @enderror" 
                                                                    id="package_id" 
                                                                    name="package_id">
                                                                <option value="">เลือกแพสินค้า...</option>
                                                                @foreach($packages as $package)
                                                                    <option value="{{ $package->id }}" 
                                                                            {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                                                        {{ $package->name }} ({{ $package->code }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('package_id')
                                                                <span class="invalid-feedback">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="quantity">จำนวนแพ <span class="text-danger">*</span></label>
                                                            <input type="number" 
                                                                   class="form-control @error('quantity') is-invalid @enderror" 
                                                                   id="quantity" 
                                                                   name="quantity" 
                                                                   value="{{ old('quantity', 1) }}" 
                                                                   min="1"
                                                                   placeholder="จำนวนแพ">
                                                            @error('quantity')
                                                                <span class="invalid-feedback">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- แสดงรายละเอียดสินค้าในแพ -->
                                                <div id="package-products-display" style="display: none;">
                                                    <h5><i class="fas fa-list"></i> สินค้าในแพ:</h5>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-bordered">
                                                            <thead class="thead-light">
                                                                <tr>
                                                                    <th>สินค้า</th>
                                                                    <th>จำนวน/แพ</th>
                                                                    <th>หน่วย</th>
                                                                    <th>รวมที่จะผลิต</th>
                                                                    <th>ราคาต้นทุน</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="package-products-list">
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Tab 2: สั่งผลิตหลายรายการ -->
                                            <div class="tab-pane fade" 
                                                 id="products-content" 
                                                 role="tabpanel">
                                                
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h5><i class="fas fa-cubes"></i> รายการสินค้าที่ต้องการผลิต</h5>
                                                    <button type="button" 
                                                            class="btn btn-success btn-sm" 
                                                            onclick="addProductRow()">
                                                        <i class="fas fa-plus"></i> เพิ่มสินค้า
                                                    </button>
                                                </div>

                                                <div id="products-container">
                                                    <!-- Dynamic product rows จะถูกเพิ่มที่นี่ -->
                                                </div>

                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle"></i>
                                                    <strong>คำแนะนำ:</strong> คลิก "เพิ่มสินค้า" เพื่อเพิ่มสินค้าที่ต้องการผลิต
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- หมายเหตุ -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description">หมายเหตุ</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" 
                                              name="description" 
                                              rows="3" 
                                              placeholder="หมายเหตุเพิ่มเติมเกี่ยวกับการผลิต">{{ old('description') }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <a href="{{ route('admin.production-orders.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> ย้อนกลับ
                                </a>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="submit" class="btn btn-primary" id="submit-btn">
                                    <i class="fas fa-save"></i> สร้างใบสั่งผลิต
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .select2-container {
            width: 100% !important;
        }
        
        .product-row {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
            padding: 1rem;
            background-color: #f8f9fa;
        }
        
        .remove-product {
            cursor: pointer;
            color: #dc3545;
        }
        
        .nav-tabs .nav-link.active {
            background-color: #007bff;
            color: white;
            border-color: #007bff #007bff #fff;
        }
        
        .table th {
            background-color: #f1f1f1;
        }
    </style>
@stop

@section('js')
    <script>
        let productRowIndex = 0;

        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                placeholder: 'เลือกแพสินค้า',
                allowClear: true
            });

            // Handle tab switching
            $('#production-tabs a').on('click', function (e) {
                e.preventDefault();
                $(this).tab('show');
                
                // Update order_type based on active tab
                if ($(this).attr('id') === 'package-tab') {
                    $('#order_type').val('package');
                } else {
                    $('#order_type').val('multiple');
                }
            });

            // Package selection change
            $('#package_id').change(function() {
                loadPackageProducts();
            });

            // Quantity change
            $('#quantity').on('input', function() {
                updatePackageProductQuantities();
            });

            // Add initial product row when switching to products tab
            $('#products-tab').on('shown.bs.tab', function() {
                if ($('#products-container').children().length === 0) {
                    addProductRow();
                }
            });
        });

        function loadPackageProducts() {
            const packageId = $('#package_id').val();
            if (!packageId) {
                $('#package-products-display').hide();
                return;
            }

            // Show loading
            $('#package-products-list').html('<tr><td colspan="5" class="text-center">กำลังโหลด...</td></tr>');
            $('#package-products-display').show();

            $.get(`{{ url('admin/api/packages') }}/${packageId}/products`)
                .done(function(data) {
                    displayPackageProducts(data.products);
                })
                .fail(function() {
                    $('#package-products-list').html('<tr><td colspan="5" class="text-center text-danger">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>');
                });
        }

        function displayPackageProducts(products) {
            const quantity = parseInt($('#quantity').val()) || 1;
            let html = '';

            products.forEach(function(product) {
                const totalQuantity = product.quantity_per_package * quantity;
                const totalCost = product.unit_cost * totalQuantity;
                
                html += `
                    <tr>
                        <td>
                            <strong>${product.product_name}</strong><br>
                            <small class="text-muted">${product.product_sku}</small>
                        </td>
                        <td>${product.quantity_per_package}</td>
                        <td>${product.unit}</td>
                        <td><span class="badge badge-info total-qty">${totalQuantity}</span></td>
                        <td>฿${totalCost.toLocaleString()}</td>
                    </tr>
                `;
            });

            $('#package-products-list').html(html);
        }

        function updatePackageProductQuantities() {
            const quantity = parseInt($('#quantity').val()) || 1;
            
            $('#package-products-list tr').each(function() {
                const $row = $(this);
                const qtyPerPackage = parseFloat($row.find('td:eq(1)').text());
                if (!isNaN(qtyPerPackage)) {
                    const totalQty = qtyPerPackage * quantity;
                    $row.find('.total-qty').text(totalQty);
                }
            });
        }

        function addProductRow() {
            productRowIndex++;
            
            const html = `
                <div class="product-row" id="product-row-${productRowIndex}">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>สินค้า <span class="text-danger">*</span></label>
                                <select class="form-control product-select" 
                                        name="items[${productRowIndex}][product_id]" 
                                        required
                                        onchange="loadProductInfo(this, ${productRowIndex})">
                                    <option value="">เลือกสินค้า...</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" 
                                                data-sku="{{ $product->sku }}"
                                                data-unit="{{ $product->unit }}"
                                                data-cost="{{ $product->cost_price }}">
                                            {{ $product->name }} ({{ $product->sku }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>จำนวน <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control" 
                                       name="items[${productRowIndex}][quantity]" 
                                       min="1" 
                                       value="1"
                                       required
                                       onchange="calculateRowTotal(${productRowIndex})">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>ต้นทุน/หน่วย</label>
                                <input type="number" 
                                       class="form-control" 
                                       name="items[${productRowIndex}][unit_cost]" 
                                       step="0.01" 
                                       min="0"
                                       onchange="calculateRowTotal(${productRowIndex})">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>รวม</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="total-${productRowIndex}"
                                       readonly>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="button" 
                                            class="btn btn-danger btn-sm remove-product" 
                                            onclick="removeProductRow(${productRowIndex})"
                                            title="ลบรายการ">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('#products-container').append(html);
            
            // Initialize select2 for the new row
            $(`#product-row-${productRowIndex} .product-select`).select2({
                placeholder: 'เลือกสินค้า',
                allowClear: true
            });
        }

        function removeProductRow(index) {
            if ($('#products-container .product-row').length > 1) {
                $(`#product-row-${index}`).remove();
            } else {
                alert('ต้องมีสินค้าอย่างน้อย 1 รายการ');
            }
        }

        function loadProductInfo(select, index) {
            const $select = $(select);
            const $option = $select.find('option:selected');
            const cost = $option.data('cost');
            
            if (cost) {
                $(`input[name="items[${index}][unit_cost]"]`).val(cost);
                calculateRowTotal(index);
            }
        }

        function calculateRowTotal(index) {
            const quantity = parseFloat($(`input[name="items[${index}][quantity]"]`).val()) || 0;
            const unitCost = parseFloat($(`input[name="items[${index}][unit_cost]"]`).val()) || 0;
            const total = quantity * unitCost;
            
            $(`#total-${index}`).val(total > 0 ? '฿' + total.toLocaleString() : '');
        }

        // Form validation before submit
        $('#production-form').on('submit', function(e) {
            const orderType = $('#order_type').val();
            
            if (orderType === 'package') {
                if (!$('#package_id').val()) {
                    e.preventDefault();
                    alert('กรุณาเลือกแพสินค้า');
                    $('#package-tab').tab('show');
                    $('#package_id').focus();
                    return false;
                }
            } else if (orderType === 'multiple') {
                if ($('#products-container .product-row').length === 0) {
                    e.preventDefault();
                    alert('กรุณาเพิ่มสินค้าอย่างน้อย 1 รายการ');
                    $('#products-tab').tab('show');
                    return false;
                }
            }
        });
    </script>
@stop