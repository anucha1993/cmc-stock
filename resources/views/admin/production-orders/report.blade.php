@extends('adminlte::page')

@section('title', 'รายงานการผลิต')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>รายงานการผลิต</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.production-orders.index') }}">ใบสั่งผลิต</a></li>
                <li class="breadcrumb-item active">รายงาน</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Filter Card -->
    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title">กรองข้อมูล</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.production-orders.report') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>วันที่เริ่ม</label>
                            <input type="date" name="date_from" class="form-control" 
                                   value="{{ $dateFrom }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>วันที่สิ้นสุด</label>
                            <input type="date" name="date_to" class="form-control" 
                                   value="{{ $dateTo }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>สถานะ</label>
                            <select name="status" class="form-control">
                                <option value="">ทั้งหมด</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>รอดำเนินการ</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>อนุมัติแล้ว</option>
                                <option value="in_production" {{ request('status') == 'in_production' ? 'selected' : '' }}>กำลังผลิต</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>เสร็จสิ้น</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ยกเลิก</option>
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

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($stats['total_orders']) }}</h3>
                    <p>ใบสั่งผลิตทั้งหมด</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($stats['completed_orders']) }}</h3>
                    <p>ผลิตเสร็จแล้ว</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($stats['pending_orders']) }}</h3>
                    <p>รอดำเนินการ</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ number_format($stats['in_progress_orders']) }}</h3>
                    <p>กำลังผลิต</p>
                </div>
                <div class="icon">
                    <i class="fas fa-cogs"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format($stats['cancelled_orders']) }}</h3>
                    <p>ยกเลิกแล้ว</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ number_format($stats['total_quantity']) }}</h3>
                    <p>จำนวนผลิตทั้งหมด</p>
                </div>
                <div class="icon">
                    <i class="fas fa-boxes"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">รายละเอียดใบสั่งผลิต ({{ $dateFrom }} - {{ $dateTo }})</h3>
        </div>
        <div class="card-body table-responsive">
            @if($orders->count() > 0)
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th style="width: 10px">#</th>
                            <th>รหัสสั่งผลิต</th>
                            <th>สินค้า</th>
                            <th>คลังเป้าหมาย</th>
                            <th>จำนวนสั่ง</th>
                            <th>จำนวนผลิต</th>
                            <th>สถานะ</th>
                            <th>วันที่ขอ</th>
                            <th>วันที่ครบกำหนด</th>
                            <th>ต้นทุน</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $index => $order)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $order->order_code }}</strong>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $order->product->name ?? 'N/A' }}</strong>
                                        @if($order->product && $order->product->sku)
                                            <br><small class="text-muted">SKU: {{ $order->product->sku }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $order->targetWarehouse->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge badge-info">{{ number_format($order->quantity) }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-success">{{ number_format($order->produced_quantity) }}</span>
                                </td>
                                <td>
                                    @switch($order->status)
                                        @case('pending')
                                            <span class="badge badge-warning">รอดำเนินการ</span>
                                            @break
                                        @case('approved')
                                            <span class="badge badge-info">อนุมัติแล้ว</span>
                                            @break
                                        @case('in_production')
                                            <span class="badge badge-primary">กำลังผลิต</span>
                                            @break
                                        @case('completed')
                                            <span class="badge badge-success">เสร็จสิ้น</span>
                                            @break
                                        @case('cancelled')
                                            <span class="badge badge-danger">ยกเลิก</span>
                                            @break
                                        @default
                                            <span class="badge badge-secondary">{{ $order->status }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    @if($order->requested_at)
                                        {{ $order->requested_at->format('d/m/Y H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($order->due_date)
                                        {{ $order->due_date->format('d/m/Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($order->production_cost > 0)
                                        {{ number_format($order->production_cost, 2) }} ฿
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-light">
                            <th colspan="4">รวม</th>
                            <th>{{ number_format($orders->sum('quantity')) }}</th>
                            <th>{{ number_format($orders->sum('produced_quantity')) }}</th>
                            <th colspan="3"></th>
                            <th>{{ number_format($orders->sum('production_cost'), 2) }} ฿</th>
                        </tr>
                    </tfoot>
                </table>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">ไม่พบข้อมูลใบสั่งผลิตในช่วงวันที่ที่เลือก</p>
                </div>
            @endif
        </div>
    </div>
@stop

@section('css')
    <style>
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
        // Auto-submit form when dates change
        $('input[name="date_from"], input[name="date_to"], select[name="status"]').change(function() {
            $(this).closest('form').submit();
        });
    </script>
@stop