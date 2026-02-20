@extends('adminlte::page')

@section('title', 'จัดการใบสั่งผลิต')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>จัดการใบสั่งผลิต</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item active">ใบสั่งผลิต</li>
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
            <form method="GET" action="{{ route('admin.production-orders.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>ค้นหา</label>
                            <input type="text" name="search" class="form-control" 
                                   value="{{ request('search') }}" 
                                   placeholder="รหัสสั่งผลิต, สินค้า, SKU">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>สถานะ</label>
                            <select name="status" class="form-control">
                                <option value="">ทั้งหมด</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>รอดำเนินการ</option>
                                <option value="in_production" {{ request('status') == 'in_production' ? 'selected' : '' }}>กำลังผลิต</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>เสร็จสิ้น</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ยกเลิก</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>ความสำคัญ</label>
                            <select name="priority" class="form-control">
                                <option value="">ทั้งหมด</option>
                                <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>เร่งด่วน</option>
                                <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>สูง</option>
                                <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>ปกติ</option>
                                <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>ต่ำ</option>
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
            <h3 class="card-title">รายการใบสั่งผลิต</h3>
            <div class="card-tools">
                @can('create-edit')
                <a href="{{ route('admin.production-orders.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> สร้างใบสั่งผลิต
                </a>
                @endcan
                <a href="{{ route('admin.production-orders.dashboard') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-tachometer-alt"></i> แดชบอร์ด
                </a>
                <a href="{{ route('admin.production-orders.report') }}" class="btn btn-info btn-sm">
                    <i class="fas fa-chart-bar"></i> รายงาน
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th width="12%">รหัสสั่งผลิต</th>
                            <th width="18%">สินค้า</th>
                            <th width="12%">คลัง</th>
                            <th width="8%">จำนวน</th>
                            <th width="8%">ผลิตแล้ว</th>
                            <th width="8%">สถานะผลิต</th>
                            <th width="10%">กำหนดส่ง</th>
                            <th width="8%">ความสำคัญ</th>
                            <th width="8%">สถานะ</th>
                            <th width="8%">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr class="{{ $order->due_date < now() && !in_array($order->status, ['completed', 'cancelled']) ? 'table-warning' : '' }}">
                                <td>
                                    <strong>{{ $order->order_code }}</strong>
                                    @if($order->due_date < now() && !in_array($order->status, ['completed', 'cancelled']))
                                        <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> เลยกำหนด</small>
                                    @endif
                                    <br><small class="text-muted">{{ $order->order_type_text }}</small>
                                </td>
                                <td>
                                    @if($order->order_type === 'package' && $order->package)
                                        <strong><i class="fas fa-box-open text-primary"></i> {{ $order->package->name }}</strong><br>
                                        <small class="text-muted">{{ $order->package->code }}</small>
                                    @elseif($order->order_type === 'multiple')
                                        <strong><i class="fas fa-cubes text-success"></i> หลายรายการ</strong><br>
                                        <small class="text-muted">{{ $order->items->count() }} รายการ</small>
                                    @elseif($order->product)
                                        <strong>{{ $order->product->name }}</strong><br>
                                        <small class="text-muted">SKU: {{ $order->product->sku }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $order->targetWarehouse->code }}</span><br>
                                    <small>{{ $order->targetWarehouse->name }}</small>
                                </td>
                                <td class="text-center">
                                    @if($order->order_type === 'package')
                                        <span class="badge badge-primary">{{ number_format($order->quantity) }} แพ</span>
                                        @if($order->items->isNotEmpty())
                                            <br><small class="text-muted">{{ number_format($order->items->sum('quantity')) }} ชิ้น</small>
                                        @endif
                                    @elseif($order->order_type === 'multiple')
                                        <span class="badge badge-primary">{{ number_format($order->items->sum('quantity')) }} ชิ้น</span>
                                        <br><small class="text-muted">{{ $order->items->count() }} รายการ</small>
                                    @else
                                        <span class="badge badge-primary">{{ number_format($order->quantity) }} ชิ้น</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($order->order_type === 'package')
                                        <span class="badge badge-success">{{ number_format($order->produced_quantity) }} แพ</span>
                                        @if($order->items->isNotEmpty() && $order->items->sum('produced_quantity') > 0)
                                            <br><small class="text-muted">{{ number_format($order->items->sum('produced_quantity')) }} ชิ้น</small>
                                        @endif
                                    @elseif($order->order_type === 'multiple')
                                        <span class="badge badge-success">{{ number_format($order->items->sum('produced_quantity')) }} ชิ้น</span>
                                        @if($order->items->where('produced_quantity', '>', 0)->count() > 0)
                                            <br><small class="text-muted">{{ $order->items->where('produced_quantity', '>', 0)->count() }}/{{ $order->items->count() }} รายการ</small>
                                        @endif
                                    @else
                                        <span class="badge badge-success">{{ number_format($order->produced_quantity) }} ชิ้น</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($order->status === 'completed')
                                        <span class="badge badge-success">
                                            <i class="fas fa-check"></i> เสร็จแล้ว
                                        </span>
                                        @if($order->produced_quantity > 0)
                                            <br><small class="text-muted">{{ number_format($order->produced_quantity) }}/{{ number_format($order->quantity) }}</small>
                                        @endif
                                    @elseif($order->status === 'in_production')
                                        <span class="badge badge-info">
                                            <i class="fas fa-spinner"></i> กำลังผลิต
                                        </span>
                                    @elseif($order->status === 'pending')
                                        <span class="badge badge-warning">
                                            <i class="fas fa-clock"></i> รอดำเนินการ
                                        </span>
                                    @elseif($order->status === 'cancelled')
                                        <span class="badge badge-danger">
                                            <i class="fas fa-times"></i> ยกเลิก
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    {{ $order->due_date->format('d/m/Y') }}<br>
                                    <small class="text-muted">
                                        {{ $order->due_date->diffForHumans() }}
                                    </small>
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
                                    <span class="badge badge-{{ $priorityColors[$order->priority] }}">
                                        {{ $priorityTexts[$order->priority] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'in_production' => 'info',
                                            'completed' => 'success',
                                            'cancelled' => 'danger'
                                        ];
                                        $statusTexts = [
                                            'pending' => 'รอดำเนินการ',
                                            'in_production' => 'กำลังผลิต',
                                            'completed' => 'เสร็จสิ้น',
                                            'cancelled' => 'ยกเลิก'
                                        ];
                                    @endphp
                                    <span class="badge badge-{{ $statusColors[$order->status] }}">
                                        {{ $statusTexts[$order->status] }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.production-orders.show', $order) }}" 
                                           class="btn btn-info" title="ดูรายละเอียด">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($order->status == 'pending')
                                            @can('create-edit')
                                            <a href="{{ route('admin.production-orders.edit', $order) }}" 
                                               class="btn btn-warning" title="แก้ไข">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcan
                                            <button type="button" class="btn btn-success" 
                                                    onclick="updateStatus({{ $order->id }}, 'in_production')" title="เริ่มผลิต">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        @endif
                                        
                                        @if($order->status == 'in_production')
                                            <button type="button" class="btn btn-success" 
                                                    onclick="updateStatus({{ $order->id }}, 'completed')" title="เสร็จแล้ว">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                        
                                        @if(in_array($order->status, ['pending', 'in_production']))
                                            <button type="button" class="btn btn-danger" 
                                                    onclick="confirmCancel({{ $order->id }})" title="ยกเลิก">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="fas fa-industry fa-3x mb-3"></i><br>
                                    ยังไม่มีใบสั่งผลิต
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($orders->hasPages())
            <div class="card-footer">
                {{ $orders->withQueryString()->links() }}
            </div>
        @endif
    </div>

    <!-- Status Update Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">เปลี่ยนสถานะ</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <span id="statusMessage"></span>
                    </div>
                    <div class="form-group">
                        <label>หมายเหตุ (ไม่บังคับ)</label>
                        <textarea class="form-control" name="notes" rows="3" placeholder="หมายเหตุเพิ่มเติม"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    <form id="statusForm" method="POST" style="display: inline;">
                        @csrf
                        <input type="hidden" name="status" id="statusInput">
                        <button type="submit" class="btn btn-primary">ยืนยัน</button>
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
        .progress-sm {
            height: 0.5rem;
        }
    </style>
@stop

@section('js')
    <script>
        function updateStatus(orderId, status) {
            const statusTexts = {
                'pending': 'รอดำเนินการ',
                'in_production': 'กำลังผลิต',
                'completed': 'เสร็จแล้ว',
                'cancelled': 'ยกเลิก'
            };
            
            $('#statusForm').attr('action', '/admin/production-orders/' + orderId + '/update-status');
            $('#statusInput').val(status);
            $('#statusMessage').text('คุณต้องการเปลี่ยนสถานะเป็น "' + statusTexts[status] + '" หรือไม่?');
            $('#statusModal').modal('show');
        }



        function confirmCancel(orderId) {
            $('#statusForm').attr('action', '/admin/production-orders/' + orderId + '/update-status');
            $('#statusInput').val('cancelled');
            $('#statusMessage').text('คุณแน่ใจหรือไม่ที่จะยกเลิกใบสั่งผลิตนี้? การดำเนินการนี้ไม่สามารถย้อนกลับได้');
            $('#statusModal').modal('show');
        }

        // Alert auto hide
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
@stop