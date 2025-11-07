@extends('adminlte::page')

@section('title', 'รายละเอียดแพ: ' . $package->name)

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>รายละเอียดแพสินค้า</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.packages.index') }}">แพสินค้า</a></li>
                <li class="breadcrumb-item active">{{ $package->name }}</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Package Information -->
    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <span class="badge" style="background-color: {{ $package->color }}; color: {{ $package->getTextColor() }}; font-size: 16px; padding: 8px 15px;">
                            {{ $package->code }} - {{ $package->name }}
                        </span>
                    </h3>
                    <div class="card-tools">
                        <span class="badge {{ $package->is_active ? 'badge-success' : 'badge-secondary' }}">
                            {{ $package->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    @if($package->description)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            {{ $package->description }}
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-5">รหัสแพ:</dt>
                                <dd class="col-sm-7">
                                    <code>{{ $package->code }}</code>
                                </dd>
                                
                                <dt class="col-sm-5">จำนวนแพ:</dt>
                                <dd class="col-sm-7">
                                    <span class="badge badge-info">{{ $package->package_quantity }} แพ</span>
                                </dd>
                                
                                <dt class="col-sm-5">ความยาวต่อแพ:</dt>
                                <dd class="col-sm-7">
                                    @if($package->length_per_package)
                                        {{ number_format($package->length_per_package, 2) }} {{ $package->length_unit }}
                                    @else
                                        <span class="text-muted">ไม่ระบุ</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-5">จำนวนต่อแพ:</dt>
                                <dd class="col-sm-7">
                                    {{ $package->items_per_package }} {{ $package->item_unit }}
                                </dd>
                                
                                <dt class="col-sm-5">น้ำหนักต่อแพ:</dt>
                                <dd class="col-sm-7">
                                    @if($package->weight_per_package)
                                        {{ number_format($package->weight_per_package, 2) }} {{ $package->weight_unit }}
                                    @else
                                        <span class="text-muted">ไม่ระบุ</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-5">ความยาวรวม:</dt>
                                <dd class="col-sm-7">
                                    @if($package->total_length)
                                        <span class="badge badge-primary">{{ number_format($package->total_length, 2) }} {{ $package->length_unit }}</span>
                                    @else
                                        <span class="text-muted">ไม่ระบุ</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-5">จำนวนรวม:</dt>
                                <dd class="col-sm-7">
                                    <span class="badge badge-success">{{ $package->total_items }} {{ $package->item_unit }}</span>
                                </dd>
                                
                                <dt class="col-sm-5">ผู้จำหน่าย:</dt>
                                <dd class="col-sm-7">
                                    @if($package->supplier)
                                        <span class="badge badge-outline-primary">{{ $package->supplier->name }}</span>
                                    @else
                                        <span class="text-muted">ไม่ระบุ</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-5">หมวดหมู่:</dt>
                                <dd class="col-sm-7">
                                    @if($package->category)
                                        <span class="badge" style="background-color: {{ $package->category->color }}; color: {{ $package->category->getTextColor() }};">
                                            {{ $package->category->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">ไม่ระบุ</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-5">สร้างเมื่อ:</dt>
                                <dd class="col-sm-7">
                                    {{ $package->created_at->format('d/m/Y H:i') }}
                                    <small class="text-muted">({{ $package->created_at->diffForHumans() }})</small>
                                </dd>
                            </dl>
                        </div>
                    </div>

                    <!-- Cost Information -->
                    @if($package->cost_per_package || $package->selling_price_per_package)
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h6>ข้อมูลราคา</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-warning">
                                                <i class="fas fa-dollar-sign"></i>
                                            </span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">ต้นทุนต่อแพ</span>
                                                <span class="info-box-number">{{ number_format($package->cost_per_package ?? 0, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-success">
                                                <i class="fas fa-dollar-sign"></i>
                                            </span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">ราคาขายต่อแพ</span>
                                                <span class="info-box-number">{{ number_format($package->selling_price_per_package ?? 0, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-info">
                                                <i class="fas fa-calculator"></i>
                                            </span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">ต้นทุนรวม</span>
                                                <span class="info-box-number">{{ number_format($stats['total_cost'], 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-primary">
                                                <i class="fas fa-chart-line"></i>
                                            </span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">ราคาขายรวม</span>
                                                <span class="info-box-number">{{ number_format($stats['total_selling_price'], 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="{{ route('admin.packages.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> ย้อนกลับ
                            </a>
                        </div>
                        <div class="col-md-6 text-right">
                            @can('packages.import')
                                <a href="{{ route('admin.packages.import', $package) }}" class="btn btn-success">
                                    <i class="fas fa-download"></i> นำเข้าแพ
                                </a>
                            @endcan
                            @can('packages.edit')
                                <a href="{{ route('admin.packages.edit', $package) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> แก้ไข
                                </a>
                            @endcan
                            @can('packages.delete')
                                <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                    <i class="fas fa-trash"></i> ลบ
                                </button>
                            @endcan
                            @can('packages.duplicate')
                                <button type="button" class="btn btn-info" onclick="confirmDuplicate()">
                                    <i class="fas fa-copy"></i> คัดลอก
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="col-md-4">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i> สถิติแพ
                    </h3>
                </div>
                <div class="card-body">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-boxes"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">สินค้าในแพ</span>
                            <span class="info-box-number">{{ $stats['total_products'] }} รายการ</span>
                        </div>
                    </div>

                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-primary">
                            <i class="fas fa-weight"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">น้ำหนักรวม</span>
                            <span class="info-box-number">{{ number_format($stats['total_weight'], 2) }} กก.</span>
                        </div>
                    </div>

                    @if($stats['profit_margin'] > 0)
                        <div class="info-box">
                            <span class="info-box-icon bg-warning">
                                <i class="fas fa-percent"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">อัตรากำไร</span>
                                <span class="info-box-number">{{ number_format($stats['profit_margin'], 1) }}%</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Products List -->
    @if($package->products->count() > 0)
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list"></i> รายการสินค้าในแพ
                        </h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>ลำดับ</th>
                                    <th>สินค้า</th>
                                    <th>จำนวนต่อแพ</th>
                                    <th>จำนวนรวม</th>
                                    <th>เกรด/ขนาด</th>
                                    <th>ความยาวรวม</th>
                                    <th>น้ำหนักรวม</th>
                                    <th>ต้นทุนรวม</th>
                                    <th>ราคาขายรวม</th>
                                    <th>สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($package->packageProducts->sortBy('sort_order') as $index => $packageProduct)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $packageProduct->product->name }}</strong>
                                            @if($packageProduct->product->category)
                                                <br><small class="text-muted">{{ $packageProduct->product->category->name }}</small>
                                            @endif
                                            @if($packageProduct->specifications)
                                                <br><small class="text-info">{{ $packageProduct->specifications }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            {{ number_format($packageProduct->quantity_per_package, 2) }} {{ $packageProduct->unit }}
                                        </td>
                                        <td>
                                            <span class="badge badge-primary">
                                                {{ number_format($packageProduct->total_quantity, 2) }} {{ $packageProduct->unit }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($packageProduct->grade || $packageProduct->size)
                                                @if($packageProduct->grade)
                                                    <span class="badge badge-info">{{ $packageProduct->grade }}</span>
                                                @endif
                                                @if($packageProduct->size)
                                                    <span class="badge badge-secondary">{{ $packageProduct->size }}</span>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($packageProduct->total_length)
                                                {{ number_format($packageProduct->total_length, 2) }} {{ $package->length_unit }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($packageProduct->total_weight)
                                                {{ number_format($packageProduct->total_weight, 2) }} {{ $package->weight_unit }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($packageProduct->total_cost)
                                                {{ number_format($packageProduct->total_cost, 2) }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($packageProduct->total_selling_price)
                                                {{ number_format($packageProduct->total_selling_price, 2) }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($packageProduct->is_main_product)
                                                <span class="badge badge-warning">สินค้าหลัก</span>
                                            @else
                                                <span class="badge badge-light">สินค้าประกอบ</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Forms -->
    @can('packages.delete')
        <form id="delete-form" action="{{ route('admin.packages.destroy', $package) }}" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    @endcan

    @can('packages.duplicate')
        <form id="duplicate-form" action="{{ route('admin.packages.duplicate', $package) }}" method="POST" style="display: none;">
            @csrf
        </form>
    @endcan
@stop

@section('css')
    <style>
        .badge-outline-primary {
            color: #007bff;
            border: 1px solid #007bff;
            background-color: transparent;
        }
        .info-box-number {
            font-size: 18px;
            font-weight: bold;
        }
        .info-box-text {
            font-size: 12px;
        }
        dl.row dt {
            font-weight: 600;
        }
        .table th {
            border-top: none;
        }
    </style>
@stop

@section('js')
    <script>
        function confirmDelete() {
            Swal.fire({
                title: 'ยืนยันการลบ',
                html: `คุณต้องการลบแพ <strong>"{{ $package->name }}"</strong> หรือไม่?<br><br>
                       @if($package->products->count() > 0)
                           <div class="alert alert-warning">
                               <i class="fas fa-exclamation-triangle"></i>
                               <strong>คำเตือน:</strong> แพนี้มีสินค้า {{ $package->products->count() }} รายการ<br>
                               การลบแพจะลบข้อมูลสินค้าในแพด้วย
                           </div>
                       @endif
                       <div class="text-danger">
                           <i class="fas fa-exclamation-triangle"></i>
                           ข้อมูลจะถูกลบถาวรและไม่สามารถกู้คืนได้
                       </div>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ลบ',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form').submit();
                }
            });
        }

        function confirmDuplicate() {
            Swal.fire({
                title: 'ยืนยันการคัดลอก',
                html: `คุณต้องการคัดลอกแพ <strong>"{{ $package->name }}"</strong> หรือไม่?<br><br>
                       <div class="text-info">
                           <i class="fas fa-info-circle"></i>
                           แพใหม่จะถูกสร้างพร้อมสินค้าทั้งหมด และปิดใช้งานไว้ก่อน<br>
                           คุณสามารถแก้ไขข้อมูลได้ก่อนเปิดใช้งาน
                       </div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#17a2b8',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'คัดลอก',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('duplicate-form').submit();
                }
            });
        }
    </script>
@stop