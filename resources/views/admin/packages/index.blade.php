@extends('adminlte::page')

@section('title', 'จัดการแพสินค้า')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>จัดการแพสินค้า</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item active">แพสินค้า</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total_packages'] }}</h3>
                    <p>แพทั้งหมด</p>
                </div>
                <div class="icon">
                    <i class="fas fa-boxes"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['active_packages'] }}</h3>
                    <p>แพที่เปิดใช้งาน</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['inactive_packages'] }}</h3>
                    <p>แพที่ปิดใช้งาน</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $stats['total_products'] }}</h3>
                    <p>สินค้าในแพ</p>
                </div>
                <div class="icon">
                    <i class="fas fa-cubes"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">ค้นหาและกรองข้อมูล</h3>
            <div class="card-tools">
                @can('create-edit')
                <a href="{{ route('admin.packages.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> เพิ่มแพใหม่
                </a>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.packages.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">ค้นหา</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="search" 
                                   name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="ชื่อแพ, รหัส...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="supplier_id">ผู้จำหน่าย</label>
                            <select class="form-control" id="supplier_id" name="supplier_id">
                                <option value="">ทั้งหมด</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
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
                            <label for="status">สถานะ</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">ทั้งหมด</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>เปิดใช้งาน</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>ปิดใช้งาน</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> ค้นหา
                        </button>
                        <a href="{{ route('admin.packages.index') }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> รีเซ็ต
                        </a>
                        <a href="{{ route('admin.packages.report') }}" class="btn btn-info">
                            <i class="fas fa-chart-bar"></i> รายงาน
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Packages List -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">รายการแพสินค้า ({{ $packages->total() }} รายการ)</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>รหัสแพ</th>
                        <th>ชื่อแพ</th>
                        <th>จำนวนแพ</th>
                        <th>ความยาวรวม</th>
                        <th>จำนวนรวม</th>
                        <th>สินค้าในแพ</th>
                        <th>ผู้จำหน่าย</th>
                        <th>หมวดหมู่</th>
                        <th>สถานะ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($packages as $package)
                        <tr>
                            <td>
                                <span class="badge" style="background-color: {{ $package->color }}; color: {{ $package->getTextColor() }};">
                                    {{ $package->code }}
                                </span>
                            </td>
                            <td>
                                <strong>{{ $package->name }}</strong>
                                @if($package->description)
                                    <br><small class="text-muted">{{ Str::limit($package->description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $package->package_quantity }} {{ $package->item_unit }}</span>
                            </td>
                            <td>
                                @if($package->total_length)
                                    {{ number_format($package->total_length, 2) }} {{ $package->length_unit }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-primary">{{ $package->total_items }} {{ $package->item_unit }}</span>
                            </td>
                            <td>
                                <span class="badge badge-secondary">{{ $package->products_count }} รายการ</span>
                            </td>
                            <td>
                                @if($package->supplier)
                                    <span class="badge badge-outline-primary">{{ $package->supplier->name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($package->category)
                                    <span class="badge" style="background-color: {{ $package->category->color }}; color: {{ $package->category->getTextColor() }};">
                                        {{ $package->category->name }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $package->is_active ? 'badge-success' : 'badge-secondary' }}">
                                    {{ $package->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.packages.show', $package) }}" 
                                       class="btn btn-sm btn-info" 
                                       title="ดูรายละเอียด">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <a href="{{ route('admin.packages.import', $package) }}" 
                                       class="btn btn-sm btn-success" 
                                       title="นำเข้าแพ">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    
                                    @can('create-edit')
                                    <a href="{{ route('admin.packages.edit', $package) }}" 
                                       class="btn btn-sm btn-warning" 
                                       title="แก้ไข">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('delete')
                                    <button type="button" 
                                            class="btn btn-sm btn-danger" 
                                            onclick="confirmDelete('{{ $package->id }}', '{{ $package->name }}')"
                                            title="ลบ">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                    
                                    <button type="button" 
                                            class="btn btn-sm btn-secondary" 
                                            onclick="confirmDuplicate('{{ $package->id }}', '{{ $package->name }}')"
                                            title="คัดลอก">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted">ไม่พบข้อมูลแพสินค้า</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($packages->hasPages())
            <div class="card-footer">
                {{ $packages->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    <!-- Delete Forms -->
    <form id="delete-form" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <form id="duplicate-form" method="POST" style="display: none;">
        @csrf
    </form>
@stop

@section('css')
    <style>
        .badge-outline-primary {
            color: #007bff;
            border: 1px solid #007bff;
            background-color: transparent;
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
        function confirmDelete(packageId, packageName) {
            Swal.fire({
                title: 'ยืนยันการลบ',
                html: `คุณต้องการลบแพ <strong>"${packageName}"</strong> หรือไม่?<br><br>
                       <div class="text-warning">
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
                    const form = document.getElementById('delete-form');
                    form.action = `/admin/packages/${packageId}`;
                    form.submit();
                }
            });
        }

        function confirmDuplicate(packageId, packageName) {
            Swal.fire({
                title: 'ยืนยันการคัดลอก',
                html: `คุณต้องการคัดลอกแพ <strong>"${packageName}"</strong> หรือไม่?<br><br>
                       <div class="text-info">
                           <i class="fas fa-info-circle"></i>
                           แพใหม่จะถูกสร้างและปิดใช้งานไว้ก่อน
                       </div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#17a2b8',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'คัดลอก',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('duplicate-form');
                    form.action = `/admin/packages/${packageId}/duplicate`;
                    form.submit();
                }
            });
        }
    </script>
@stop