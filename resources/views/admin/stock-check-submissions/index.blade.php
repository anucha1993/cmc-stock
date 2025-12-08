@extends('adminlte::page')

@section('title', 'รายการส่งตรวจสอบ')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>รายการส่งตรวจสอบ</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าแรก</a></li>
                <li class="breadcrumb-item active">รายการส่งตรวจสอบ</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">รายการส่งตรวจสอบสต๊อก</h3>
                        
                        <div class="card-tools">
                            <form method="GET" class="form-inline">
                                <select name="status" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                                    <option value="">ทุกสถานะ</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>รอตรวจสอบ</option>
                                    <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>กำลังตรวจสอบ</option>
                                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>อนุมัติแล้ว</option>
                                    <option value="partially_approved" {{ request('status') === 'partially_approved' ? 'selected' : '' }}>อนุมัติบางส่วน</option>
                                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>ปฏิเสธ</option>
                                </select>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>คลังสินค้า</th>
                                    <th>ส่งโดย</th>
                                    <th>วันที่ส่ง</th>
                                    <th>จำนวนรายการ</th>
                                    <th>ปัญหาที่พบ</th>
                                    <th>สถานะ</th>
                                    <th>การดำเนินการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($submissions as $submission)
                                    <tr>
                                        <td>{{ $submission->id }}</td>
                                        <td>{{ $submission->session?->warehouse?->name ?? 'ไม่ระบุ' }}</td>
                                        <td>{{ $submission->submittedBy?->name ?? 'ไม่ระบุ' }}</td>
                                        <td>{{ $submission->submitted_at?->format('d/m/Y H:i') ?? 'ไม่ระบุ' }}</td>
                                        <td>
                                            <span class="badge badge-info">{{ count($submission->scanned_summary ?? []) }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $issues = count($submission->discrepancy_summary['missing_items'] ?? []) +
                                                         collect($submission->scanned_summary)->where('status', 'not_in_system')->count();
                                            @endphp
                                            @if($issues > 0)
                                                <span class="badge badge-warning">{{ $issues }} ปัญหา</span>
                                            @else
                                                <span class="badge badge-success">ไม่มีปัญหา</span>
                                            @endif
                                        </td>
                                        <td>
                                            @switch($submission->status)
                                                @case('pending')
                                                    <span class="badge badge-warning">รอตรวจสอบ</span>
                                                    @break
                                                @case('under_review')
                                                    <span class="badge badge-info">กำลังตรวจสอบ</span>
                                                    @break
                                                @case('approved')
                                                    <span class="badge badge-success">อนุมัติแล้ว</span>
                                                    @break
                                                @case('partially_approved')
                                                    <span class="badge badge-primary">อนุมัติบางส่วน</span>
                                                    @break
                                                @case('rejected')
                                                    <span class="badge badge-danger">ปฏิเสธ</span>
                                                    @break
                                            @endswitch
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.stock-check-submissions.show', $submission) }}" 
                                                   class="btn btn-sm btn-info" title="ดูรายละเอียด">
                                                    ดู
                                                </a>
                                                
                                                @can('admin')
                                                    @if($submission->canBeReviewed())
                                                        <a href="{{ route('admin.stock-check-submissions.review', $submission) }}" 
                                                           class="btn btn-sm btn-primary" title="ตรวจสอบ">
                                                            ตรวจสอบ
                                                        </a>
                                                    @endif
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            ไม่มีข้อมูลรายการส่งตรวจสอบ
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($submissions->hasPages())
                        <div class="card-footer">
                            {{ $submissions->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .table th {
            white-space: nowrap;
        }
    </style>
@stop