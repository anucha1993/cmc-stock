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

@section('css')
<style>
    /* Mobile card list - ซ่อน table แสดง card แทนบนมือถือ */
    .mobile-cards { display: none; }

    @media (max-width: 767.98px) {
        .desktop-table { display: none !important; }
        .mobile-cards { display: block; }

        .mobile-note-card {
            border-left: 4px solid #007bff;
            margin-bottom: .75rem;
        }
        .mobile-note-card .card-body {
            padding: .75rem;
        }
        .mobile-note-card .note-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: .5rem;
        }
        .mobile-note-card .note-meta {
            font-size: .85rem;
            color: #6c757d;
        }
        .mobile-note-card .note-actions {
            display: flex;
            flex-wrap: wrap;
            gap: .375rem;
            margin-top: .5rem;
            padding-top: .5rem;
            border-top: 1px solid #eee;
        }
        .mobile-note-card .note-actions .btn {
            flex: 1 1 auto;
            min-width: 0;
            font-size: .8rem;
            padding: .35rem .5rem;
        }

        /* Filter form mobile */
        .filter-form .col-md-3,
        .filter-form .col-md-2 {
            margin-bottom: .25rem;
        }
    }
</style>
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
            <h3 class="card-title"><i class="fas fa-filter"></i> กรองข้อมูล</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.delivery-notes.index') }}" class="filter-form">
                <div class="row">
                    <div class="col-12 col-md-3">
                        <div class="form-group">
                            <label>ค้นหา</label>
                            <input type="text" class="form-control" name="search" value="{{ request('search') }}" 
                                placeholder="เลขที่, ลูกค้า, เลขที่อ้างอิง">
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
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
                    <div class="col-6 col-md-2">
                        <div class="form-group">
                            <label>วันที่เริ่ม</label>
                            <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="form-group">
                            <label>วันที่สิ้นสุด</label>
                            <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="form-group">
                            <label class="d-none d-md-block">&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> <span class="d-inline d-md-none">ค้นหา</span>
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
            <h3 class="card-title">รายการใบตัดสต็อก</h3>
            <div class="card-tools">
                @can('create-edit')
                <a href="{{ route('admin.delivery-notes.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus"></i> <span class="d-none d-sm-inline">สร้างใบตัดสต็อกใหม่</span>
                </a>
                @endcan
            </div>
        </div>
        
        <div class="card-body p-md-3 p-2">
            @if($deliveryNotes->count() > 0)

                {{-- === Desktop Table (md ขึ้นไป) === --}}
                <div class="table-responsive desktop-table">
                    <table class="table table-bordered table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>เลขที่ใบตัดสต็อก</th>
                                <th>วันที่จัดส่ง</th>
                                <th>ลูกค้า</th>
                                <th>เลขที่อ้างอิง</th>
                                <th class="text-center">รายการ</th>
                                <th class="text-center">สถานะ</th>
                                <th class="text-center">การดำเนินการ</th>
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
                                            @can('create-edit')
                                            <a href="{{ route('admin.delivery-notes.edit', $note->id) }}" 
                                               class="btn btn-warning btn-sm" title="แก้ไข">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcan
                                        @endif
                                        @if($note->status === 'scanned')
                                            @can('approve')
                                            <a href="{{ route('admin.delivery-notes.review', $note->id) }}" 
                                               class="btn btn-success btn-sm" title="ตรวจสอบและอนุมัติ">
                                                <i class="fas fa-check-double"></i>
                                            </a>
                                            @endcan
                                        @endif
                                        @if($note->status !== 'completed')
                                        <button type="button" class="btn btn-outline-primary btn-sm btn-copy-link"
                                                data-url="{{ route('admin.delivery-notes.share-link', $note->id) }}"
                                                title="Copy URL สแกน">
                                            <i class="fas fa-link"></i>
                                        </button>
                                        @endif
                                        <a href="{{ route('admin.delivery-notes.print', $note->id) }}" 
                                           class="btn btn-secondary btn-sm" target="_blank" title="พิมพ์">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        @if($note->status === 'pending')
                                            @can('delete')
                                            <button type="button" class="btn btn-danger btn-sm" 
                                                    onclick="deleteDeliveryNote({{ $note->id }})" title="ลบ">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- === Mobile Card List (< md) === --}}
                <div class="mobile-cards">
                    @foreach($deliveryNotes as $note)
                    <div class="card mobile-note-card shadow-sm">
                        <div class="card-body">
                            <div class="note-header">
                                <div>
                                    <strong>{{ $note->delivery_number }}</strong>
                                    <span class="badge badge-{{ $note->status_color }} ml-1">{{ $note->status_text }}</span>
                                    @if($note->has_discrepancies && $note->status === 'scanned')
                                        <span class="text-danger ml-1"><i class="fas fa-exclamation-triangle"></i></span>
                                    @endif
                                </div>
                                <span class="badge badge-info">{{ $note->total_items }} รายการ</span>
                            </div>

                            <div class="note-meta">
                                <div><i class="fas fa-user"></i> {{ $note->customer_name }}
                                    @if($note->customer_phone)
                                        &middot; <i class="fas fa-phone"></i> {{ $note->customer_phone }}
                                    @endif
                                </div>
                                <div>
                                    <i class="fas fa-calendar"></i> จัดส่ง {{ $note->delivery_date->format('d/m/Y') }}
                                    &middot; สร้าง {{ $note->created_at->format('d/m/Y H:i') }}
                                </div>
                                @if($note->sales_order_number || $note->quotation_number)
                                <div>
                                    @if($note->sales_order_number)
                                        <i class="fas fa-file-alt"></i> {{ $note->sales_order_number }}
                                    @endif
                                    @if($note->quotation_number)
                                        &middot; <i class="fas fa-file-invoice"></i> {{ $note->quotation_number }}
                                    @endif
                                </div>
                                @endif
                                @if(($note->status === 'scanned' || $note->status === 'completed') && $note->total_scanned)
                                <div class="text-success"><i class="fas fa-barcode"></i> สแกนแล้ว {{ $note->total_scanned }}/{{ $note->total_items }}</div>
                                @endif
                            </div>

                            <div class="note-actions">
                                <a href="{{ route('admin.delivery-notes.show', $note->id) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> ดู
                                </a>
                                @if($note->status === 'pending')
                                    @can('create-edit')
                                    <a href="{{ route('admin.delivery-notes.edit', $note->id) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> แก้ไข
                                    </a>
                                    @endcan
                                @endif
                                @if($note->status === 'scanned')
                                    @can('approve')
                                    <a href="{{ route('admin.delivery-notes.review', $note->id) }}" class="btn btn-success btn-sm">
                                        <i class="fas fa-check-double"></i> อนุมัติ
                                    </a>
                                    @endcan
                                @endif
                                @if($note->status !== 'completed')
                                <button type="button" class="btn btn-outline-primary btn-sm btn-copy-link"
                                        data-url="{{ route('admin.delivery-notes.share-link', $note->id) }}">
                                    <i class="fas fa-link"></i> Copy URL
                                </button>
                                @endif
                                <a href="{{ route('admin.delivery-notes.print', $note->id) }}" class="btn btn-secondary btn-sm" target="_blank">
                                    <i class="fas fa-print"></i>
                                </a>
                                @if($note->status === 'pending')
                                    @can('delete')
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteDeliveryNote({{ $note->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="mt-3">
                    {{ $deliveryNotes->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                    <p class="text-muted">ยังไม่มีใบตัดสต็อก</p>
                    @can('create-edit')
                    <a href="{{ route('admin.delivery-notes.create') }}" class="btn btn-success">
                        <i class="fas fa-plus"></i> สร้างใบตัดสต็อกใหม่
                    </a>
                    @endcan
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

// Copy Share Link
$(document).on('click', '.btn-copy-link', function() {
    const btn = $(this);
    const originalHtml = btn.html();
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

    $.ajax({
        url: btn.data('url'),
        method: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        success: function(res) {
            if (res.success) {
                navigator.clipboard.writeText(res.url).then(function() {
                    btn.html('<i class="fas fa-check text-success"></i>');
                    Swal.fire({
                        icon: 'success',
                        title: 'คัดลอก URL แล้ว!',
                        html: `<div class="text-start">
                            <p class="mb-1">ส่งลิงก์นี้ให้คนขับ/ผู้สแกน เปิดในมือถือแล้วยิง Barcode ได้เลย</p>
                            <div class="alert alert-info py-2 px-3" style="font-size:.85rem;word-break:break-all">${res.url}</div>
                            <p class="mb-0 text-muted" style="font-size:.85rem"><i class="fas fa-clock"></i> หมดอายุ: ${res.expires_at} (3 ชม.)</p>
                        </div>`,
                        confirmButtonText: 'ตกลง',
                    });
                }).catch(function() {
                    prompt('คัดลอก URL นี้:', res.url);
                });
            } else {
                Swal.fire('ไม่สำเร็จ', res.message, 'error');
            }
        },
        error: function() {
            Swal.fire('ผิดพลาด', 'ไม่สามารถสร้างลิงก์ได้', 'error');
        },
        complete: function() {
            setTimeout(() => btn.prop('disabled', false).html(originalHtml), 2000);
        }
    });
});
</script>
@stop
