@extends('adminlte::page')

@section('title', 'จัดการการโอนสินค้า')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>จัดการการโอนสินค้า</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item active">การโอนสินค้า</li>
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
            <form method="GET" action="{{ route('admin.transfers.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>ค้นหา</label>
                            <input type="text" name="search" class="form-control" 
                                   value="{{ request('search') }}" 
                                   placeholder="รหัสโอน, สินค้า, SKU">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>สถานะ</label>
                            <select name="status" class="form-control">
                                <option value="">ทั้งหมด</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>รอดำเนินการ</option>
                                <option value="in_transit" {{ request('status') == 'in_transit' ? 'selected' : '' }}>กำลังขนส่ง</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>เสร็จสิ้น</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ยกเลิก</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>คลัง</label>
                            <select name="warehouse" class="form-control">
                                <option value="">ทั้งหมด</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" 
                                            {{ request('warehouse') == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>วันที่เริ่ม</label>
                            <input type="date" name="date_from" class="form-control" 
                                   value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>วันที่สิ้นสุด</label>
                            <input type="date" name="date_to" class="form-control" 
                                   value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-info btn-block">
                                <i class="fas fa-search"></i>
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
            <h3 class="card-title">รายการการโอนสินค้า</h3>
            <div class="card-tools">
                @can('create-edit')
                <a href="{{ route('admin.transfers.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> สร้างใบโอน
                </a>
                @endcan
                <a href="{{ route('admin.transfers.report') }}" class="btn btn-info btn-sm">
                    <i class="fas fa-chart-bar"></i> รายงาน
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th width="12%">รหัสโอน</th>
                            <th width="15%">สินค้า</th>
                            <th width="12%">จาก</th>
                            <th width="12%">ไปยัง</th>
                            <th width="8%">จำนวน</th>
                            <th width="10%">วันที่โอน</th>
                            <th width="8%">ความสำคัญ</th>
                            <th width="10%">สถานะ</th>
                            <th width="13%">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transfers as $transfer)
                            <tr>
                                <td>
                                    <strong>{{ $transfer->transfer_code }}</strong>
                                </td>
                                <td>
                                    <strong>{{ $transfer->product->full_name }}</strong><br>
                                    <small class="text-muted">SKU: {{ $transfer->product->sku }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-secondary">{{ $transfer->fromWarehouse->code }}</span><br>
                                    <small>{{ $transfer->fromWarehouse->name }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $transfer->toWarehouse->code }}</span><br>
                                    <small>{{ $transfer->toWarehouse->name }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-primary">{{ number_format($transfer->quantity) }}</span>
                                </td>
                                <td>
                                    {{ $transfer->transfer_date->format('d/m/Y') }}<br>
                                    <small class="text-muted">{{ $transfer->transfer_date->format('H:i') }}</small>
                                </td>
                                <td class="text-center">
                                    @php
                                        $priorityColors = [
                                            'low' => 'secondary',
                                            'normal' => 'info', 
                                            'high' => 'warning',
                                            'urgent' => 'danger'
                                        ];
                                        $priorityTexts = [
                                            'low' => 'ต่ำ',
                                            'normal' => 'ปกติ',
                                            'high' => 'สูง', 
                                            'urgent' => 'เร่งด่วน'
                                        ];
                                    @endphp
                                    <span class="badge badge-{{ $priorityColors[$transfer->priority] }}">
                                        {{ $priorityTexts[$transfer->priority] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'in_transit' => 'info',
                                            'completed' => 'success',
                                            'cancelled' => 'danger'
                                        ];
                                        $statusTexts = [
                                            'pending' => 'รอดำเนินการ',
                                            'in_transit' => 'กำลังขนส่ง',
                                            'completed' => 'เสร็จสิ้น',
                                            'cancelled' => 'ยกเลิก'
                                        ];
                                    @endphp
                                    <span class="badge badge-{{ $statusColors[$transfer->status] }}">
                                        {{ $statusTexts[$transfer->status] }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.transfers.show', $transfer) }}" 
                                           class="btn btn-info" title="ดูรายละเอียด">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($transfer->status == 'pending')
                                            @can('create-edit')
                                            <a href="{{ route('admin.transfers.edit', $transfer) }}" 
                                               class="btn btn-warning" title="แก้ไข">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcan
                                            @can('approve')
                                            <button type="button" class="btn btn-success" 
                                                    onclick="confirmApprove({{ $transfer->id }})" title="อนุมัติ">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            @endcan
                                        @endif
                                        
                                        @if($transfer->status == 'in_transit')
                                            <button type="button" class="btn btn-primary" 
                                                    onclick="confirmComplete({{ $transfer->id }})" title="รับสินค้า">
                                                <i class="fas fa-flag-checkered"></i>
                                            </button>
                                        @endif
                                        
                                        @if(in_array($transfer->status, ['pending', 'in_transit']))
                                            <button type="button" class="btn btn-danger" 
                                                    onclick="confirmCancel({{ $transfer->id }})" title="ยกเลิก">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-exchange-alt fa-3x mb-3"></i><br>
                                    ยังไม่มีใบโอนสินค้า
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($transfers->hasPages())
            <div class="card-footer">
                {{ $transfers->withQueryString()->links() }}
            </div>
        @endif
    </div>

    <!-- Action Modals -->
    <div class="modal fade" id="approveModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ยืนยันการอนุมัติ</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>คุณแน่ใจหรือไม่ที่จะอนุมัติการโอนนี้?</p>
                    <p class="text-info"><i class="fas fa-info-circle"></i> การอนุมัติจะเริ่มการขนส่งสินค้า</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    <form id="approveForm" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-success">อนุมัติ</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="completeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ยืนยันการรับสินค้า</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>คุณแน่ใจหรือไม่ที่จะรับสินค้าและเสร็จสิ้นการโอน?</p>
                    <p class="text-success"><i class="fas fa-check-circle"></i> สินค้าจะถูกเพิ่มเข้าคลังปลายทาง</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    <form id="completeForm" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-primary">รับสินค้า</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cancelModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ยืนยันการยกเลิก</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>คุณแน่ใจหรือไม่ที่จะยกเลิกการโอนนี้?</p>
                    <p class="text-warning"><i class="fas fa-exclamation-triangle"></i> การดำเนินการนี้ไม่สามารถยกเลิกได้</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    <form id="cancelForm" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-danger">ยกเลิก</button>
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
        function confirmApprove(transferId) {
            $('#approveForm').attr('action', '/admin/transfers/' + transferId + '/approve');
            $('#approveModal').modal('show');
        }

        function confirmComplete(transferId) {
            $('#completeForm').attr('action', '/admin/transfers/' + transferId + '/complete');
            $('#completeModal').modal('show');
        }

        function confirmCancel(transferId) {
            $('#cancelForm').attr('action', '/admin/transfers/' + transferId + '/cancel');
            $('#cancelModal').modal('show');
        }

        // Alert auto hide
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
@stop