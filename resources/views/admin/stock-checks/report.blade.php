@extends('adminlte::page')

@section('title', 'รายงานการตรวจสต๊อก - ' . $stockCheck->session_code)

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>รายงานการตรวจสต๊อก</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.stock-checks.index') }}">ตรวจนับสต๊อก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.stock-checks.show', $stockCheck) }}">{{ $stockCheck->session_code }}</a></li>
                <li class="breadcrumb-item active">รายงาน</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">{{ $stockCheck->title }}</h3>
                    <div class="card-tools">
                        @switch($stockCheck->status)
                            @case('active')
                                <span class="badge badge-success badge-lg">กำลังดำเนินการ</span>
                                @break
                            @case('completed')
                                <span class="badge badge-primary badge-lg">เสร็จสิ้น</span>
                                @break
                            @case('cancelled')
                                <span class="badge badge-danger badge-lg">ยกเลิก</span>
                                @break
                        @endswitch
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <strong>รหัส Session:</strong> {{ $stockCheck->session_code }}<br>
                            <strong>คลัง:</strong> {{ $stockCheck->warehouse->name ?? 'N/A' }}<br>
                            <strong>หมวดหมู่:</strong> {{ $stockCheck->category->name ?? 'ทั้งหมด' }}<br>
                        </div>
                        <div class="col-sm-6">
                            <strong>เริ่มเมื่อ:</strong> {{ $stockCheck->started_at?->format('d/m/Y H:i') ?? '-' }}<br>
                            <strong>เสร็จเมื่อ:</strong> {{ $stockCheck->completed_at?->format('d/m/Y H:i') ?? '-' }}<br>
                        </div>
                    </div>
                    @if($stockCheck->description)
                        <hr>
                        <strong>คำอธิบาย:</strong>
                        <div class="text-muted">{{ $stockCheck->description }}</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">การดำเนินการ</h3>
                </div>
                <div class="card-body text-center">
                    <a href="{{ route('admin.stock-checks.show', $stockCheck) }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-arrow-left"></i> กลับไปหน้ารายละเอียด
                    </a>
                    @if($stockCheck->status === 'active')
                        <a href="{{ route('admin.stock-checks.scan', $stockCheck) }}" class="btn btn-success btn-block mt-2">
                            <i class="fas fa-barcode"></i> ไปหน้าสแกน
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ number_format($summary['total_scanned'] ?? 0) }}</h3>
                    <p>ทั้งหมดที่สแกน</p>
                </div>
                <div class="icon">
                    <i class="fas fa-barcode"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($summary['found_in_system'] ?? 0) }}</h3>
                    <p>พบในระบบ</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($summary['not_in_system'] ?? 0) }}</h3>
                    <p>ไม่มีในระบบ</p>
                </div>
                <div class="icon">
                    <i class="fas fa-question-circle"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($summary['system_vs_actual']['missing_items'] ?? 0) }}</h3>
                    <p>ขาดจากระบบ</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-secondary"><i class="fas fa-balance-scale"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">อัตราตรงกัน</span>
                    <span class="info-box-number">
                        {{ $summary['system_vs_actual']['match_percentage'] ?? 0 }}%
                    </span>
                    <span class="progress-description">
                        คาดหวัง {{ number_format($summary['system_vs_actual']['expected_count'] ?? 0) }} / พบจริง {{ number_format($summary['system_vs_actual']['actual_count'] ?? 0) }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-copy"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">สแกนซ้ำ</span>
                    <span class="info-box-number">
                        {{ number_format($summary['duplicate_scans'] ?? $summary['duplicates'] ?? 0) }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-primary"><i class="fas fa-mouse-pointer"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">จำนวนครั้งที่สแกนทั้งหมด</span>
                    <span class="info-box-number">
                        {{ number_format($summary['total_scan_attempts'] ?? 0) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Missing Items -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">รายการที่ขาด (มีในระบบแต่ไม่พบ)</h3>
        </div>
        <div class="card-body table-responsive">
            @if($missingItems->count() > 0)
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th style="width: 10px">#</th>
                            <th>Barcode</th>
                            <th>สินค้า</th>
                            <th>หมวดหมู่</th>
                            <th>จำนวนที่คาดหวัง</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($missingItems as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><code>{{ $item->barcode }}</code></td>
                                <td>{{ $item->product_name ?? 'N/A' }}</td>
                                <td>{{ $item->category_name ?? '-' }}</td>
                                <td>{{ number_format($item->quantity ?? 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <div>ไม่พบรายการขาด</div>
                </div>
            @endif
        </div>
    </div>

    <!-- Extra Items -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">รายการเกิน (สแกนพบแต่ไม่มีในระบบ)</h3>
        </div>
        <div class="card-body table-responsive">
            @if($extraItems->count() > 0)
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th style="width: 10px">#</th>
                            <th>Barcode</th>
                            <th>จำนวนครั้งที่สแกน</th>
                            <th>ตำแหน่งที่พบ</th>
                            <th>สแกนล่าสุด</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($extraItems as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><code>{{ $item->barcode }}</code></td>
                                <td>{{ $item->scanned_count }}</td>
                                <td>{{ $item->location_found ?? '-' }}</td>
                                <td>{{ $item->last_scanned_at?->format('d/m/Y H:i:s') ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <div>ไม่พบรายการเกิน</div>
                </div>
            @endif
        </div>
    </div>

    <!-- Duplicate Items -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">รายการสแกนซ้ำ</h3>
        </div>
        <div class="card-body table-responsive">
            @if($duplicateItems->count() > 0)
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th style="width: 10px">#</th>
                            <th>Barcode</th>
                            <th>สินค้า</th>
                            <th>จำนวนครั้งที่สแกน</th>
                            <th>สแกนล่าสุด</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($duplicateItems as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><code>{{ $item->barcode }}</code></td>
                                <td>{{ $item->product->full_name ?? 'N/A' }}</td>
                                <td>{{ $item->scanned_count }}</td>
                                <td>{{ $item->last_scanned_at?->format('d/m/Y H:i:s') ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <div>ไม่พบรายการสแกนซ้ำ</div>
                </div>
            @endif
        </div>
    </div>
@stop

@section('css')
    <style>
        .badge-lg {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }

        .small-box .inner h3 {
            font-size: 2.2rem;
        }

        code {
            font-size: 0.9rem;
            color: #e83e8c;
        }
    </style>
@stop
