@extends('adminlte::page')

@section('title', 'รายละเอียดการตรวจสต๊อก - ' . $stockCheck->session_code)

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>รายละเอียดการตรวจสต๊อก</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.stock-checks.index') }}">ตรวจนับสต๊อก</a></li>
                <li class="breadcrumb-item active">{{ $stockCheck->session_code }}</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Session Info -->
    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary">
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
                            <strong>คลัง:</strong> {{ $stockCheck->warehouse->name }}<br>
                            <strong>หมวดหมู่:</strong> {{ $stockCheck->category->name ?? 'ทั้งหมด' }}<br>
                            <strong>เริ่มเมื่อ:</strong> {{ $stockCheck->started_at->format('d/m/Y H:i') }}<br>
                        </div>
                        <div class="col-sm-6">
                            <strong>ผู้สร้าง:</strong> {{ $stockCheck->creator->name ?? 'N/A' }}<br>
                            @if($stockCheck->completed_at)
                                <strong>เสร็จเมื่อ:</strong> {{ $stockCheck->completed_at->format('d/m/Y H:i') }}<br>
                                <strong>ผู้ปิด:</strong> {{ $stockCheck->completedBy->name ?? 'N/A' }}<br>
                            @endif
                            @if($stockCheck->description)
                                <strong>คำอธิบาย:</strong><br>
                                <div class="text-muted">{{ $stockCheck->description }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">การดำเนินการ</h3>
                </div>
                <div class="card-body text-center">
                    @if($stockCheck->status === 'active')
                        <a href="{{ route('admin.stock-checks.scan', $stockCheck) }}" class="btn btn-success btn-block">
                            <i class="fas fa-barcode"></i> เริ่มสแกน
                        </a>
                        
                        @if($stats['total_scanned'] > 0)
                            @can('approve')
                            <form action="{{ route('admin.stock-checks.complete', $stockCheck) }}" 
                                  method="POST" 
                                  style="display: inline;"
                                  onsubmit="return confirm('ต้องการปิด Session นี้หรือไม่?')">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-block mt-2">
                                    <i class="fas fa-stop"></i> ปิด Session
                                </button>
                            </form>
                            @endcan
                        @endif
                    @endif

                    @if($stockCheck->status === 'completed')
                        <a href="{{ route('admin.stock-checks.report', $stockCheck) }}" class="btn btn-info btn-block">
                            <i class="fas fa-chart-bar"></i> ดูรายงานเปรียบเทียบ
                        </a>
                        
                        @can('stock-operations')
                        <form action="{{ route('admin.stock-checks.generate-adjustment', $stockCheck) }}" 
                              method="POST" 
                              style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-block mt-2">
                                <i class="fas fa-edit"></i> สร้างการปรับปรุงสต๊อก
                            </button>
                        </form>
                        @endcan
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ number_format($stats['total_scanned']) }}</h3>
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
                    <h3>{{ number_format($stats['found_in_system']) }}</h3>
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
                    <h3>{{ number_format($stats['not_in_system']) }}</h3>
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
                    <h3>{{ number_format($stats['duplicates']) }}</h3>
                    <p>สแกนซ้ำ</p>
                </div>
                <div class="icon">
                    <i class="fas fa-copy"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Scanned Items -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">รายการที่สแกนแล้ว</h3>
        </div>
        <div class="card-body table-responsive">
            @if($stockCheck->checkItems->count() > 0)
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th style="width: 10px">#</th>
                            <th>Barcode</th>
                            <th>สินค้า</th>
                            <th>สถานะ</th>
                            <th>จำนวนครั้งที่สแกน</th>
                            <th>ตำแหน่งที่พบ</th>
                            <th>สแกนเมื่อ</th>
                            <th>ผู้สแกน</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stockCheck->checkItems->sortByDesc('last_scanned_at') as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <code>{{ $item->barcode }}</code>
                                </td>
                                <td>
                                    @if($item->product)
                                        <strong>{{ $item->product->full_name }}</strong>
                                        @if($item->product->sku)
                                            <br><small class="text-muted">SKU: {{ $item->product->sku }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">ไม่พบในระบบ</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $item->status_color }}">
                                        {{ $item->status_text }}
                                    </span>
                                </td>
                                <td>
                                    {{ $item->scanned_count }}
                                    @if($item->scanned_count > 1)
                                        <i class="fas fa-exclamation-circle text-warning ml-1" title="สแกนซ้ำ"></i>
                                    @endif
                                </td>
                                <td>{{ $item->location_found ?? '-' }}</td>
                                <td>
                                    {{ $item->last_scanned_at->format('d/m/Y H:i:s') }}
                                    @if($item->first_scanned_at != $item->last_scanned_at)
                                        <br><small class="text-muted">ครั้งแรก: {{ $item->first_scanned_at->format('H:i:s') }}</small>
                                    @endif
                                </td>
                                <td>{{ $item->scannedBy->name }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-barcode fa-3x text-muted mb-3"></i>
                    <p class="text-muted">ยังไม่มีการสแกน</p>
                    @if($stockCheck->status === 'active')
                        <a href="{{ route('admin.stock-checks.scan', $stockCheck) }}" class="btn btn-success">
                            <i class="fas fa-barcode"></i> เริ่มสแกน
                        </a>
                    @endif
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

@section('js')
    <script>
        // Auto refresh if session is active
        @if($stockCheck->status === 'active')
        setInterval(function() {
            location.reload();
        }, 30000); // Refresh every 30 seconds
        @endif
    </script>
@stop