@extends('adminlte::page')

@section('title', 'แดชบอร์ดการผลิต')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>แดชบอร์ดการผลิต</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.production-orders.index') }}">ใบสั่งผลิต</a></li>
                <li class="breadcrumb-item active">แดชบอร์ด</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Navigation Tabs -->
    <div class="card card-primary card-tabs">
        <div class="card-header p-0 pt-1">
            <ul class="nav nav-tabs" id="dashboard-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="overview-tab" data-toggle="pill" href="#overview" role="tab">
                        <i class="fas fa-tachometer-alt"></i> ภาพรวมวันนี้
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="report-tab" data-toggle="pill" href="#report" role="tab">
                        <i class="fas fa-chart-bar"></i> รายงานย้อนหลัง
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="dashboard-tabContent">
                
                <!-- Overview Tab -->
                <div class="tab-pane fade show active" id="overview" role="tabpanel">
                    <!-- Filter for Report -->
                    <div class="row mb-3" id="report-filters" style="display: none;">
                        <div class="col-12">
                            <form method="GET" id="report-form">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>วันที่เริ่ม</label>
                                            <input type="date" name="date_from" class="form-control" 
                                                   value="{{ request('date_from', now()->startOfMonth()->format('Y-m-d')) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>วันที่สิ้นสุด</label>
                                            <input type="date" name="date_to" class="form-control" 
                                                   value="{{ request('date_to', now()->endOfMonth()->format('Y-m-d')) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>สถานะ</label>
                                            <select name="status" class="form-control">
                                                <option value="">ทั้งหมด</option>
                                                <option value="pending">รอดำเนินการ</option>
                                                <option value="approved">อนุมัติแล้ว</option>
                                                <option value="in_production">กำลังผลิต</option>
                                                <option value="completed">เสร็จสิ้น</option>
                                                <option value="cancelled">ยกเลิก</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <div>
                                                <button type="button" id="load-report" class="btn btn-primary">
                                                    <i class="fas fa-search"></i> โหลดรายงาน
                                                </button>
                                                <button type="button" class="btn btn-success" onclick="window.print()">
                                                    <i class="fas fa-print"></i> พิมพ์
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
    <!-- Weekly Statistics -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($weekStats['completed_this_week']) }}</h3>
                    <p>ผลิตเสร็จสิ้นสัปดาห์นี้</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format($weekStats['pending_urgent']) }}</h3>
                    <p>รอดำเนินการ (เร่งด่วน)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($weekStats['avg_progress'], 1) }}%</h3>
                    <p>ความคืบหน้าเฉลี่ย</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($overdueOrders->count()) }}</h3>
                    <p>เลยกำหนดแล้ว</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Today's Orders -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-day mr-1"></i>
                        ใบสั่งผลิตวันนี้
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-primary">{{ $todayOrders->count() }}</span>
                    </div>
                </div>
                <div class="card-body">
                    @if($todayOrders->count() > 0)
                        @foreach($todayOrders->take(5) as $order)
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                <div>
                                    <strong>{{ $order->order_code }}</strong><br>
                                    <small class="text-muted">{{ $order->product->name ?? 'N/A' }}</small><br>
                                    <small>
                                        @switch($order->priority)
                                            @case('urgent')
                                                <span class="badge badge-danger">เร่งด่วน</span>
                                                @break
                                            @case('high')
                                                <span class="badge badge-warning">สูง</span>
                                                @break
                                            @case('normal')
                                                <span class="badge badge-info">ปกติ</span>
                                                @break
                                            @case('low')
                                                <span class="badge badge-secondary">ต่ำ</span>
                                                @break
                                        @endswitch
                                    </small>
                                </div>
                                <div class="text-right">
                                    <small class="text-muted">{{ number_format($order->quantity) }} ชิ้น</small>
                                </div>
                            </div>
                        @endforeach
                        @if($todayOrders->count() > 5)
                            <div class="text-center mt-2">
                                <a href="{{ route('admin.production-orders.index', ['status' => 'pending']) }}" class="btn btn-sm btn-outline-primary">
                                    ดูทั้งหมด ({{ $todayOrders->count() - 5 }} เพิ่มเติม)
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-calendar-check fa-2x mb-2"></i>
                            <p>ไม่มีใบสั่งผลิตที่ต้องเริ่มวันนี้</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- In Progress Orders -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cogs mr-1"></i>
                        กำลังผลิต
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-primary">{{ $inProgressOrders->count() }}</span>
                    </div>
                </div>
                <div class="card-body">
                    @if($inProgressOrders->count() > 0)
                        @foreach($inProgressOrders->take(5) as $order)
                            @php
                                $progress = $order->quantity > 0 ? ($order->produced_quantity * 100 / $order->quantity) : 0;
                            @endphp
                            <div class="border-bottom pb-2 mb-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $order->order_code }}</strong><br>
                                        <small class="text-muted">{{ $order->product->name ?? 'N/A' }}</small>
                                    </div>
                                    <div class="text-right">
                                        <small>{{ number_format($order->produced_quantity) }}/{{ number_format($order->quantity) }}</small>
                                    </div>
                                </div>
                                <div class="progress progress-sm mt-1">
                                    <div class="progress-bar bg-primary" style="width: {{ $progress }}%"></div>
                                </div>
                                <small class="text-muted">{{ number_format($progress, 1) }}%</small>
                            </div>
                        @endforeach
                        @if($inProgressOrders->count() > 5)
                            <div class="text-center mt-2">
                                <a href="{{ route('admin.production-orders.index', ['status' => 'in_production']) }}" class="btn btn-sm btn-outline-primary">
                                    ดูทั้งหมด ({{ $inProgressOrders->count() - 5 }} เพิ่มเติม)
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-pause-circle fa-2x mb-2"></i>
                            <p>ไม่มีใบสั่งผลิตที่กำลังดำเนินการ</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Overdue Orders -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        เลยกำหนดแล้ว
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-danger">{{ $overdueOrders->count() }}</span>
                    </div>
                </div>
                <div class="card-body">
                    @if($overdueOrders->count() > 0)
                        @foreach($overdueOrders->take(5) as $order)
                            @php
                                $daysOverdue = now()->diffInDays($order->due_date);
                            @endphp
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                <div>
                                    <strong>{{ $order->order_code }}</strong><br>
                                    <small class="text-muted">{{ $order->product->name ?? 'N/A' }}</small><br>
                                    <small class="text-danger">
                                        <i class="fas fa-clock"></i> เลย {{ $daysOverdue }} วัน
                                    </small>
                                </div>
                                <div class="text-right">
                                    @switch($order->status)
                                        @case('pending')
                                            <span class="badge badge-warning">รอดำเนินการ</span>
                                            @break
                                        @case('in_production')
                                            <span class="badge badge-primary">กำลังผลิต</span>
                                            @break
                                    @endswitch
                                </div>
                            </div>
                        @endforeach
                        @if($overdueOrders->count() > 5)
                            <div class="text-center mt-2">
                                <a href="{{ route('admin.production-orders.index', ['overdue' => '1']) }}" class="btn btn-sm btn-outline-danger">
                                    ดูทั้งหมด ({{ $overdueOrders->count() - 5 }} เพิ่มเติม)
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-thumbs-up fa-2x mb-2"></i>
                            <p>ไม่มีใบสั่งผลิตที่เลยกำหนด</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt mr-1"></i>
                        การดำเนินการด่วน
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('admin.production-orders.create') }}" class="btn btn-primary btn-block">
                                <i class="fas fa-plus"></i> สร้างใบสั่งผลิตใหม่
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.production-orders.index', ['status' => 'pending']) }}" class="btn btn-warning btn-block">
                                <i class="fas fa-clock"></i> ใบสั่งรอดำเนินการ
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.production-orders.index', ['status' => 'in_production']) }}" class="btn btn-info btn-block">
                                <i class="fas fa-cogs"></i> กำลังผลิต
                            </a>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-success btn-block" onclick="switchToReportTab()">
                                <i class="fas fa-chart-bar"></i> ดูรายงานย้อนหลัง
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
                </div>

                <!-- Report Tab -->
                <div class="tab-pane fade" id="report" role="tabpanel">
                    <div id="report-content">
                        <div class="text-center py-5">
                            <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                            <p class="text-muted">เลือกช่วงเวลาและกดโหลดรายงานเพื่อดูข้อมูล</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .small-box .inner h3 {
            font-size: 2.2rem;
        }
        .progress-sm {
            height: 0.5rem;
        }
        .card-body {
            max-height: 400px;
            overflow-y: auto;
        }
        @media print {
            .card-header, .content-header, .main-sidebar, .main-header, .main-footer {
                display: none !important;
            }
            .content-wrapper {
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
@stop

@section('js')
    <script>
        function switchToReportTab() {
            $('#report-tab').tab('show');
            $('#report-filters').show();
        }

        $('#report-tab').on('shown.bs.tab', function() {
            $('#report-filters').show();
        });

        $('#overview-tab').on('shown.bs.tab', function() {
            $('#report-filters').hide();
        });

        $('#load-report').click(function() {
            const formData = new FormData($('#report-form')[0]);
            const params = new URLSearchParams(formData);
            
            // Show loading
            $('#report-content').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x"></i><p>กำลังโหลดข้อมูล...</p></div>');
            
            // Load report data
            fetch('{{ route("admin.production-orders.report") }}?' + params.toString())
                .then(response => response.text())
                .then(data => {
                    // Extract only the report content from the response
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(data, 'text/html');
                    const reportCards = doc.querySelectorAll('.row, .card');
                    
                    let reportHtml = '';
                    reportCards.forEach(card => {
                        if (!card.querySelector('.breadcrumb') && 
                            !card.querySelector('#dashboard-tabs') &&
                            !card.textContent.includes('กรองข้อมูล')) {
                            reportHtml += card.outerHTML;
                        }
                    });
                    
                    $('#report-content').html(reportHtml || '<div class="alert alert-info">ไม่พบข้อมูลในช่วงเวลาที่เลือก</div>');
                })
                .catch(error => {
                    $('#report-content').html('<div class="alert alert-danger">เกิดข้อผิดพลาดในการโหลดข้อมูล</div>');
                });
        });

        // Auto refresh every 5 minutes (only for overview tab)
        setInterval(function() {
            if ($('#overview').hasClass('show')) {
                location.reload();
            }
        }, 300000);
    </script>
@stop