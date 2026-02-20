@extends('adminlte::page')

@section('title', 'รายละเอียดคำขอปรับปรุงสต็อก')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>รายละเอียดคำขอปรับปรุงสต็อก</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.stock-adjustments.index') }}">คำขอปรับปรุงสต็อก</a></li>
                <li class="breadcrumb-item active">#{{ $stockAdjustment->request_number }}</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <!-- Request Details -->
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">ข้อมูลคำขอ</h3>
                    <div class="card-tools">
                        <span class="badge badge-lg {{ $stockAdjustment->status_color }}">
                            {{ $stockAdjustment->status_text }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-hashtag"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">หมายเลขคำขอ</span>
                                    <span class="info-box-number">{{ $stockAdjustment->request_number }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon {{ $stockAdjustment->type_color }}">
                                    <i class="{{ $stockAdjustment->type_icon }}"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">ประเภท</span>
                                    <span class="info-box-number">{{ $stockAdjustment->type_text }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <strong>สินค้า:</strong>
                            <p class="text-muted">
                                {{ $stockAdjustment->product->name }}<br>
                                <small>{{ $stockAdjustment->product->sku }}</small>
                            </p>

                            <strong>คลัง:</strong>
                            <p class="text-muted">{{ $stockAdjustment->warehouse->name }}</p>

                            <strong>เหตุผล:</strong>
                            <p class="text-muted">{{ $stockAdjustment->reason_text }}</p>

                            @if($stockAdjustment->reference_document)
                                <strong>เอกสารอ้างอิง:</strong>
                                <p class="text-muted">{{ $stockAdjustment->reference_document }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <strong>จำนวนปัจจุบัน:</strong>
                            <p class="text-muted">{{ number_format($stockAdjustment->current_quantity) }} {{ $stockAdjustment->product->unit }}</p>

                            <strong>จำนวนที่ขอ:</strong>
                            <p class="text-muted">{{ number_format($stockAdjustment->requested_quantity) }} {{ $stockAdjustment->product->unit }}</p>

                            @if($stockAdjustment->final_quantity)
                                <strong>จำนวนที่อนุมัติ:</strong>
                                <p class="text-success">{{ number_format($stockAdjustment->final_quantity) }} {{ $stockAdjustment->product->unit }}</p>
                            @endif

                            <strong>ผู้ขอ:</strong>
                            <p class="text-muted">{{ $stockAdjustment->requestedBy->name }}</p>

                            <strong>วันที่ขอ:</strong>
                            <p class="text-muted">{{ $stockAdjustment->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <strong>รายละเอียด:</strong>
                    <div class="alert alert-light">
                        {{ $stockAdjustment->description }}
                    </div>

                    @if($stockAdjustment->approval_notes)
                        <strong>หมายเหตุการอนุมัติ:</strong>
                        <div class="alert alert-info">
                            {{ $stockAdjustment->approval_notes }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions & Status -->
        <div class="col-md-4">
            <!-- Action Panel -->
            @if($stockAdjustment->status === 'pending')
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">การดำเนินการ</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-warning">
                            <i class="fas fa-clock"></i>
                            คำขอนี้รอการอนุมัติ
                        </p>

                        @can('approve')
                        <!-- Approve Form -->
                        <form action="{{ route('admin.stock-adjustments.approve', $stockAdjustment) }}" method="POST" class="mb-3">
                            @csrf
                            <div class="form-group">
                                <label for="final_quantity">จำนวนที่อนุมัติ:</label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control" 
                                           id="final_quantity" 
                                           name="final_quantity" 
                                           value="{{ $stockAdjustment->requested_quantity }}"
                                           min="1"
                                           required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">{{ $stockAdjustment->product->unit }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="approval_notes">หมายเหตุ:</label>
                                <textarea class="form-control" 
                                          id="approval_notes" 
                                          name="approval_notes" 
                                          rows="3"
                                          placeholder="หมายเหตุเพิ่มเติม (ไม่บังคับ)"></textarea>
                            </div>
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-check"></i> อนุมัติ
                            </button>
                        </form>

                        <!-- Reject Form -->
                        <form action="{{ route('admin.stock-adjustments.reject', $stockAdjustment) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="reject_notes">เหตุผลการปฏิเสธ:</label>
                                <textarea class="form-control" 
                                          id="reject_notes" 
                                          name="approval_notes" 
                                          rows="3"
                                          placeholder="ระบุเหตุผลการปฏิเสธ"
                                          required></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger btn-block">
                                <i class="fas fa-times"></i> ปฏิเสธ
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>
            @endif

            @if($stockAdjustment->status === 'approved')
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">ดำเนินการ</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-success">
                            <i class="fas fa-check-circle"></i>
                            คำขอได้รับการอนุมัติแล้ว
                        </p>
                        <p class="text-muted">
                            <strong>ผู้อนุมัติ:</strong> {{ $stockAdjustment->approvedBy->name }}<br>
                            <strong>วันที่อนุมัติ:</strong> {{ $stockAdjustment->approved_at->format('d/m/Y H:i') }}
                        </p>

                        @can('approve')
                        <form action="{{ route('admin.stock-adjustments.process', $stockAdjustment) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-block" onclick="return confirm('ต้องการดำเนินการปรับปรุงสต็อกใช่หรือไม่?')">
                                <i class="fas fa-cogs"></i> ดำเนินการปรับปรุงสต็อก
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>
            @endif

            @if($stockAdjustment->status === 'completed')
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">สำเร็จ</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-info">
                            <i class="fas fa-check-double"></i>
                            ดำเนินการเสร็จสิ้น
                        </p>
                        <p class="text-muted">
                            <strong>ผู้ดำเนินการ:</strong> {{ $stockAdjustment->processedBy->name }}<br>
                            <strong>วันที่ดำเนินการ:</strong> {{ $stockAdjustment->processed_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>
            @endif

            @if($stockAdjustment->status === 'rejected')
                <div class="card card-danger">
                    <div class="card-header">
                        <h3 class="card-title">ปฏิเสธ</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-danger">
                            <i class="fas fa-times-circle"></i>
                            คำขอถูกปฏิเสธ
                        </p>
                        <p class="text-muted">
                            <strong>ผู้ปฏิเสธ:</strong> {{ $stockAdjustment->approvedBy->name }}<br>
                            <strong>วันที่ปฏิเสธ:</strong> {{ $stockAdjustment->approved_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>
            @endif

            <!-- History -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ประวัติการดำเนินการ</h3>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="time-label">
                            <span class="bg-info">{{ $stockAdjustment->created_at->format('d/m/Y') }}</span>
                        </div>

                        <div>
                            <i class="fas fa-plus bg-blue"></i>
                            <div class="timeline-item">
                                <span class="time">
                                    <i class="fas fa-clock"></i> {{ $stockAdjustment->created_at->format('H:i') }}
                                </span>
                                <h3 class="timeline-header">สร้างคำขอ</h3>
                                <div class="timeline-body">
                                    {{ $stockAdjustment->requestedBy->name }} สร้างคำขอปรับปรุงสต็อก
                                </div>
                            </div>
                        </div>

                        @if($stockAdjustment->approved_at)
                            @if($stockAdjustment->status === 'rejected')
                                <div>
                                    <i class="fas fa-times bg-red"></i>
                                    <div class="timeline-item">
                                        <span class="time">
                                            <i class="fas fa-clock"></i> {{ $stockAdjustment->approved_at->format('H:i') }}
                                        </span>
                                        <h3 class="timeline-header">ปฏิเสธคำขอ</h3>
                                        <div class="timeline-body">
                                            {{ $stockAdjustment->approvedBy->name }} ปฏิเสธคำขอ
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div>
                                    <i class="fas fa-check bg-green"></i>
                                    <div class="timeline-item">
                                        <span class="time">
                                            <i class="fas fa-clock"></i> {{ $stockAdjustment->approved_at->format('H:i') }}
                                        </span>
                                        <h3 class="timeline-header">อนุมัติคำขอ</h3>
                                        <div class="timeline-body">
                                            {{ $stockAdjustment->approvedBy->name }} อนุมัติคำขอ
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif

                        @if($stockAdjustment->processed_at)
                            <div>
                                <i class="fas fa-cogs bg-purple"></i>
                                <div class="timeline-item">
                                    <span class="time">
                                        <i class="fas fa-clock"></i> {{ $stockAdjustment->processed_at->format('H:i') }}
                                    </span>
                                    <h3 class="timeline-header">ดำเนินการเสร็จสิ้น</h3>
                                    <div class="timeline-body">
                                        {{ $stockAdjustment->processedBy->name }} ดำเนินการปรับปรุงสต็อกแล้ว
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
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="{{ route('admin.stock-adjustments.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> กลับไปรายการ
                            </a>
                        </div>
                        <div class="col-md-6 text-right">
                            @if($stockAdjustment->status === 'pending')
                                @can('create-edit')
                                <a href="{{ route('admin.stock-adjustments.edit', $stockAdjustment) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> แก้ไข
                                </a>
                                @endcan
                                @can('delete')
                                <form action="{{ route('admin.stock-adjustments.destroy', $stockAdjustment) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('ต้องการลบคำขอนี้ใช่หรือไม่?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash"></i> ลบ
                                    </button>
                                </form>
                                @endcan
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .timeline {
            position: relative;
            margin: 0 0 30px 0;
            padding: 0;
            list-style: none;
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
        
        .timeline > div {
            margin-bottom: 15px;
            position: relative;
        }
        
        .timeline > div > .timeline-item {
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
            border-radius: 3px;
            margin-top: 0;
            background: #fff;
            color: #444;
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
            color: #666;
            background: #d2d6de;
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
            padding: 5px;
        }
        
        .timeline-header {
            margin: 0;
            color: #555;
            border-bottom: 1px solid #f4f4f4;
            padding: 10px;
            font-weight: 600;
            font-size: 16px;
        }
        
        .timeline-body, .timeline-footer {
            padding: 10px;
        }
        
        .time {
            color: #999;
            float: right;
            padding: 10px;
            font-size: 12px;
        }
    </style>
@stop