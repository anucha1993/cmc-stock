@extends('adminlte::page')

@section('title', 'รายละเอียดการส่งตรวจสอบ')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>รายละเอียดการส่งตรวจสอบ #{{ $submission->id }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าแรก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.stock-check-submissions.index') }}">รายการส่งตรวจสอบ</a></li>
                <li class="breadcrumb-item active">รายละเอียด</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Status and Actions Row -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h4>
                                    สถานะ: 
                                    @switch($submission->status)
                                        @case('pending')
                                            <span class="badge badge-warning badge-lg">รอตรวจสอบ</span>
                                            @break
                                        @case('under_review')
                                            <span class="badge badge-info badge-lg">กำลังตรวจสอบ</span>
                                            @break
                                        @case('approved')
                                            <span class="badge badge-success badge-lg">อนุมัติแล้ว</span>
                                            @break
                                        @case('partially_approved')
                                            <span class="badge badge-primary badge-lg">อนุมัติบางส่วน</span>
                                            @break
                                        @case('rejected')
                                            <span class="badge badge-danger badge-lg">ปฏิเสธ</span>
                                            @break
                                    @endswitch
                                </h4>
                                <p class="mb-0 text-muted">
                                    @if($submission->session && $submission->session->warehouse)
                                        <strong>คลังสินค้า:</strong> {{ $submission->session->warehouse->name }} |
                                    @endif
                                    <strong>ส่งโดย:</strong> {{ $submission->submittedBy->name ?? 'ไม่ระบุ' }} |
                                    <strong>วันที่ส่ง:</strong> {{ $submission->submitted_at?->format('d/m/Y H:i') ?? 'ไม่ระบุ' }}
                                </p>
                            </div>
                            <div class="col-md-4 text-right">
                                <a href="{{ route('admin.stock-check-submissions.show', $submission) }}" 
                                   class="btn btn-sm btn-outline-primary" title="ดูรายละเอียด">
                                    ดู
                                </a>
                                @can('admin')
                                    @if($submission->canBeReviewed())
                                        <a href="{{ route('admin.stock-check-submissions.review', $submission) }}" 
                                           class="btn btn-sm btn-primary">
                                            ตรวจสอบ
                                        </a>
                                    @elseif($submission->status === 'approved' || $submission->status === 'rejected')
                                        <form method="POST" action="{{ route('admin.stock-check-submissions.request-recheck', $submission) }}" 
                                              style="display: inline;"
                                              onsubmit="return confirm('ต้องการส่งกลับให้ตรวจนับใหม่หรือไม่?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning">
                                                ให้ตรวจนับใหม่
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Summary Card -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">สรุปผลการตรวจนับ</h3>
                    </div>
                    <div class="card-body">
                        @php
                            $scannedItems = collect($submission->scanned_summary ?? []);
                            $missingItems = collect($submission->discrepancy_summary['missing_items'] ?? []);
                            $foundItems = $scannedItems->whereIn('status', ['found', 'duplicate']);
                            $extraItems = $scannedItems->where('status', 'not_in_system');
                        @endphp
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">พบแล้ว</span>
                                        <span class="info-box-number">{{ $foundItems->count() }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i class="fas fa-exclamation"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">ขาดหาย</span>
                                        <span class="info-box-number">{{ $missingItems->count() }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info"><i class="fas fa-plus"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">ไม่มีในระบบ</span>
                                        <span class="info-box-number">{{ $extraItems->count() }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-secondary"><i class="fas fa-copy"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">สแกนซ้ำ</span>
                                        <span class="info-box-number">{{ $scannedItems->where('scanned_count', '>', 1)->count() }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Review History Card -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">ประวัติการตรวจสอบ</h3>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="time-label">
                                <span class="bg-info">{{ $submission->submitted_at?->format('d/m/Y') ?? 'ไม่ระบุ' }}</span>
                            </div>
                            
                            <div>
                                <i class="fas fa-paper-plane bg-primary"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-clock"></i> {{ $submission->submitted_at?->format('H:i') ?? 'ไม่ระบุ' }}</span>
                                    <h3 class="timeline-header">ส่งเพื่อตรวจสอบ</h3>
                                    <div class="timeline-body">
                                        โดย: {{ $submission->submittedBy->name ?? 'ไม่ระบุ' }}
                                    </div>
                                </div>
                            </div>

                            @if($submission->reviewed_at)
                                <div>
                                    <i class="fas fa-eye bg-info"></i>
                                    <div class="timeline-item">
                                        <span class="time"><i class="fas fa-clock"></i> {{ $submission->reviewed_at?->format('H:i') ?? 'ไม่ระบุ' }}</span>
                                        <h3 class="timeline-header">เริ่มตรวจสอบ</h3>
                                        <div class="timeline-body">
                                            โดย: {{ $submission->reviewedBy?->name ?? 'ไม่ระบุ' }}
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($submission->approved_at)
                                <div>
                                    <i class="fas fa-check bg-success"></i>
                                    <div class="timeline-item">
                                        <span class="time"><i class="fas fa-clock"></i> {{ $submission->approved_at?->format('H:i') ?? 'ไม่ระบุ' }}</span>
                                        <h3 class="timeline-header">
                                            @if($submission->status === 'approved')
                                                อนุมัติแล้ว
                                            @elseif($submission->status === 'partially_approved')
                                                อนุมัติบางส่วน
                                            @else
                                                ปฏิเสธ
                                            @endif
                                        </h3>
                                        <div class="timeline-body">
                                            โดย: {{ $submission->approvedBy?->name ?? 'ไม่ระบุ' }}
                                            @if($submission->review_notes)
                                                <br><strong>หมายเหตุ:</strong> {{ $submission->review_notes }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scanned Items Tab -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header p-0 pt-1">
                        <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="found-items-tab" data-toggle="pill" href="#found-items" role="tab">
                                    รายการที่พบ <span class="badge badge-light">{{ $foundItems->count() }}</span>
                                </a>
                            </li>
                            @if($missingItems->count() > 0)
                                <li class="nav-item">
                                    <a class="nav-link" id="missing-items-tab" data-toggle="pill" href="#missing-items" role="tab">
                                        รายการขาดหาย <span class="badge badge-light">{{ $missingItems->count() }}</span>
                                    </a>
                                </li>
                            @endif
                            @if($extraItems->count() > 0)
                                <li class="nav-item">
                                    <a class="nav-link" id="extra-items-tab" data-toggle="pill" href="#extra-items" role="tab">
                                        ไม่มีในระบบ <span class="badge badge-light">{{ $extraItems->count() }}</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="custom-tabs-one-tabContent">
                            <!-- Found Items -->
                            <div class="tab-pane fade show active" id="found-items" role="tabpanel">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Barcode</th>
                                            <th>ชื่อสินค้า</th>
                                            <th>หมวดหมู่</th>
                                            <th>จำนวนที่สแกน</th>
                                            <th>สถานะ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($foundItems as $item)
                                            <tr>
                                                <td><code>{{ $item['barcode'] }}</code></td>
                                                <td>{{ $item['product_name'] ?? 'ไม่ระบุ' }}</td>
                                                <td>{{ $item['category_name'] ?? 'ไม่ระบุ' }}</td>
                                                <td>
                                                    @if($item['scanned_count'] > 1)
                                                        <span class="badge badge-warning">{{ $item['scanned_count'] }}</span>
                                                    @else
                                                        {{ $item['scanned_count'] }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($item['status'] === 'duplicate')
                                                        <span class="badge badge-warning">สแกนซ้ำ</span>
                                                    @else
                                                        <span class="badge badge-success">พบแล้ว</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Missing Items -->
                            @if($missingItems->count() > 0)
                                <div class="tab-pane fade" id="missing-items" role="tabpanel">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th>Barcode</th>
                                                <th>ชื่อสินค้า</th>
                                                <th>หมวดหมู่</th>
                                                <th>ตำแหน่ง</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($missingItems as $item)
                                                <tr>
                                                    <td><code>{{ $item['barcode'] }}</code></td>
                                                    <td>{{ $item['product_name'] ?? 'ไม่ระบุ' }}</td>
                                                    <td>{{ $item['category_name'] ?? 'ไม่ระบุ' }}</td>
                                                    <td>{{ $item['location_code'] ?? 'ไม่ระบุ' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            <!-- Extra Items -->
                            @if($extraItems->count() > 0)
                                <div class="tab-pane fade" id="extra-items" role="tabpanel">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th>Barcode</th>
                                                <th>จำนวนที่สแกน</th>
                                                <th>สถานะ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($extraItems as $item)
                                                <tr>
                                                    <td><code>{{ $item['barcode'] }}</code></td>
                                                    <td>{{ $item['scanned_count'] }}</td>
                                                    <td><span class="badge badge-info">ไม่มีในระบบ</span></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
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
        .badge-lg {
            font-size: 1rem;
            padding: 0.5rem 0.75rem;
        }
        .timeline {
            padding-left: 0;
        }
        .timeline .timeline-item {
            margin-left: 40px;
            margin-bottom: 20px;
        }
        .timeline .fas {
            width: 30px;
            height: 30px;
            font-size: 15px;
            line-height: 30px;
            position: absolute;
            color: #666;
            background: #fff;
            border-radius: 50%;
            text-align: center;
            left: 0;
            top: 0;
        }
    </style>
@stop