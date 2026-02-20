@extends('adminlte::page')

@section('title', 'จัดการสินค้า - CMC-STOCK')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>จัดการสินค้า</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">แดชบอร์ด</a></li>
                <li class="breadcrumb-item active">สินค้า</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> {!! session('error') !!}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('warning') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Filter Card -->
    <div class="card card-outline card-primary collapsed-card">
        <div class="card-header">
            <h3 class="card-title">กรองข้อมูล</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.products.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>ค้นหา</label>
                            <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="ชื่อ, SKU, Barcode">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>หมวดหมู่</label>
                            <select class="form-control" name="category_id">
                                <option value="">ทั้งหมด</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>ผู้จำหน่าย</label>
                            <select class="form-control" name="supplier_id">
                                <option value="">ทั้งหมด</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>ประเภทไซส์</label>
                            <select class="form-control" name="size_type">
                                <option value="">ทั้งหมด</option>
                                <option value="standard" {{ request('size_type') == 'standard' ? 'selected' : '' }}>ไซส์มาตรฐาน</option>
                                <option value="custom" {{ request('size_type') == 'custom' ? 'selected' : '' }}>ไซส์กำหนดเอง</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>สถานะสต็อก</label>
                            <select class="form-control" name="stock_status">
                                <option value="">ทั้งหมด</option>
                                <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>สต็อกต่ำ</option>
                                <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>หมดสต็อก</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> กรอง
                                </button>
                                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> ล้าง
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">รายการสินค้า</h3>
            <div class="card-tools">
                @can('create-edit')
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> เพิ่มสินค้าใหม่
                </a>
                @endcan
            </div>
        </div>
        
        <div class="card-body">
            @if($products->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="100">รูปภาพ</th>
                                <th>ข้อมูลสินค้า</th>
                                <th width="150">รายละเอียดสินค้า</th>
                                <th width="120">Barcode</th>
                                <th width="100">หมวดหมู่</th>
                                <th width="120">ประเภทไซส์</th>
                                <th width="100">สต็อก</th>
                                <th width="80">สถานะ</th>
                                <th width="120">การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                            <tr>
                                <td class="text-center">
                                    @if($product->main_image)
                                        <img src="{{ $product->main_image }}" class="img-thumbnail" width="60" height="60">
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 4px;">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $product->full_name }}</strong>
                                        <br><small class="text-muted">SKU: {{ $product->sku }}</small>
                                        @if($product->description)
                                            <br><small class="text-muted">{{ Str::limit($product->description, 50) }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="text-sm">
                                        @if($product->length || $product->thickness)
                                            <div>
                                                @if($product->length)
                                                    <small class="text-muted">ยาว: {{ number_format($product->length, 2) }} {{ $product->measurement_unit_short }}</small>
                                                @endif
                                                @if($product->thickness)
                                                    <br><small class="text-muted">หนา: {{ number_format($product->thickness, 2) }} {{ $product->measurement_unit_short }}</small>
                                                @endif
                                            </div>
                                        @endif
                                        @if($product->steel_type !== 'not_specified')
                                            <div>
                                                <small class="badge badge-secondary">{{ $product->steel_type_text }}</small>
                                            </div>
                                        @endif
                                        @if($product->side_steel_type !== 'not_specified')
                                            <div>
                                                <small class="text-info">{{ $product->side_steel_type_text }}</small>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $product->barcode }}</span>
                                </td>
                                <td>
                                    @if($product->category)
                                        <span class="badge" style="background-color: {{ $product->category->color }}; color: {{ $product->category->getTextColor() }};">
                                            {{ $product->category->name }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <span class="badge badge-{{ $product->size_type_color }}">
                                            {{ $product->size_type_text }}
                                        </span>
                                        @if($product->allow_custom_order)
                                            <br><small class="text-success"><i class="fas fa-tools"></i> รับผลิตตามสั่ง</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $product->stock_status_color }}">
                                        {{ $product->total_stock }} {{ $product->unit }}
                                    </span>
                                    <br><small class="text-muted">{{ $product->stock_status_text }}</small>
                                </td>
                               
                                <td>
                                    @if($product->is_active)
                                        <span class="badge badge-success">ใช้งาน</span>
                                    @else
                                        <span class="badge badge-secondary">ไม่ใช้งาน</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.products.show', $product) }}" class="btn btn-info btn-sm" title="ดูรายละเอียด">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('create-edit')
                                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-warning btn-sm" title="แก้ไข">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        @can('delete')
                                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteProduct({{ $product->id }}, '{{ $product->full_name }}')" title="ลบ">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    {{ $products->appends(request()->all())->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">ไม่มีข้อมูลสินค้า</h5>
                    <p class="text-muted">เริ่มต้นด้วยการเพิ่มสินค้าใหม่</p>
                    @can('create-edit')
                    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> เพิ่มสินค้าใหม่
                    </a>
                    @endcan
                </div>
            @endif
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">ยืนยันการลบ</h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>คุณต้องการลบสินค้า <strong id="productName"></strong> หรือไม่?</p>
                    <p class="text-warning"><i class="fas fa-exclamation-triangle"></i> การดำเนินการนี้ไม่สามารถยกเลิกได้</p>
                </div>
                <div class="modal-footer">
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-danger">ลบสินค้า</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        // ข้อมูลคลังและสต็อก
        const warehouseStocks = {!! json_encode(\App\Models\Warehouse::where('is_active', true)->with(['warehouseProducts' => function($query) {
            $query->select('warehouse_id', 'product_id', 'quantity');
        }])->get()->mapWithKeys(function($warehouse) {
            return [$warehouse->id => $warehouse->warehouseProducts->mapWithKeys(function($wp) {
                return [$wp->product_id => $wp->quantity];
            })];
        })) !!};

        function deleteProduct(id, name) {
            $('#productName').text(name);
            $('#deleteForm').attr('action', '{{ route("admin.products.index") }}/' + id);
            $('#deleteModal').modal('show');
        }

        function quickStockManagement(productId, productName, currentStock, unit) {
            $('#stockProductName').text(productName);
            $('#currentStock').text(new Intl.NumberFormat().format(currentStock));
            $('#productUnit').text(unit);
            $('#quantityUnit').text(unit);
            $('#stockForm').attr('action', '{{ route("admin.products.index") }}/' + productId + '/update-stock');
            
            // Reset form
            $('#stockForm')[0].reset();
            $('#warehouseStock').text('-');
            
            // Show modal
            $('#stockModal').modal('show');
        }

        // อัปเดตสต็อกในคลังเมื่อเลือกคลัง
        $('#warehouseSelect').on('change', function() {
            const warehouseId = $(this).val();
            const productId = $('#stockForm').attr('action').split('/').slice(-2, -1)[0];
            
            if (warehouseId && warehouseStocks[warehouseId] && warehouseStocks[warehouseId][productId]) {
                const stock = warehouseStocks[warehouseId][productId];
                $('#warehouseStock').text(new Intl.NumberFormat().format(stock) + ' ' + $('#productUnit').text());
            } else {
                $('#warehouseStock').text('0 ' + $('#productUnit').text());
            }
        });

        // ตรวจสอบการส่งฟอร์ม
        $('#stockForm').on('submit', function(e) {
            const type = $('select[name="type"]').val();
            const quantity = parseInt($('input[name="quantity"]').val());
            const warehouseId = $('#warehouseSelect').val();
            
            if (!warehouseId) {
                e.preventDefault();
                alert('กรุณาเลือกคลัง');
                return;
            }

            if (type === 'out') {
                const productId = $(this).attr('action').split('/').slice(-2, -1)[0];
                const availableStock = warehouseStocks[warehouseId] && warehouseStocks[warehouseId][productId] 
                    ? warehouseStocks[warehouseId][productId] : 0;
                
                if (quantity > availableStock) {
                    e.preventDefault();
                    alert(`ไม่สามารถลดสต็อกได้ สต็อกในคลังมีเพียง ${new Intl.NumberFormat().format(availableStock)} ${$('#productUnit').text()}`);
                    return;
                }
            }
        });
    </script>
@stop