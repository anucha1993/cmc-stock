@extends('adminlte::page')

@section('title', 'จัดการผู้จำหน่าย')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>จัดการผู้จำหน่าย</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item active">ผู้จำหน่าย</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Filter Card -->
    <div class="card card-outline card-info collapsed-card">
        <div class="card-header">
            <h3 class="card-title">ค้นหาและกรองข้อมูล</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.suppliers.index') }}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>ค้นหา</label>
                            <input type="text" name="search" class="form-control" 
                                   value="{{ request('search') }}" 
                                   placeholder="ชื่อ, อีเมล, เบอร์โทร">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>สถานะ</label>
                            <select name="status" class="form-control">
                                <option value="">ทั้งหมด</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>เปิดใช้งาน</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>ปิดใช้งาน</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>จังหวัด</label>
                            <input type="text" name="province" class="form-control" 
                                   value="{{ request('province') }}" 
                                   placeholder="จังหวัด">
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
            <h3 class="card-title">รายการผู้จำหน่าย</h3>
            <div class="card-tools">
                @can('manage-master-data')
                <a href="{{ route('admin.suppliers.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> เพิ่มผู้จำหน่าย
                </a>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th width="20%">ชื่อผู้จำหน่าย</th>
                            <th width="15%">ติดต่อ</th>
                            <th width="20%">ที่อยู่</th>
                            <th width="10%">จำนวนสินค้า</th>
                            <th width="10%">มูลค่ารวม</th>
                            <th width="10%">สถานะ</th>
                            <th width="15%">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $supplier)
                            <tr>
                                <td>
                                    <strong>{{ $supplier->name }}</strong>
                                    @if($supplier->company)
                                        <br><small class="text-muted">{{ $supplier->company }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($supplier->contact_person)
                                        <strong>{{ $supplier->contact_person }}</strong><br>
                                    @endif
                                    @if($supplier->phone)
                                        <i class="fas fa-phone"></i> {{ $supplier->phone }}<br>
                                    @endif
                                    @if($supplier->email)
                                        <i class="fas fa-envelope"></i> {{ $supplier->email }}
                                    @endif
                                </td>
                                <td>
                                    @if($supplier->address)
                                        {{ $supplier->address }}
                                        @if($supplier->province)
                                            <br><small class="text-muted">{{ $supplier->province }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info">{{ $supplier->products_count ?? 0 }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="text-success font-weight-bold">
                                        ฿{{ number_format($supplier->total_value ?? 0, 2) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($supplier->is_active)
                                        <span class="badge badge-success">เปิดใช้งาน</span>
                                    @else
                                        <span class="badge badge-secondary">ปิดใช้งาน</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.suppliers.show', $supplier) }}" 
                                           class="btn btn-info" title="ดูรายละเอียด">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('manage-master-data')
                                        <a href="{{ route('admin.suppliers.edit', $supplier) }}" 
                                           class="btn btn-warning" title="แก้ไข">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        @can('delete')
                                        <button type="button" class="btn btn-danger" 
                                                onclick="confirmDelete({{ $supplier->id }})" title="ลบ">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-truck fa-3x mb-3"></i><br>
                                    ยังไม่มีผู้จำหน่าย
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($suppliers->hasPages())
            <div class="card-footer">
                {{ $suppliers->withQueryString()->links() }}
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
                    <p>คุณแน่ใจหรือไม่ที่จะลบผู้จำหน่ายนี้?</p>
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
    </style>
@stop

@section('js')
    <script>
        function confirmDelete(supplierId) {
            $('#deleteForm').attr('action', '/admin/suppliers/' + supplierId);
            $('#deleteModal').modal('show');
        }

        // Alert auto hide
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
@stop