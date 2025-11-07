@extends('adminlte::page')

@section('title', 'จัดการสต็อกแบบกลุ่ม - ' . $warehouse->name)

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>จัดการสต็อกแบบกลุ่ม</h1>
            <p class="text-muted">คลัง: {{ $warehouse->name }}</p>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.warehouses.index') }}">คลังสินค้า</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.warehouses.stock', $warehouse) }}">สต็อก {{ $warehouse->name }}</a></li>
                <li class="breadcrumb-item active">จัดการแบบกลุ่ม</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">เพิ่ม/ลดสต็อกหลายรายการพร้อมกัน</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-success btn-sm" onclick="addProductRow()">
                    <i class="fas fa-plus"></i> เพิ่มสินค้า
                </button>
            </div>
        </div>
        <form action="{{ route('admin.warehouses.bulk-update-stock', $warehouse) }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>วิธีใช้:</strong> เลือกสินค้าที่ต้องการจัดการสต็อก ระบุการดำเนินการและจำนวน แล้วกดบันทึกทั้งหมด
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" id="bulkStockTable">
                        <thead class="bg-light">
                            <tr>
                                <th width="25%">สินค้า</th>
                                <th width="15%">สต็อกปัจจุบัน</th>
                                <th width="15%">การดำเนินการ</th>
                                <th width="15%">จำนวน</th>
                                <th width="20%">หมายเหตุ</th>
                                <th width="10%">ลบ</th>
                            </tr>
                        </thead>
                        <tbody id="bulkStockRows">
                            <!-- Rows will be added dynamically -->
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 text-center" id="noRowsMessage">
                    <p class="text-muted">กดปุ่ม "เพิ่มสินค้า" เพื่อเริ่มจัดการสต็อกแบบกลุ่ม</p>
                </div>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-6">
                        <a href="{{ route('admin.warehouses.stock', $warehouse) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> กลับ
                        </a>
                    </div>
                    <div class="col-md-6 text-right">
                        <button type="submit" class="btn btn-primary" id="submitBtn" style="display: none;">
                            <i class="fas fa-save"></i> บันทึกทั้งหมด
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop

@section('css')
    <style>
        .select2-container {
            width: 100% !important;
        }
        .product-stock-info {
            font-size: 0.9em;
            color: #6c757d;
        }
        #bulkStockTable tbody tr:hover {
            background-color: #f8f9fa;
        }
    </style>
@stop

@section('js')
    <script>
        let rowCounter = 0;
        const warehouseId = {{ $warehouse->id }};
        const products = @json($products);

        $(document).ready(function() {
            // เพิ่มแถวแรกอัตโนมัติ
            addProductRow();
        });

        function addProductRow() {
            rowCounter++;
            const rowId = `row_${rowCounter}`;
            
            let productOptions = '<option value="">เลือกสินค้า</option>';
            products.forEach(product => {
                productOptions += `<option value="${product.id}" data-stock="${product.current_stock}" data-unit="${product.unit}">
                    ${product.name} (${product.sku})
                </option>`;
            });

            const newRow = `
                <tr id="${rowId}">
                    <td>
                        <select name="items[${rowCounter}][product_id]" class="form-control product-select" required 
                                onchange="updateProductStock(this, '${rowId}')">
                            ${productOptions}
                        </select>
                        <small class="product-stock-info" id="stock_info_${rowId}"></small>
                    </td>
                    <td>
                        <span class="current-stock" id="current_stock_${rowId}">-</span>
                        <span class="stock-unit" id="stock_unit_${rowId}"></span>
                    </td>
                    <td>
                        <select name="items[${rowCounter}][type]" class="form-control" required>
                            <option value="in">เพิ่มสต็อก</option>
                            <option value="out">ลดสต็อก</option>
                            <option value="adjustment">ปรับปรุง</option>
                        </select>
                    </td>
                    <td>
                        <input type="number" name="items[${rowCounter}][quantity]" class="form-control" 
                               min="1" placeholder="จำนวน" required>
                    </td>
                    <td>
                        <input type="text" name="items[${rowCounter}][notes]" class="form-control" 
                               placeholder="หมายเหตุ">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeRow('${rowId}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;

            $('#bulkStockRows').append(newRow);
            $('#noRowsMessage').hide();
            $('#submitBtn').show();

            // Initialize Select2 for the new row
            $(`#${rowId} .product-select`).select2({
                placeholder: 'เลือกสินค้า',
                allowClear: true
            });

            updateRowNumbers();
        }

        function removeRow(rowId) {
            $(`#${rowId}`).remove();
            
            if ($('#bulkStockRows tr').length === 0) {
                $('#noRowsMessage').show();
                $('#submitBtn').hide();
            }
            
            updateRowNumbers();
        }

        function updateProductStock(selectElement, rowId) {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const currentStock = selectedOption.getAttribute('data-stock') || 0;
            const unit = selectedOption.getAttribute('data-unit') || '';

            $(`#current_stock_${rowId}`).text(new Intl.NumberFormat().format(currentStock));
            $(`#stock_unit_${rowId}`).text(unit);

            if (selectedOption.value) {
                const productName = selectedOption.text.split(' (')[0];
                $(`#stock_info_${rowId}`).text(`${productName} - สต็อกปัจจุบัน: ${new Intl.NumberFormat().format(currentStock)} ${unit}`);
            } else {
                $(`#stock_info_${rowId}`).text('');
            }
        }

        function updateRowNumbers() {
            $('#bulkStockRows tr').each(function(index) {
                const newIndex = index + 1;
                $(this).find('select[name*="[product_id]"]').attr('name', `items[${newIndex}][product_id]`);
                $(this).find('select[name*="[type]"]').attr('name', `items[${newIndex}][type]`);
                $(this).find('input[name*="[quantity]"]').attr('name', `items[${newIndex}][quantity]`);
                $(this).find('input[name*="[notes]"]').attr('name', `items[${newIndex}][notes]`);
            });
        }

        // ตรวจสอบก่อนส่งฟอร์ม
        $('form').on('submit', function(e) {
            let hasError = false;
            let errorMessages = [];

            $('#bulkStockRows tr').each(function() {
                const productSelect = $(this).find('.product-select');
                const typeSelect = $(this).find('select[name*="[type]"]');
                const quantityInput = $(this).find('input[name*="[quantity]"]');
                
                const productId = productSelect.val();
                const type = typeSelect.val();
                const quantity = parseInt(quantityInput.val());

                if (productId && type && quantity) {
                    const currentStock = parseInt(productSelect.find('option:selected').attr('data-stock') || 0);
                    const productName = productSelect.find('option:selected').text().split(' (')[0];

                    if (type === 'out' && quantity > currentStock) {
                        hasError = true;
                        errorMessages.push(`${productName}: ไม่สามารถลดสต็อกได้ (มีเพียง ${new Intl.NumberFormat().format(currentStock)} หน่วย)`);
                    }
                }
            });

            if (hasError) {
                e.preventDefault();
                Swal.fire({
                    title: 'ข้อผิดพลาด!',
                    html: errorMessages.join('<br>'),
                    icon: 'error'
                });
            }
        });
    </script>
@stop