@extends('adminlte::page')

@section('title', 'ใบตัดสต็อก/ขาย - CMC-STOCK')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>ใบตัดสต็อก/ขาย</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">แดชบอร์ด</a></li>
                <li class="breadcrumb-item active">ใบตัดสต็อก</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <!-- Filter Card -->
    <div class="card card-outline card-primary collapsed-card">
        <div class="card-header">
            <h3 class="card-title">กรองข้อมูล</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.delivery-notes.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>ค้นหา</label>
                            <input type="text" class="form-control" name="search" value="{{ request('search') }}" 
                                placeholder="เลขที่, ลูกค้า, เลขที่อ้างอิง">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>สถานะ</label>
                            <select class="form-control" name="status">
                                <option value="">ทั้งหมด</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>รอยืนยัน</option>
                                <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>ยืนยันแล้ว</option>
                                <option value="scanned" {{ request('status') == 'scanned' ? 'selected' : '' }}>สแกนแล้ว</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>เสร็จสิ้น</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>วันที่เริ่มต้น</label>
                            <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>วันที่สิ้นสุด</label>
                            <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">รายการใบตัดสต็อก</h3>
            <div class="card-tools">
                <a href="{{ route('admin.delivery-notes.create') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i> สร้างใบตัดสต็อกใหม่
                </a>
            </div>
        </div>
        
        <div class="card-body">
            @if($deliveryNotes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="120">เลขที่ใบตัดสต็อก</th>
                                <th width="100">วันที่จัดส่ง</th>
                                <th>ลูกค้า</th>
                                <th width="150">เลขที่อ้างอิง</th>
                                <th width="100" class="text-center">จำนวนรายการ</th>
                                <th width="100" class="text-center">สถานะ</th>
                                <th width="180" class="text-center">การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deliveryNotes as $note)
                            <tr>
                                <td>
                                    <strong>{{ $note->delivery_number }}</strong>
                                    <br><small class="text-muted">{{ $note->created_at->format('d/m/Y H:i') }}</small>
                                </td>
                                <td>{{ $note->delivery_date->format('d/m/Y') }}</td>
                                <td>
                                    <strong>{{ $note->customer_name }}</strong>
                                    @if($note->customer_phone)
                                        <br><small class="text-muted"><i class="fas fa-phone"></i> {{ $note->customer_phone }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($note->sales_order_number)
                                        <small>ใบสั่งขาย: <strong>{{ $note->sales_order_number }}</strong></small><br>
                                    @endif
                                    @if($note->quotation_number)
                                        <small>ใบเสนอราคา: <strong>{{ $note->quotation_number }}</strong></small>
                                    @endif
                                    @if(!$note->sales_order_number && !$note->quotation_number)
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info">{{ $note->total_items }}</span>
                                    @if($note->status === 'scanned' || $note->status === 'completed')
                                        <br><small class="text-success">สแกน: {{ $note->total_scanned }}</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $note->status_color }}">
                                        {{ $note->status_text }}
                                    </span>
                                    @if($note->has_discrepancies && $note->status === 'scanned')
                                        <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> ไม่ตรง</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.delivery-notes.show', $note->id) }}" 
                                           class="btn btn-info btn-sm" title="ดูรายละเอียด">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($note->status === 'pending')
                                            <a href="{{ route('admin.delivery-notes.edit', $note->id) }}" 
                                               class="btn btn-warning btn-sm" title="แก้ไข">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        
                                        @if($note->status === 'confirmed')
                                            <a href="{{ route('admin.delivery-notes.scan', $note->id) }}" 
                                               class="btn btn-primary btn-sm" title="สแกน Barcode">
                                                <i class="fas fa-barcode"></i>
                                            </a>
                                        @endif
                                        
                                        @if($note->status === 'scanned')
                                            <a href="{{ route('admin.delivery-notes.review', $note->id) }}" 
                                               class="btn btn-success btn-sm" title="ตรวจสอบและอนุมัติ">
                                                <i class="fas fa-check-double"></i>
                                            </a>
                                        @endif
                                        
                                        <a href="{{ route('admin.delivery-notes.print', $note->id) }}" 
                                           class="btn btn-secondary btn-sm" target="_blank" title="พิมพ์">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        
                                        @if($note->status === 'pending')
                                            <button type="button" class="btn btn-danger btn-sm" 
                                                    onclick="deleteDeliveryNote({{ $note->id }})" title="ลบ">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    {{ $deliveryNotes->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                    <p class="text-muted">ยังไม่มีใบตัดสต็อก</p>
                    <a href="{{ route('admin.delivery-notes.create') }}" class="btn btn-success">
                        <i class="fas fa-plus"></i> สร้างใบตัดสต็อกใหม่
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Delete Form -->
    <form id="delete-form" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@stop

@section('js')
<script>
function deleteDeliveryNote(id) {
    Swal.fire({
        title: 'ยืนยันการลบ?',
        text: "คุณต้องการลบใบตัดสต็อกนี้ใช่หรือไม่?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('delete-form');
            form.action = '/admin/delivery-notes/' + id;
            form.submit();
        }
    });
}
</script>
@stop
