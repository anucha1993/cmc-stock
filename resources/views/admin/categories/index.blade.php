@extends('adminlte::page')

@section('title', 'จัดการหมวดหมู่สินค้า')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>จัดการหมวดหมู่สินค้า</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item active">หมวดหมู่สินค้า</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Filter Card -->
    <div class="card card-outline card-info collapsed-card">

        <!-- Statistics Card -->
    <div class="row">
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-tags"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">หมวดหมู่ทั้งหมด</span>
                    <span class="info-box-number">{{ $categories->total() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">เปิดใช้งาน</span>
                    <span class="info-box-number">{{ $categories->where('is_active', true)->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-times-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">ปิดใช้งาน</span>
                    <span class="info-box-number">{{ $categories->where('is_active', false)->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-primary"><i class="fas fa-boxes"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">สินค้ารวม</span>
                    <span class="info-box-number">{{ $categories->sum('products_count') }}</span>
                </div>
            </div>
        </div>
    </div>
    
        <div class="card-header">
            <h3 class="card-title">ค้นหาและกรองข้อมูล</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.categories.index') }}">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>ค้นหา</label>
                            <input type="text" name="search" class="form-control" 
                                   value="{{ request('search') }}" 
                                   placeholder="ชื่อหมวดหมู่, คำอธิบาย">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>สถานะ</label>
                            <select name="status" class="form-control">
                                <option value="">ทั้งหมด</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>เปิดใช้งาน</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>ปิดใช้งาน</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-info btn-block">
                                <i class="fas fa-search"></i> ค้นหา
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">รายการหมวดหมู่สินค้า</h3>
            <div class="card-tools">
                @can('manage-master-data')
                <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> เพิ่มหมวดหมู่
                </a>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="25%">ชื่อหมวดหมู่</th>
                            <th width="35%">คำอธิบาย</th>
                            <th width="10%">จำนวนสินค้า</th>
                            <th width="10%">สถานะ</th>
                            <th width="15%">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>
                                    <strong>{{ $category->name }}</strong>
                                    @if($category->code)
                                        <br><small class="text-muted">รหัส: {{ $category->code }}</small>
                                    @endif
                                </td>
                                <td>
                                    {{ $category->description ?: '-' }}
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info">{{ $category->products_count ?? 0 }}</span>
                                </td>
                                <td class="text-center">
                                    @if($category->is_active)
                                        <span class="badge badge-success">เปิดใช้งาน</span>
                                    @else
                                        <span class="badge badge-secondary">ปิดใช้งาน</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.categories.show', $category) }}" 
                                           class="btn btn-info" title="ดูรายละเอียด">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('manage-master-data')
                                        <a href="{{ route('admin.categories.edit', $category) }}" 
                                           class="btn btn-warning" title="แก้ไข">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        @can('delete')
                                        <button type="button" class="btn btn-danger" 
                                                onclick="confirmDelete({{ $category->id }})" title="ลบ">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-tags fa-3x mb-3"></i><br>
                                    ยังไม่มีหมวดหมู่สินค้า
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($categories->hasPages())
            <div class="card-footer">
                {{ $categories->withQueryString()->links() }}
            </div>
        @endif
    </div>

    

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ยืนยันการลบ</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>คุณแน่ใจหรือไม่ที่จะลบหมวดหมู่นี้?</p>
                    <p class="text-warning"><i class="fas fa-exclamation-triangle"></i> การดำเนินการนี้ไม่สามารถยกเลิกได้</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">ลบ</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .btn-group-sm > .btn {
            margin-right: 2px;
        }
        .info-box {
            margin-bottom: 1rem;
        }
    </style>
@stop

@section('js')
    <script>
        function confirmDelete(categoryId) {
            $('#deleteForm').attr('action', '/admin/categories/' + categoryId);
            $('#deleteModal').modal('show');
        }

        // Alert auto hide
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
@stop