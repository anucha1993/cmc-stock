@extends('adminlte::page')

@section('title', 'รายละเอียดใบเคลม ' . $claim->claim_number)

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-shield-alt"></i> ใบเคลม {{ $claim->claim_number }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.claims.index') }}">การเคลมสินค้า</a></li>
                <li class="breadcrumb-item active">{{ $claim->claim_number }}</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('error') }}
        </div>
    @endif

    <!-- Action Buttons -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="btn-group">
                <a href="{{ route('admin.claims.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> กลับ
                </a>

                @if(in_array($claim->status, ['pending', 'reviewing']))
                    @can('create-edit')
                    <a href="{{ route('admin.claims.edit', $claim) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> แก้ไข
                    </a>
                    @endcan
                @endif

                @if($claim->status === 'pending')
                    @can('approve')
                    <form action="{{ route('admin.claims.review', $claim) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-search"></i> เริ่มตรวจสอบ
                        </button>
                    </form>
                    @endcan
                @endif

                @if(in_array($claim->status, ['pending', 'reviewing']))
                    @can('approve')
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#approveModal">
                        <i class="fas fa-check"></i> อนุมัติ
                    </button>
                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#rejectModal">
                        <i class="fas fa-times"></i> ปฏิเสธ
                    </button>
                    @endcan
                @endif

                @if($claim->status === 'approved')
                    @can('approve')
                    <form action="{{ route('admin.claims.process', $claim) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-cogs"></i> เริ่มดำเนินการ
                        </button>
                    </form>
                    @endcan
                @endif

                @if($claim->status === 'processing')
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#completeModal">
                        <i class="fas fa-check-double"></i> เสร็จสิ้น
                    </button>
                @endif

                @if(!in_array($claim->status, ['completed', 'cancelled']))
                    <button type="button" class="btn btn-outline-danger" data-toggle="modal" data-target="#cancelModal">
                        <i class="fas fa-ban"></i> ยกเลิก
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <!-- ข้อมูลเคลม -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-alt"></i> ข้อมูลใบเคลม</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="35%">เลขที่เคลม:</th>
                            <td><strong>{{ $claim->claim_number }}</strong></td>
                        </tr>
                        <tr>
                            <th>ที่มาเคลม:</th>
                            <td><span class="badge badge-{{ $claim->claim_source_color }} px-3 py-1">{{ $claim->claim_source_text }}</span></td>
                        </tr>
                        <tr>
                            <th>สถานะ:</th>
                            <td><span class="badge badge-{{ $claim->status_color }} px-3 py-2">{{ $claim->status_text }}</span></td>
                        </tr>
                        <tr>
                            <th>ประเภท:</th>
                            <td><span class="badge badge-{{ $claim->claim_type_color }}">{{ $claim->claim_type_text }}</span></td>
                        </tr>
                        <tr>
                            <th>ลำดับความสำคัญ:</th>
                            <td><span class="badge badge-{{ $claim->priority_color }}">{{ $claim->priority_text }}</span></td>
                        </tr>
                        <tr>
                            <th>วิธีดำเนินการ:</th>
                            <td>{{ $claim->resolution_type_text }}</td>
                        </tr>
                        <tr>
                            <th>วันที่เคลม:</th>
                            <td>{{ $claim->claim_date->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>เลขที่อ้างอิง:</th>
                            <td>{{ $claim->reference_number ?? '-' }}</td>
                        </tr>
                        @if($claim->deliveryNote)
                        <tr>
                            <th>ใบส่งของ:</th>
                            <td>
                                <a href="{{ route('admin.delivery-notes.show', $claim->deliveryNote) }}">
                                    {{ $claim->deliveryNote->delivery_number }}
                                </a>
                            </td>
                        </tr>
                        @endif
                        @if($claim->damagedWarehouse)
                        <tr>
                            <th>คลังสินค้าชำรุด:</th>
                            <td>{{ $claim->damagedWarehouse->name }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- ข้อมูลลูกค้า -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user"></i> ข้อมูลลูกค้า</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="35%">ชื่อ:</th>
                            <td>{{ $claim->customer_name }}</td>
                        </tr>
                        <tr>
                            <th>โทรศัพท์:</th>
                            <td>{{ $claim->customer_phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>อีเมล:</th>
                            <td>{{ $claim->customer_email ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>ที่อยู่:</th>
                            <td>{{ $claim->customer_address ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- ข้อมูลผู้ดำเนินการ -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-users"></i> ประวัติการดำเนินการ</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tr>
                            <th>ผู้สร้าง:</th>
                            <td>{{ $claim->creator->name ?? '-' }}</td>
                            <td>{{ $claim->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @if($claim->reviewer)
                        <tr>
                            <th>ผู้ตรวจสอบ:</th>
                            <td>{{ $claim->reviewer->name }}</td>
                            <td>{{ $claim->reviewed_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                        @endif
                        @if($claim->approver)
                        <tr>
                            <th>ผู้อนุมัติ:</th>
                            <td>{{ $claim->approver->name }}</td>
                            <td>{{ $claim->approved_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                        @endif
                        @if($claim->processor)
                        <tr>
                            <th>ผู้ดำเนินการ:</th>
                            <td>{{ $claim->processor->name }}</td>
                            <td>{{ $claim->processed_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                        @endif
                        @if($claim->completed_at)
                        <tr>
                            <th>เสร็จสิ้น:</th>
                            <td colspan="2">{{ $claim->completed_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- รายละเอียด -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-align-left"></i> รายละเอียดปัญหา</h3>
        </div>
        <div class="card-body">
            <p>{!! nl2br(e($claim->description)) !!}</p>
            @if($claim->resolution_notes)
                <hr>
                <h6><strong>หมายเหตุการดำเนินการ:</strong></h6>
                <p class="text-success">{!! nl2br(e($claim->resolution_notes)) !!}</p>
            @endif
            @if($claim->rejection_reason)
                <hr>
                <h6><strong>เหตุผลที่ปฏิเสธ:</strong></h6>
                <p class="text-danger">{!! nl2br(e($claim->rejection_reason)) !!}</p>
            @endif
        </div>
    </div>

    <!-- รายการสินค้า -->
    <div class="card card-warning">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-boxes"></i> รายการสินค้าที่เคลม ({{ $claim->items->count() }} รายการ, {{ $claim->total_items }} ชิ้น)</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-striped mb-0">
                <thead class="bg-light">
                    <tr>
                        <th width="30">#</th>
                        <th>สินค้า</th>
                        <th>Barcode</th>
                        <th width="80">จำนวน</th>
                        <th>สาเหตุ</th>
                        <th>สถานะชำรุด</th>
                        <th>การดำเนินการ</th>
                        <th>หมายเหตุ</th>
                        @if(in_array($claim->status, ['reviewing', 'approved', 'processing']))
                            <th width="150">จัดการ</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($claim->items as $idx => $item)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>
                                <strong>{{ $item->product->full_name ?? 'N/A' }}</strong>
                                <br><small class="text-muted">{{ $item->product->sku ?? '' }}</small>
                            </td>
                            <td>
                                @if($item->stockItem)
                                    <code>{{ $item->stockItem->barcode }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td><span class="badge badge-secondary">{{ $item->reason_text }}</span></td>
                            <td>
                                <span class="badge badge-{{ $item->damaged_status_color }}">
                                    {{ $item->damaged_status_text }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $item->action_taken_color }}">
                                    {{ $item->action_taken_text }}
                                </span>
                            </td>
                            <td>
                                {{ $item->description ?? '-' }}
                                @if($item->inspection_notes)
                                    <br><small class="text-info"><i class="fas fa-clipboard-check"></i> {{ $item->inspection_notes }}</small>
                                @endif
                            </td>
                            @if(in_array($claim->status, ['reviewing', 'approved', 'processing']))
                                <td>
                                    @if($item->damaged_status === 'pending_inspection')
                                        <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#inspectModal-{{ $item->id }}">
                                            <i class="fas fa-search"></i> ตรวจ
                                        </button>
                                    @endif

                                    @if(in_array($item->damaged_status, ['confirmed_damaged', 'repairable', 'unrepairable']) && $item->action_taken === 'none')
                                        <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#processItemModal-{{ $item->id }}">
                                            <i class="fas fa-cogs"></i> จัดการ
                                        </button>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===== Modals ===== -->

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.claims.approve', $claim) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white"><i class="fas fa-check"></i> อนุมัติใบเคลม</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>วิธีดำเนินการ <span class="text-danger">*</span></label>
                            <select name="resolution_type" class="form-control" required>
                                <option value="replace">เปลี่ยนสินค้าใหม่</option>
                                <option value="repair">ซ่อมแซม</option>
                                <option value="refund">คืนเงิน</option>
                                <option value="credit">เครดิต</option>
                                <option value="none">ไม่มีการดำเนินการ</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>หมายเหตุ</label>
                            <textarea name="resolution_notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                        <button type="submit" class="btn btn-success">อนุมัติ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.claims.reject', $claim) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-danger">
                        <h5 class="modal-title text-white"><i class="fas fa-times"></i> ปฏิเสธใบเคลม</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>เหตุผลที่ปฏิเสธ <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                        <button type="submit" class="btn btn-danger">ปฏิเสธ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Complete Modal -->
    <div class="modal fade" id="completeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.claims.complete', $claim) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white"><i class="fas fa-check-double"></i> เสร็จสิ้นใบเคลม</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>หมายเหตุสรุปงาน</label>
                            <textarea name="resolution_notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                        <button type="submit" class="btn btn-success">ยืนยันเสร็จสิ้น</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.claims.cancel', $claim) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-secondary">
                        <h5 class="modal-title text-white"><i class="fas fa-ban"></i> ยกเลิกใบเคลม</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>เหตุผลที่ยกเลิก</label>
                            <textarea name="cancel_reason" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                        <button type="submit" class="btn btn-danger">ยืนยันยกเลิก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Inspect Item Modals -->
    @foreach($claim->items as $item)
        @if($item->damaged_status === 'pending_inspection')
        <div class="modal fade" id="inspectModal-{{ $item->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('admin.claims.inspect-item', [$claim, $item]) }}" method="POST">
                        @csrf
                        <div class="modal-header bg-info">
                            <h5 class="modal-title text-white"><i class="fas fa-search"></i> ตรวจสอบสินค้า: {{ $item->product->full_name }}</h5>
                            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>ผลการตรวจสอบ <span class="text-danger">*</span></label>
                                <select name="damaged_status" class="form-control" required>
                                    <option value="confirmed_damaged">ยืนยันชำรุด</option>
                                    <option value="repairable">ซ่อมได้</option>
                                    <option value="unrepairable">ซ่อมไม่ได้</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>หมายเหตุการตรวจสอบ</label>
                                <textarea name="inspection_notes" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                            <button type="submit" class="btn btn-info">บันทึกผลตรวจสอบ</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        @if(in_array($item->damaged_status, ['confirmed_damaged', 'repairable', 'unrepairable']) && $item->action_taken === 'none')
        <div class="modal fade" id="processItemModal-{{ $item->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('admin.claims.process-item', [$claim, $item]) }}" method="POST">
                        @csrf
                        <div class="modal-header bg-warning">
                            <h5 class="modal-title"><i class="fas fa-cogs"></i> จัดการสินค้า: {{ $item->product->full_name }}</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <strong>สถานะ:</strong> {{ $item->damaged_status_text }}
                                <br><strong>สาเหตุ:</strong> {{ $item->reason_text }}
                            </div>
                            <div class="form-group">
                                <label>การดำเนินการ <span class="text-danger">*</span></label>
                                <select name="action" class="form-control action-select" required>
                                    <option value="scrap">ทำลาย/ตัดทิ้ง</option>
                                    <option value="return_supplier">ส่งคืนผู้จำหน่าย</option>
                                    <option value="return_stock">คืนเข้าสต็อก (สินค้าปกติ)</option>
                                    <option value="replace">เปลี่ยนทดแทน</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>หมายเหตุ</label>
                                <textarea name="notes" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                            <button type="submit" class="btn btn-warning">ดำเนินการ</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    @endforeach
@stop
