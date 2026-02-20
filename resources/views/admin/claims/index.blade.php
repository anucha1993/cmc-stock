@extends('adminlte::page')

@section('title', 'การเคลมสินค้า')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-shield-alt"></i> การเคลมสินค้า</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item active">การเคลมสินค้า</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Stats Cards -->
    <div class="row">
        <div class="col-lg-2 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $stats['total'] ?? 0 }}</h3>
                    <p>ทั้งหมด</p>
                </div>
                <div class="icon"><i class="fas fa-list"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['pending'] ?? 0 }}</h3>
                    <p>รอตรวจสอบ</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['reviewing'] ?? 0 }}</h3>
                    <p>กำลังตรวจสอบ</p>
                </div>
                <div class="icon"><i class="fas fa-search"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $stats['approved'] ?? 0 }}</h3>
                    <p>อนุมัติแล้ว</p>
                </div>
                <div class="icon"><i class="fas fa-check"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['processing'] ?? 0 }}</h3>
                    <p>กำลังดำเนินการ</p>
                </div>
                <div class="icon"><i class="fas fa-cogs"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['completed'] ?? 0 }}</h3>
                    <p>เสร็จสิ้น</p>
                </div>
                <div class="icon"><i class="fas fa-check-double"></i></div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">กรองข้อมูล</h3>
            <div class="card-tools">
                @can('create-edit')
                <a href="{{ route('admin.claims.create', ['source' => 'delivery_note']) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-file-invoice"></i> เคลมจากใบตัดสต็อก
                </a>
                <a href="{{ route('admin.claims.create', ['source' => 'stock_damage']) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-warehouse"></i> เคลมชำรุดจากสต็อก
                </a>
                @endcan
                <a href="{{ route('admin.claims.damaged-report') }}" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-exclamation-triangle"></i> รายงานสินค้าชำรุด
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.claims.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <input type="text" name="search" class="form-control" placeholder="ค้นหาเลขเคลม, ชื่อลูกค้า..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <select name="status" class="form-control">
                                <option value="">-- สถานะทั้งหมด --</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>รอตรวจสอบ</option>
                                <option value="reviewing" {{ request('status') == 'reviewing' ? 'selected' : '' }}>กำลังตรวจสอบ</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>อนุมัติแล้ว</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>ปฏิเสธ</option>
                                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>กำลังดำเนินการ</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>เสร็จสิ้น</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ยกเลิก</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <select name="claim_type" class="form-control">
                                <option value="">-- ประเภททั้งหมด --</option>
                                <option value="defective" {{ request('claim_type') == 'defective' ? 'selected' : '' }}>สินค้าชำรุด</option>
                                <option value="damaged" {{ request('claim_type') == 'damaged' ? 'selected' : '' }}>สินค้าเสียหาย</option>
                                <option value="wrong_item" {{ request('claim_type') == 'wrong_item' ? 'selected' : '' }}>สินค้าผิดรายการ</option>
                                <option value="missing_item" {{ request('claim_type') == 'missing_item' ? 'selected' : '' }}>สินค้าขาดหาย</option>
                                <option value="warranty" {{ request('claim_type') == 'warranty' ? 'selected' : '' }}>เคลมประกัน</option>
                                <option value="other" {{ request('claim_type') == 'other' ? 'selected' : '' }}>อื่นๆ</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <select name="claim_source" class="form-control">
                                <option value="">-- ที่มาทั้งหมด --</option>
                                <option value="delivery_note" {{ request('claim_source') == 'delivery_note' ? 'selected' : '' }}>จากใบตัดสต็อก</option>
                                <option value="stock_damage" {{ request('claim_source') == 'stock_damage' ? 'selected' : '' }}>ชำรุดจากสต็อก</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <select name="priority" class="form-control">
                                <option value="">-- ลำดับความสำคัญ --</option>
                                <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>เร่งด่วน</option>
                                <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>สูง</option>
                                <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>ปกติ</option>
                                <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>ต่ำ</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> ค้นหา</button>
                        <a href="{{ route('admin.claims.index') }}" class="btn btn-secondary"><i class="fas fa-redo"></i> รีเซ็ต</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Claims Table -->
    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>เลขที่เคลม</th>
                        <th>ที่มา</th>
                        <th>ลูกค้า</th>
                        <th>ประเภท</th>
                        <th>สินค้า</th>
                        <th>ลำดับความสำคัญ</th>
                        <th>สถานะ</th>
                        <th>วันที่เคลม</th>
                        <th>ผู้สร้าง</th>
                        <th width="120">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($claims as $claim)
                        <tr>
                            <td>
                                <a href="{{ route('admin.claims.show', $claim) }}">
                                    <strong>{{ $claim->claim_number }}</strong>
                                </a>
                            </td>
                            <td>
                                <span class="badge badge-{{ $claim->claim_source_color }}">
                                    {{ $claim->claim_source_text }}
                                </span>
                                @if($claim->deliveryNote)
                                    <br><small><a href="{{ route('admin.delivery-notes.show', $claim->deliveryNote) }}">{{ $claim->deliveryNote->delivery_number }}</a></small>
                                @endif
                            </td>
                            <td>
                                {{ $claim->customer_name }}
                                @if($claim->customer_phone)
                                    <br><small class="text-muted">{{ $claim->customer_phone }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $claim->claim_type_color }}">
                                    {{ $claim->claim_type_text }}
                                </span>
                            </td>
                            <td>
                                {{ $claim->items->count() }} รายการ
                                <br><small class="text-muted">{{ $claim->total_items }} ชิ้น</small>
                            </td>
                            <td>
                                <span class="badge badge-{{ $claim->priority_color }}">
                                    {{ $claim->priority_text }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $claim->status_color }}">
                                    {{ $claim->status_text }}
                                </span>
                            </td>
                            <td>{{ $claim->claim_date->format('d/m/Y') }}</td>
                            <td>{{ $claim->creator->name ?? '-' }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.claims.show', $claim) }}" class="btn btn-info" title="ดูรายละเอียด">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(in_array($claim->status, ['pending', 'reviewing']))
                                        @can('create-edit')
                                        <a href="{{ route('admin.claims.edit', $claim) }}" class="btn btn-warning" title="แก้ไข">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                    @endif
                                    @if(in_array($claim->status, ['pending', 'cancelled']))
                                        @can('delete')
                                        <form action="{{ route('admin.claims.destroy', $claim) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" title="ลบ">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">ไม่มีรายการเคลม</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($claims->hasPages())
            <div class="card-footer">
                {{ $claims->withQueryString()->links() }}
            </div>
        @endif
    </div>
@stop
