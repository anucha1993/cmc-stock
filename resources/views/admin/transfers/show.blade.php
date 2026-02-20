@extends('adminlte::page')

@section('title', 'รายละเอียดการโอนสินค้า')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>รายละเอียดการโอนสินค้า</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.transfers.index') }}">การโอนสินค้า</a></li>
                <li class="breadcrumb-item active">{{ $transfer->transfer_code }}</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <!-- Transfer Info -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ใบโอนสินค้า: {{ $transfer->transfer_code }}</h3>
                    <div class="card-tools">
                        @php
                            $statusColors = [
                                'pending' => 'warning',
                                'in_transit' => 'info',
                                'completed' => 'success',
                                'cancelled' => 'danger'
                            ];
                            $statusTexts = [
                                'pending' => 'รออนุมัติ',
                                'in_transit' => 'กำลังขนส่ง',
                                'completed' => 'เสร็จสิ้น',
                                'cancelled' => 'ยกเลิก'
                            ];
                        @endphp
                        <span class="badge badge-{{ $statusColors[$transfer->status] }} badge-lg">
                            {{ $statusTexts[$transfer->status] }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">รหัสโอน:</th>
                                    <td><strong>{{ $transfer->transfer_code }}</strong></td>
                                </tr>
                                <tr>
                                    <th>สินค้า:</th>
                                    <td>
                                        <strong>{{ $transfer->product->full_name }}</strong><br>
                                        <small class="text-muted">SKU: {{ $transfer->product->sku }}</small><br>
                                        <small class="text-muted">หมวดหมู่: {{ $transfer->product->category->name ?? '-' }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>จำนวน:</th>
                                    <td><strong class="text-primary">{{ number_format($transfer->quantity) }} {{ $transfer->product->unit ?? 'ชิ้น' }}</strong></td>
                                </tr>
                                <tr>
                                    <th>ความสำคัญ:</th>
                                    <td>
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
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">วันที่โอน:</th>
                                    <td>{{ $transfer->transfer_date->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>ผู้ร้องขอ:</th>
                                    <td>{{ $transfer->user->name }}</td>
                                </tr>
                                @if($transfer->approved_at)
                                <tr>
                                    <th>อนุมัติเมื่อ:</th>
                                    <td>
                                        {{ $transfer->approved_at->format('d/m/Y H:i') }}<br>
                                        <small class="text-muted">โดย {{ $transfer->approvedByUser->name ?? '-' }}</small>
                                    </td>
                                </tr>
                                @endif
                                @if($transfer->completed_at)
                                <tr>
                                    <th>เสร็จสิ้นเมื่อ:</th>
                                    <td>
                                        {{ $transfer->completed_at->format('d/m/Y H:i') }}<br>
                                        <small class="text-muted">โดย {{ $transfer->completedByUser->name ?? '-' }}</small>
                                    </td>
                                </tr>
                                @endif
                                @if($transfer->cancelled_at)
                                <tr>
                                    <th>ยกเลิกเมื่อ:</th>
                                    <td>
                                        {{ $transfer->cancelled_at->format('d/m/Y H:i') }}<br>
                                        <small class="text-muted">โดย {{ $transfer->cancelledByUser->name ?? '-' }}</small>
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <!-- Warehouse Transfer Flow -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>การโอนระหว่างคลัง</h5>
                            <div class="d-flex align-items-center justify-content-center">
                                <!-- From Warehouse -->
                                <div class="text-center">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-warning"><i class="fas fa-warehouse"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">คลังต้นทาง</span>
                                            <span class="info-box-number">{{ $transfer->fromWarehouse->code }}</span>
                                            <div class="text-muted small">{{ $transfer->fromWarehouse->name }}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Arrow -->
                                <div class="mx-4">
                                    <i class="fas fa-arrow-right fa-3x text-primary"></i>
                                </div>

                                <!-- To Warehouse -->
                                <div class="text-center">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-success"><i class="fas fa-warehouse"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">คลังปลายทาง</span>
                                            <span class="info-box-number">{{ $transfer->toWarehouse->code }}</span>
                                            <div class="text-muted small">{{ $transfer->toWarehouse->name }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Timeline -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>สถานะการดำเนินการ</h5>
                            <div class="timeline">
                                <div class="time-label">
                                    <span class="bg-info">{{ $transfer->transfer_date->format('d/m/Y') }}</span>
                                </div>
                                
                                <div>
                                    <i class="fas fa-plus bg-primary"></i>
                                    <div class="timeline-item">
                                        <span class="time"><i class="fas fa-clock"></i> {{ $transfer->transfer_date->format('H:i') }}</span>
                                        <h3 class="timeline-header">สร้างใบโอน</h3>
                                        <div class="timeline-body">
                                            ผู้ใช้ {{ $transfer->user->name }} สร้างใบโอนสินค้า
                                        </div>
                                    </div>
                                </div>

                                @if($transfer->approved_at)
                                <div>
                                    <i class="fas fa-check bg-success"></i>
                                    <div class="timeline-item">
                                        <span class="time"><i class="fas fa-clock"></i> {{ $transfer->approved_at->format('H:i') }}</span>
                                        <h3 class="timeline-header">อนุมัติการโอน</h3>
                                        <div class="timeline-body">
                                            {{ $transfer->approvedByUser->name ?? 'ระบบ' }} อนุมัติการโอนและเริ่มขนส่ง
                                        </div>
                                    </div>
                                </div>
                                @endif

                                @if($transfer->completed_at)
                                <div>
                                    <i class="fas fa-check-double bg-primary"></i>
                                    <div class="timeline-item">
                                        <span class="time"><i class="fas fa-clock"></i> {{ $transfer->completed_at->format('H:i') }}</span>
                                        <h3 class="timeline-header">เสร็จสิ้นการโอน</h3>
                                        <div class="timeline-body">
                                            {{ $transfer->completedByUser->name ?? 'ระบบ' }} รับสินค้าและเสร็จสิ้นการโอน
                                        </div>
                                    </div>
                                </div>
                                @endif

                                @if($transfer->cancelled_at)
                                <div>
                                    <i class="fas fa-times bg-danger"></i>
                                    <div class="timeline-item">
                                        <span class="time"><i class="fas fa-clock"></i> {{ $transfer->cancelled_at->format('H:i') }}</span>
                                        <h3 class="timeline-header">ยกเลิกการโอน</h3>
                                        <div class="timeline-body">
                                            {{ $transfer->cancelledByUser->name ?? 'ระบบ' }} ยกเลิกการโอนสินค้า
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <div>
                                    <i class="fas fa-clock bg-gray"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    @if($transfer->notes)
                        <div class="row mt-3">
                            <div class="col-12">
                                <h5>หมายเหตุ</h5>
                                <div class="alert alert-light">
                                    {{ $transfer->notes }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">การดำเนินการ</h3>
                </div>
                <div class="card-body">
                    @if($transfer->status == 'pending')
                        @can('approve')
                        <button type="button" class="btn btn-success btn-block mb-2" 
                                onclick="confirmApprove()">
                            <i class="fas fa-check"></i> อนุมัติการโอน
                        </button>
                        @endcan
                        @can('create-edit')
                        <a href="{{ route('admin.transfers.edit', $transfer) }}" 
                           class="btn btn-warning btn-block mb-2">
                            <i class="fas fa-edit"></i> แก้ไข
                        </a>
                        @endcan
                    @endif

                    @if($transfer->status == 'in_transit')
                        @can('approve')
                        <button type="button" class="btn btn-primary btn-block mb-2" 
                                onclick="confirmComplete()">
                            <i class="fas fa-check-double"></i> รับสินค้า
                        </button>
                        @endcan
                    @endif

                    @if(in_array($transfer->status, ['pending', 'in_transit']))
                        <button type="button" class="btn btn-danger btn-block mb-2" 
                                onclick="confirmCancel()">
                            <i class="fas fa-times"></i> ยกเลิก
                        </button>
                    @endif

                    <a href="{{ route('admin.transfers.index') }}" 
                       class="btn btn-secondary btn-block">
                        <i class="fas fa-arrow-left"></i> ย้อนกลับ
                    </a>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">สถิติด่วน</h3>
                </div>
                <div class="card-body">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-clock"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">เวลาที่ใช้</span>
                            <span class="info-box-number">
                                @if($transfer->completed_at)
                                    {{ $transfer->transfer_date->diffForHumans($transfer->completed_at, true) }}
                                @elseif($transfer->cancelled_at)
                                    ยกเลิกแล้ว
                                @else
                                    {{ $transfer->transfer_date->diffForHumans() }}
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="info-box">
                        <span class="info-box-icon bg-primary"><i class="fas fa-boxes"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">จำนวนสินค้า</span>
                            <span class="info-box-number">{{ number_format($transfer->quantity) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
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
                    <p>คุณแน่ใจหรือไม่ที่จะอนุมัติการโอนสินค้านี้?</p>
                    <p class="text-info"><i class="fas fa-info-circle"></i> สินค้าจะถูกโอนและเปลี่ยนสถานะเป็น "กำลังขนส่ง"</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    <form action="{{ route('admin.transfers.approve', $transfer) }}" method="POST" style="display: inline;">
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
                    <p class="text-info"><i class="fas fa-info-circle"></i> สินค้าจะถูกเพิ่มเข้าคลังปลายทาง</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    <form action="{{ route('admin.transfers.complete', $transfer) }}" method="POST" style="display: inline;">
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
                    <p>คุณแน่ใจหรือไม่ที่จะยกเลิกการโอนสินค้านี้?</p>
                    <p class="text-warning"><i class="fas fa-exclamation-triangle"></i> การดำเนินการนี้ไม่สามารถยกเลิกได้</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    <form action="{{ route('admin.transfers.cancel', $transfer) }}" method="POST" style="display: inline;">
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
        .badge-lg {
            font-size: 1em;
            padding: 8px 12px;
        }
        .timeline {
            margin: 0 0 45px 0;
            padding: 0;
            position: relative;
        }
        .timeline:before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            width: 4px;
            background: #dee2e6;
            left: 31px;
            margin: 0;
            border-radius: 2px;
        }
        .timeline > div > .timeline-item {
            box-shadow: 0 1px 3px rgba(0,0,0,0.12);
            border-radius: 3px;
            margin-top: 0;
            background: #fff;
            color: #495057;
            margin-left: 60px;
            margin-right: 15px;
            padding: 0;
            position: relative;
        }
        .timeline > div > .fas {
            width: 30px;
            height: 30px;
            font-size: 15px;
            line-height: 30px;
            position: absolute;
            color: #fff;
            background: #6c757d;
            border-radius: 50%;
            text-align: center;
            left: 18px;
            top: 0;
        }
        .timeline > .time-label > span {
            font-weight: 600;
            color: #fff;
            border-radius: 4px;
            display: inline-block;
            padding: 5px 10px;
        }
        .timeline-header {
            margin: 0;
            color: #495057;
            border-bottom: 1px solid #dee2e6;
            padding: 10px 15px;
            font-size: 16px;
            line-height: 1.1;
        }
        .timeline-body, .timeline-footer {
            padding: 10px 15px;
        }
        .timeline > div > .timeline-item > .time {
            color: #999;
            float: right;
            padding: 10px;
            font-size: 12px;
        }
        .info-box {
            margin-bottom: 1rem;
        }
    </style>
@stop

@section('js')
    <script>
        function confirmApprove() {
            $('#approveModal').modal('show');
        }

        function confirmComplete() {
            $('#completeModal').modal('show');
        }

        function confirmCancel() {
            $('#cancelModal').modal('show');
        }

        // Alert auto hide
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
@stop