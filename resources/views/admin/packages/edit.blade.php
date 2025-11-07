@extends('adminlte::page')

@section('title', 'แก้ไขแพ: ' . $package->name)

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>แก้ไขแพสินค้า</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.packages.index') }}">แพสินค้า</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.packages.show', $package) }}">{{ $package->name }}</a></li>
                <li class="breadcrumb-item active">แก้ไข</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <form action="{{ route('admin.packages.update', $package) }}" method="POST" id="package-form">
        @csrf
        @method('PUT')
        
        <!-- Package Information -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">ข้อมูลแพ</h3>
                <div class="card-tools">
                    <span class="badge {{ $package->is_active ? 'badge-success' : 'badge-secondary' }}">
                        {{ $package->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <!-- Info Alert -->
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>ข้อมูลปัจจุบัน:</strong> 
                    สร้างเมื่อ {{ $package->created_at->format('d/m/Y H:i') }} 
                    | อัปเดตล่าสุด {{ $package->updated_at->format('d/m/Y H:i') }}
                    @if($package->products->count() > 0)
                        | มีสินค้า {{ $package->products->count() }} รายการ
                    @endif
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">ชื่อแพ <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $package->name) }}" 
                                   placeholder="เช่น แพพี่ผิว"
                                   required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="code">รหัสแพ</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="code" 
                                   name="code" 
                                   value="{{ $package->code }}" 
                                   readonly
                                   style="background-color: #f8f9fa;">
                            <small class="text-muted">รหัสถูกสร้างอัตโนมัติ</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="color">สีแพ</label>
                            <div class="input-group">
                                <input type="color" 
                                       class="form-control @error('color') is-invalid @enderror" 
                                       id="color" 
                                       name="color" 
                                       value="{{ old('color', $package->color) }}" 
                                       style="height: 38px;">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" onclick="randomColor()">
                                        <i class="fas fa-random"></i>
                                    </button>
                                </div>
                            </div>
                            @error('color')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="description">คำอธิบาย</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3" 
                                      placeholder="คำอธิบายเกี่ยวกับแพ">{{ old('description', $package->description) }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="package_quantity">จำนวนแพ <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('package_quantity') is-invalid @enderror" 
                                   id="package_quantity" 
                                   name="package_quantity" 
                                   value="{{ old('package_quantity', $package->package_quantity) }}" 
                                   min="1"
                                   required>
                            @error('package_quantity')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="length_per_package">ความยาวต่อแพ</label>
                            <input type="number" 
                                   class="form-control @error('length_per_package') is-invalid @enderror" 
                                   id="length_per_package" 
                                   name="length_per_package" 
                                   value="{{ old('length_per_package', $package->length_per_package) }}" 
                                   step="0.01"
                                   min="0">
                            @error('length_per_package')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="length_unit">หน่วยความยาว <span class="text-danger">*</span></label>
                            <select class="form-control @error('length_unit') is-invalid @enderror" 
                                    id="length_unit" 
                                    name="length_unit" 
                                    required>
                                <option value="เมตร" {{ old('length_unit', $package->length_unit) === 'เมตร' ? 'selected' : '' }}>เมตร</option>
                                <option value="เซนติเมตร" {{ old('length_unit', $package->length_unit) === 'เซนติเมตร' ? 'selected' : '' }}>เซนติเมตร</option>
                                <option value="กิโลเมตร" {{ old('length_unit', $package->length_unit) === 'กิโลเมตร' ? 'selected' : '' }}>กิโลเมตร</option>
                            </select>
                            @error('length_unit')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="items_per_package">จำนวนต่อแพ <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('items_per_package') is-invalid @enderror" 
                                   id="items_per_package" 
                                   name="items_per_package" 
                                   value="{{ old('items_per_package', $package->items_per_package) }}" 
                                   min="1"
                                   required>
                            @error('items_per_package')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="item_unit">หน่วยสินค้า <span class="text-danger">*</span></label>
                            <select class="form-control @error('item_unit') is-invalid @enderror" 
                                    id="item_unit" 
                                    name="item_unit" 
                                    required>
                                <option value="ต้น" {{ old('item_unit', $package->item_unit) === 'ต้น' ? 'selected' : '' }}>ต้น</option>
                                <option value="ชิ้น" {{ old('item_unit', $package->item_unit) === 'ชิ้น' ? 'selected' : '' }}>ชิ้น</option>
                                <option value="อัน" {{ old('item_unit', $package->item_unit) === 'อัน' ? 'selected' : '' }}>อัน</option>
                                <option value="ท่อน" {{ old('item_unit', $package->item_unit) === 'ท่อน' ? 'selected' : '' }}>ท่อน</option>
                            </select>
                            @error('item_unit')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="weight_per_package">น้ำหนักต่อแพ</label>
                            <input type="number" 
                                   class="form-control @error('weight_per_package') is-invalid @enderror" 
                                   id="weight_per_package" 
                                   name="weight_per_package" 
                                   value="{{ old('weight_per_package', $package->weight_per_package) }}" 
                                   step="0.01"
                                   min="0">
                            @error('weight_per_package')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="weight_unit">หน่วยน้ำหนัก</label>
                            <select class="form-control @error('weight_unit') is-invalid @enderror" 
                                    id="weight_unit" 
                                    name="weight_unit">
                                <option value="กิโลกรัม" {{ old('weight_unit', $package->weight_unit) === 'กิโลกรัม' ? 'selected' : '' }}>กิโลกรัม</option>
                                <option value="กรัม" {{ old('weight_unit', $package->weight_unit) === 'กรัม' ? 'selected' : '' }}>กรัม</option>
                                <option value="ตัน" {{ old('weight_unit', $package->weight_unit) === 'ตัน' ? 'selected' : '' }}>ตัน</option>
                            </select>
                            @error('weight_unit')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <div class="custom-control custom-switch mt-4">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="is_active" 
                                       name="is_active" 
                                       {{ old('is_active', $package->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">เปิดใช้งาน</label>
                            </div>
                        </div>
                    </div>
                </div>



                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="supplier_id">ผู้จำหน่าย</label>
                            <select class="form-control @error('supplier_id') is-invalid @enderror" 
                                    id="supplier_id" 
                                    name="supplier_id">
                                <option value="">เลือกผู้จำหน่าย</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id', $package->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Package Summary -->
                <div class="alert alert-info" id="package-summary" style="display: none;">
                    <h5><i class="fas fa-info-circle"></i> สรุปแพ</h5>
                    <div id="summary-content"></div>
                </div>
            </div>
        </div>

        <!-- Products in Package -->
        <div class="card card-secondary">
            <div class="card-header">
                <h3 class="card-title">สินค้าในแพ</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-success" onclick="addProduct()">
                        <i class="fas fa-plus"></i> เพิ่มสินค้า
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="products-container">
                    <!-- Existing products will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="card">
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-6">
                        <a href="{{ route('admin.packages.show', $package) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> ย้อนกลับ
                        </a>
                    </div>
                    <div class="col-md-6 text-right">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> บันทึกแพ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Product Template -->
    <template id="product-template">
        <div class="product-item border rounded p-3 mb-3" data-index="__INDEX__">
            <div class="row">
                <div class="col-md-12">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">สินค้าที่ __NUMBER__</h6>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeProduct(__INDEX__)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>เลือกสินค้า <span class="text-danger">*</span></label>
                        <select class="form-control product-select select2" 
                                name="products[__INDEX__][product_id]" 
                                onchange="updateProductInfo(__INDEX__)" 
                                required>
                            <option value="">ค้นหาและเลือกสินค้า...</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" 
                                        data-name="{{ $product->name }}"
                                        data-unit="{{ $product->unit }}"
                                        data-category="{{ $product->category->name ?? '' }}">
                                    {{ $product->name }} | {{ $product->unit }} | {{ $product->category->name ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>จำนวนสต็อก <span class="text-danger">*</span></label>
                        <input type="number" 
                               class="form-control quantity-input" 
                               name="products[__INDEX__][quantity_per_package]" 
                               step="1" 
                               min="1" 
                               onchange="updateCalculations()"
                               required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>หน่วย</label>
                        <input type="text" 
                               class="form-control unit-display" 
                               readonly
                               style="background-color: #f8f9fa;">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <div class="custom-control custom-checkbox mt-4">
                            <input type="checkbox" 
                                   class="custom-control-input" 
                                   id="is_main_product___INDEX__" 
                                   name="products[__INDEX__][is_main_product]">
                            <label class="custom-control-label" for="is_main_product___INDEX__">สินค้าหลัก</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-10">
                    <div class="form-group">
                        <label>รายละเอียด</label>
                        <textarea class="form-control" 
                                  name="products[__INDEX__][specifications]" 
                                  rows="1" 
                                  placeholder="รายละเอียดเพิ่มเติม"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </template>
@stop

@section('css')
    <!-- Select2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" />
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        .product-item {
            background-color: #f8f9fa;
        }
        .product-item:nth-child(even) {
            background-color: #ffffff;
        }
    </style>
@stop

@section('js')
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <script>
        let productIndex = 0;
        
        // Random color function
        function randomColor() {
            var colors = [
                '#007bff', '#6c757d', '#28a745', '#dc3545', '#ffc107', 
                '#17a2b8', '#6f42c1', '#e83e8c', '#fd7e14', '#20c997'
            ];
            var randomColor = colors[Math.floor(Math.random() * colors.length)];
            $('#color').val(randomColor);
        }

        // Add product
        function addProduct() {
            const template = document.getElementById('product-template').innerHTML;
            const productHtml = template.replace(/__INDEX__/g, productIndex)
                                       .replace(/__NUMBER__/g, productIndex + 1);
            
            document.getElementById('products-container').insertAdjacentHTML('beforeend', productHtml);
            
            // Initialize Select2 for new product select
            $(`select[name="products[${productIndex}][product_id]"]`).select2({
                theme: 'bootstrap4',
                placeholder: 'ค้นหาและเลือกสินค้า...',
                allowClear: true,
                width: '100%'
            });
            
            productIndex++;
            updateCalculations();
        }

        // Remove product
        function removeProduct(index) {
            const productItem = document.querySelector(`[data-index="${index}"]`);
            if (productItem) {
                productItem.remove();
                updateCalculations();
            }
        }

        // Update product info when product is selected
        function updateProductInfo(index) {
            const select = document.querySelector(`select[name="products[${index}][product_id]"]`);
            const productItem = select.closest('.product-item');
            const unitDisplay = productItem.querySelector('.unit-display');
            
            if (select.value) {
                const option = select.selectedOptions[0];
                const unit = option.getAttribute('data-unit');
                unitDisplay.value = unit;
            } else {
                unitDisplay.value = '';
            }
            updateCalculations();
        }

        // Load existing products
        function loadExistingProducts() {
            @foreach($package->products as $index => $product)
                addProduct();
                const currentIndex = productIndex - 1;
                const productItem = document.querySelector(`[data-index="${currentIndex}"]`);
                
                // Set product selection
                const productSelect = productItem.querySelector('select[name*="[product_id]"]');
                $(productSelect).val('{{ $product->id }}').trigger('change');
                
                // Set quantity
                productItem.querySelector('input[name*="[quantity_per_package]"]').value = '{{ $product->pivot->quantity_per_package }}';
                
                // Set unit
                productItem.querySelector('.unit-display').value = '{{ $product->unit }}';
                
                // Set is_main_product
                productItem.querySelector('input[name*="[is_main_product]"]').checked = {{ $product->pivot->is_main_product ? 'true' : 'false' }};
                
                // Set specifications
                productItem.querySelector('textarea[name*="[specifications]"]').value = '{{ $product->pivot->specifications ?? '' }}';
            @endforeach
        }

        // Update calculations
        function updateCalculations() {
            const packageQuantity = parseInt(document.getElementById('package_quantity').value) || 1;
            const lengthPerPackage = parseFloat(document.getElementById('length_per_package').value) || 0;
            const lengthUnit = document.getElementById('length_unit').value;
            
            // Calculate total items per package from all products
            let totalItemsPerPackage = 0;
            document.querySelectorAll('.quantity-input').forEach(input => {
                const quantity = parseInt(input.value) || 0;
                totalItemsPerPackage += quantity;
            });
            
            // Update items per package field
            document.getElementById('items_per_package').value = totalItemsPerPackage;
            
            const totalLength = lengthPerPackage * packageQuantity;
            const totalItems = totalItemsPerPackage * packageQuantity;
            
            const summaryContent = `
                <div class="row">
                    <div class="col-md-3">
                        <strong>จำนวนแพ:</strong> ${packageQuantity} แพ
                    </div>
                    <div class="col-md-3">
                        <strong>ความยาวรวม:</strong> ${totalLength.toLocaleString()} ${lengthUnit}
                    </div>
                    <div class="col-md-3">
                        <strong>จำนวนรวม:</strong> ${totalItems.toLocaleString()} ชิ้น
                    </div>
                    <div class="col-md-3">
                        <strong>สินค้าในแพ:</strong> ${document.querySelectorAll('.product-item').length} รายการ
                    </div>
                </div>
            `;
            
            document.getElementById('summary-content').innerHTML = summaryContent;
            document.getElementById('package-summary').style.display = 'block';
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-focus first input
            document.getElementById('name').focus();
            
            // Load existing products
            loadExistingProducts();
            
            // Update calculations on input changes
            document.addEventListener('input', function(e) {
                if (e.target.matches('#package_quantity, #length_per_package, .quantity-input')) {
                    updateCalculations();
                }
            });
            
            document.addEventListener('change', function(e) {
                if (e.target.matches('#length_unit, .product-select')) {
                    updateCalculations();
                }
            });
            
            // Initial calculations update
            updateCalculations();
        });

        // Form validation
        document.getElementById('package-form').addEventListener('submit', function(e) {
            const productItems = document.querySelectorAll('.product-item');
            if (productItems.length === 0) {
                e.preventDefault();
                Swal.fire({
                    title: 'ข้อผิดพลาด',
                    text: 'กรุณาเพิ่มสินค้าในแพอย่างน้อย 1 รายการ',
                    icon: 'error'
                });
                return false;
            }
            
            // Validate that all products have quantities
            let hasError = false;
            productItems.forEach(function(item) {
                const quantityInput = item.querySelector('.quantity-input');
                if (!quantityInput.value || quantityInput.value <= 0) {
                    hasError = true;
                }
            });
            
            if (hasError) {
                e.preventDefault();
                Swal.fire({
                    title: 'ข้อผิดพลาด',
                    text: 'กรุณาระบุจำนวนสต็อกสำหรับทุกสินค้า',
                    icon: 'error'
                });
                return false;
            }
        });

        @if(session('error'))
            Swal.fire({
                title: 'เกิดข้อผิดพลาด',
                text: '{{ session('error') }}',
                icon: 'error'
            });
        @endif

        @if(session('success'))
            Swal.fire({
                title: 'สำเร็จ',
                text: '{{ session('success') }}',
                icon: 'success'
            });
        @endif

        @if($errors->any())
            let errorMessages = '';
            @foreach($errors->all() as $error)
                errorMessages += '{{ $error }}\n';
            @endforeach
            
            Swal.fire({
                title: 'ข้อผิดพลาดในการตรวจสอบข้อมูล',
                text: errorMessages,
                icon: 'error'
            });
        @endif
    </script>
@stop