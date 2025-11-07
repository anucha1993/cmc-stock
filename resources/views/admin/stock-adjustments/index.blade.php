@extends('adminlte::page')

@section('title', 'คำขอปรับปรุงสต็อก')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>คำขอปรับปรุงสต็อก</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item active">คำขอปรับปรุงสต็อก</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Stats Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['pending'] ?? 0 }}</h3>
                    <p>รอการอนุมัติ</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['approved'] ?? 0 }}</h3>
                    <p>อนุมัติแล้ว</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['completed'] ?? 0 }}</h3>
                    <p>ดำเนินการเสร็จสิ้น</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-double"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['rejected'] ?? 0 }}</h3>
                    <p>ปฏิเสธ</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">กรองข้อมูล</h3>
            <div class="card-tools">
                <a href="{{ route('admin.stock-adjustments.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> สร้างคำขอใหม่
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.stock-adjustments.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">ค้นหา</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="search" 
                                   name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="เลขที่คำขอ, ชื่อสินค้า...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">สถานะ</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">ทั้งหมด</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>รอการอนุมัติ</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>อนุมัติแล้ว</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>ปฏิเสธ</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>ดำเนินการเสร็จสิ้น</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="type">ประเภท</label>
                            <select class="form-control" id="type" name="type">
                                <option value="">ทั้งหมด</option>
                                <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>เพิ่มสต็อก</option>
                                <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>ลดสต็อก</option>
                                <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>ปรับปรุงสต็อก</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> ค้นหา
                                </button>
                                <a href="{{ route('admin.stock-adjustments.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> ล้าง
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Requests List -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">รายการคำขอปรับปรุงสต็อก</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>เลขที่คำขอ</th>
                        <th>วันที่</th>
                        <th>ประเภท</th>
                        <th>สินค้า</th>
                        <th>คลัง</th>
                        <th>จำนวน</th>
                        <th>สถานะ</th>
                        <th>ผู้ขอ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $request)
                        <tr>
                            <td>
                                <a href="{{ route('admin.stock-adjustments.show', $request) }}">
                                    <strong>{{ $request->request_number }}</strong>
                                </a>
                            </td>
                            <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="badge badge-{{ $request->type === 'in' ? 'success' : ($request->type === 'out' ? 'danger' : 'warning') }}">
                                    {{ $request->type_text }}
                                </span>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $request->product->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $request->product->sku }}</small>
                                </div>
                            </td>
                            <td>{{ $request->warehouse->name }}</td>
                            <td>
                                <div>
                                    @if($request->type === 'adjustment')
                                        {{ number_format($request->current_quantity) }} → {{ number_format($request->requested_quantity) }}
                                    @else
                                        {{ $request->type === 'in' ? '+' : '-' }}{{ number_format($request->requested_quantity) }}
                                    @endif
                                    <span class="text-muted">{{ $request->product->unit }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-{{ $request->status_color }}">
                                    {{ $request->status_text }}
                                </span>
                            </td>
                            <td>{{ $request->requestedBy->name }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.stock-adjustments.show', $request) }}" 
                                       class="btn btn-info btn-sm" title="ดูรายละเอียด">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($request->status === 'pending')
                                        <button type="button" class="btn btn-success btn-sm" 
                                                onclick="approveRequest({{ $request->id }})" title="อนุมัติ">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" 
                                                onclick="rejectRequest({{ $request->id }})" title="ปฏิเสธ">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @elseif($request->status === 'approved')
                                        <button type="button" class="btn btn-warning btn-sm" 
                                                onclick="processRequest({{ $request->id }})" title="ดำเนินการ">
                                            <i class="fas fa-cog"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">ไม่พบข้อมูลคำขอปรับปรุงสต็อก</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($requests->hasPages())
            <div class="card-footer">
                {{ $requests->appends(request()->all())->links() }}
            </div>
        @endif
    </div>
@stop

@section('js')
    <script>
        function approveRequest(requestId) {
            Swal.fire({
                title: 'อนุมัติคำขอ',
                input: 'textarea',
                inputLabel: 'หมายเหตุ (ไม่บังคับ)',
                inputPlaceholder: 'ระบุหมายเหตุการอนุมัติ...',
                showCancelButton: true,
                confirmButtonText: 'อนุมัติ',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#28a745'
            }).then((result) => {
                if (result.isConfirmed) {
                    submitAction('approve', requestId, result.value);
                }
            });
        }

        function rejectRequest(requestId) {
            Swal.fire({
                title: 'ปฏิเสธคำขอ',
                input: 'textarea',
                inputLabel: 'เหตุผลในการปฏิเสธ (บังคับ)',
                inputPlaceholder: 'ระบุเหตุผลในการปฏิเสธ...',
                inputValidator: (value) => {
                    if (!value) {
                        return 'กรุณาระบุเหตุผลในการปฏิเสธ';
                    }
                },
                showCancelButton: true,
                confirmButtonText: 'ปฏิเสธ',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#dc3545'
            }).then((result) => {
                if (result.isConfirmed) {
                    submitAction('reject', requestId, result.value);
                }
            });
        }

        function processRequest(requestId) {
            Swal.fire({
                title: 'ดำเนินการคำขอ',
                text: 'คุณต้องการดำเนินการปรับปรุงสต็อกตามคำขอนี้หรือไม่?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'ดำเนินการ',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#ffc107'
            }).then((result) => {
                if (result.isConfirmed) {
                    submitAction('process', requestId);
                }
            });
        }

        function submitAction(action, requestId, notes = '') {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/stock-adjustments/${requestId}/${action}`;
            
            // CSRF Token
            const csrfField = document.createElement('input');
            csrfField.type = 'hidden';
            csrfField.name = '_token';
            csrfField.value = '{{ csrf_token() }}';
            form.appendChild(csrfField);
            
            // Notes
            if (notes) {
                const notesField = document.createElement('input');
                notesField.type = 'hidden';
                notesField.name = action === 'approve' ? 'approval_notes' : 'approval_notes';
                notesField.value = notes;
                form.appendChild(notesField);
            }
            
            document.body.appendChild(form);
            form.submit();
        }
    </script>
@stop