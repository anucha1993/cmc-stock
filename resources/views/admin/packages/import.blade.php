@extends('adminlte::page')

@section('title', 'นำเข้าแพ: ' . $package->name)

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>นำเข้าแพสินค้า</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.packages.index') }}">แพสินค้า</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.packages.show', $package) }}">{{ $package->name }}</a></li>
                <li class="breadcrumb-item active">นำเข้าแพ</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <!-- Package Information -->
        <div class="col-md-8">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-download"></i> นำเข้าแพสินค้า
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Package Details -->
                    <div class="alert alert-info">
                        <h5>
                            <span class="badge" style="background-color: {{ $package->color }}; color: {{ $package->getTextColor() }}; font-size: 16px; padding: 8px 15px;">
                                {{ $package->code }} - {{ $package->name }}
                            </span>
                        </h5>
                        @if($package->description)
                            <p class="mb-2">{{ $package->description }}</p>
                        @endif
                        <div class="row">
                            <div class="col-md-6">
                                <strong>จำนวนแพ:</strong> {{ $package->package_quantity }} แพ<br>
                                <strong>จำนวนต่อแพ:</strong> {{ $package->items_per_package }} {{ $package->item_unit }}
                            </div>
                            <div class="col-md-6">
                                @if($package->total_length)
                                    <strong>ความยาวรวม:</strong> {{ number_format($package->total_length, 2) }} {{ $package->length_unit }}<br>
                                @endif
                                <strong>จำนวนรวม:</strong> {{ $package->total_items }} {{ $package->item_unit }}
                            </div>
                        </div>
                    </div>

                    <!-- Import Form -->
                    <form action="{{ route('admin.packages.process-import', $package) }}" method="POST" id="import-form">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="warehouse_id">เลือกคลังปลายทาง <span class="text-danger">*</span></label>
                                    <select class="form-control @error('warehouse_id') is-invalid @enderror" 
                                            id="warehouse_id" 
                                            name="warehouse_id" 
                                            required>
                                        <option value="">เลือกคลัง</option>
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                                {{ $warehouse->name }}
                                                @if($warehouse->address)
                                                    ({{ $warehouse->address }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('warehouse_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quantity">จำนวนแพที่นำเข้า <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('quantity') is-invalid @enderror" 
                                           id="quantity" 
                                           name="quantity" 
                                           value="{{ old('quantity', 1) }}" 
                                           min="1"
                                           required>
                                    @error('quantity')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">1 แพ = {{ $package->items_per_package }} {{ $package->item_unit }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="notes">หมายเหตุ</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3" 
                                      placeholder="หมายเหตุเพิ่มเติม (ไม่บังคับ)">{{ old('notes') }}</textarea>
                            @error('notes')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Import Summary -->
                        <div class="alert alert-warning" id="import-summary" style="display: none;">
                            <h5><i class="fas fa-calculator"></i> สรุปการนำเข้า</h5>
                            <div id="summary-content"></div>
                        </div>

                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-download"></i> ยืนยันการนำเข้า
                            </button>
                            <a href="{{ route('admin.packages.show', $package) }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times"></i> ยกเลิก
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Products Preview -->
        <div class="col-md-4">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i> สินค้าที่จะนำเข้า
                    </h3>
                </div>
                <div class="card-body">
                    @if($package->products->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>สินค้า</th>
                                        <th>จำนวน/แพ</th>
                                        <th>รวม</th>
                                    </tr>
                                </thead>
                                <tbody id="products-preview">
                                    @foreach($package->packageProducts->sortBy('sort_order') as $packageProduct)
                                        <tr>
                                            <td>
                                                <strong>{{ $packageProduct->product->name }}</strong>
                                                @if($packageProduct->grade || $packageProduct->size)
                                                    <br>
                                                    @if($packageProduct->grade)
                                                        <small class="badge badge-info">{{ $packageProduct->grade }}</small>
                                                    @endif
                                                    @if($packageProduct->size)
                                                        <small class="badge badge-secondary">{{ $packageProduct->size }}</small>
                                                    @endif
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                {{ number_format($packageProduct->quantity_per_package, 2) }}
                                                <br><small>{{ $packageProduct->unit }}</small>
                                            </td>
                                            <td class="text-center">
                                                <span class="total-quantity" data-per-package="{{ $packageProduct->quantity_per_package }}">
                                                    {{ number_format($packageProduct->quantity_per_package, 2) }}
                                                </span>
                                                <br><small>{{ $packageProduct->unit }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="text-center mt-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-boxes"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">สินค้าทั้งหมด</span>
                                    <span class="info-box-number">{{ $package->products->count() }} รายการ</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                            <p>แพนี้ยังไม่มีสินค้า</p>
                            <a href="{{ route('admin.packages.edit', $package) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> เพิ่มสินค้า
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Warning Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ยืนยันการนำเข้า</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>คำเตือน:</strong> การนำเข้าแพจะเพิ่มสินค้าเข้าสู่คลังที่เลือก
                    </div>
                    <div id="confirm-details"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-success" id="confirm-import">ยืนยัน</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .info-box-number {
            font-size: 20px;
            font-weight: bold;
        }
        .table-sm th, .table-sm td {
            padding: 0.5rem;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Update calculations when quantity changes
            $('#quantity, #warehouse_id').on('change', function() {
                updateCalculations();
            });

            // Initial calculation
            updateCalculations();

            // Form submission with confirmation
            $('#import-form').on('submit', function(e) {
                e.preventDefault();
                
                const warehouseId = $('#warehouse_id').val();
                const quantity = $('#quantity').val();
                
                if (!warehouseId) {
                    Swal.fire({
                        title: 'ข้อผิดพลาด',
                        text: 'กรุณาเลือกคลังปลายทาง',
                        icon: 'error'
                    });
                    return;
                }

                if (!quantity || quantity < 1) {
                    Swal.fire({
                        title: 'ข้อผิดพลาด',
                        text: 'กรุณาระบุจำนวนแพที่นำเข้า',
                        icon: 'error'
                    });
                    return;
                }

                const warehouseName = $('#warehouse_id option:selected').text();
                const packageName = '{{ $package->name }}';
                const totalProducts = {{ $package->products->count() }};

                Swal.fire({
                    title: 'ยืนยันการนำเข้า',
                    html: `
                        <div class="text-left">
                            <p><strong>แพ:</strong> ${packageName}</p>
                            <p><strong>คลังปลายทาง:</strong> ${warehouseName}</p>
                            <p><strong>จำนวนแพ:</strong> ${quantity} แพ</p>
                            <p><strong>สินค้าที่จะนำเข้า:</strong> ${totalProducts} รายการ</p>
                            <hr>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                ระบบจะสร้างรายการสินค้าและอัปเดตสต็อกในคลังอัตโนมัติ
                            </div>
                        </div>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'ยืนยันการนำเข้า',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading
                        Swal.fire({
                            title: 'กำลังดำเนินการ...',
                            text: 'กรุณารอสักครู่',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Submit form
                        document.getElementById('import-form').submit();
                    }
                });
            });
        });

        function updateCalculations() {
            const quantity = parseInt($('#quantity').val()) || 1;
            
            // Update product quantities
            $('.total-quantity').each(function() {
                const perPackage = parseFloat($(this).data('per-package'));
                const total = perPackage * quantity;
                $(this).text(total.toLocaleString('th-TH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            });

            // Update summary
            const packageQuantity = {{ $package->package_quantity }};
            const itemsPerPackage = {{ $package->items_per_package }};
            const totalItems = itemsPerPackage * quantity;
            const totalLength = {{ $package->length_per_package ?? 0 }} * quantity;
            const lengthUnit = '{{ $package->length_unit }}';
            const itemUnit = '{{ $package->item_unit }}';

            let summaryHtml = `
                <div class="row">
                    <div class="col-md-6">
                        <strong>จำนวนแพที่นำเข้า:</strong> ${quantity} แพ<br>
                        <strong>จำนวนรวมทั้งหมด:</strong> ${totalItems.toLocaleString()} ${itemUnit}
                    </div>
                    <div class="col-md-6">
                        <strong>สินค้าที่จะนำเข้า:</strong> {{ $package->products->count() }} รายการ
            `;

            if (totalLength > 0) {
                summaryHtml += `<br><strong>ความยาวรวม:</strong> ${totalLength.toLocaleString()} ${lengthUnit}`;
            }

            summaryHtml += `
                    </div>
                </div>
            `;

            $('#summary-content').html(summaryHtml);
            $('#import-summary').show();
        }
    </script>
@stop