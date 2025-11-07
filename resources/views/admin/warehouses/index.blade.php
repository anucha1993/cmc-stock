@extends('adminlte::page')

@section('title', 'จัดการคลังสินค้า')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>จัดการคลังสินค้า</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item active">คลังสินค้า</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">รายการคลังสินค้า</h3>
            <div class="card-tools">
                <a href="{{ route('admin.warehouses.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> เพิ่มคลังสินค้า
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th width="10%">รหัส</th>
                            <th width="20%">ชื่อคลัง</th>
                            <th width="25%">ที่อยู่</th>
                            <th width="15%">ผู้ติดต่อ</th>
                            <th width="10%">จำนวนสินค้า</th>
                            <th width="10%">สต็อกรวม</th>
                            <th width="10%">สถานะ</th>
                            <th width="15%">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($warehouses as $warehouse)
                            <tr>
                                <td>
                                    <strong>{{ $warehouse->code }}</strong>
                                    @if($warehouse->is_main)
                                        <span class="badge badge-primary ml-1">หลัก</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $warehouse->name }}</strong>
                                    @if($warehouse->description)
                                        <br><small class="text-muted">{{ $warehouse->description }}</small>
                                    @endif
                                </td>
                                <td>
                                    {{ $warehouse->address ?: '-' }}
                                </td>
                                <td>
                                    @if($warehouse->contact_person)
                                        <strong>{{ $warehouse->contact_person }}</strong><br>
                                        <small class="text-muted">{{ $warehouse->phone }}</small>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info">{{ $warehouse->warehouse_products_count }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-success">{{ number_format($warehouse->warehouse_products_sum_quantity ?: 0) }}</span>
                                </td>
                                <td class="text-center">
                                    @if($warehouse->is_active)
                                        <span class="badge badge-success">เปิดใช้งาน</span>
                                    @else
                                        <span class="badge badge-secondary">ปิดใช้งาน</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.warehouses.show', $warehouse) }}" 
                                           class="btn btn-info" title="ดูรายละเอียด">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.warehouses.stock', $warehouse) }}" 
                                           class="btn btn-success" title="จัดการสต็อก">
                                            <i class="fas fa-boxes"></i>
                                        </a>
                                        <a href="{{ route('admin.warehouses.edit', $warehouse) }}" 
                                           class="btn btn-warning" title="แก้ไข">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if(!$warehouse->is_main)
                                            <button type="button" class="btn btn-danger" 
                                                    onclick="confirmDelete({{ $warehouse->id }})" title="ลบ">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-warehouse fa-3x mb-3"></i><br>
                                    ยังไม่มีคลังสินค้า
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($warehouses->hasPages())
            <div class="card-footer">
                {{ $warehouses->links() }}
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
                    <p>คุณแน่ใจหรือไม่ที่จะลบคลังสินค้านี้?</p>
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
        function confirmDelete(warehouseId) {
            $('#deleteForm').attr('action', '/admin/warehouses/' + warehouseId);
            $('#deleteModal').modal('show');
        }

        // Alert auto hide
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
@stop