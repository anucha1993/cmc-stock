@extends('adminlte::page')

@section('title', 'รายงานการโอนย้าย')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>รายงานการโอนย้ายสินค้า</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.transfers.index') }}">โอนย้ายสินค้า</a></li>
                <li class="breadcrumb-item active">รายงาน</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    {{-- Stats --}}
    <div class="row">
        <div class="col-lg-2 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total_transfers'] }}</h3>
                    <p>ทั้งหมด</p>
                </div>
                <div class="icon"><i class="fas fa-exchange-alt"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['pending_transfers'] }}</h3>
                    <p>รอดำเนินการ</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $stats['in_transit_transfers'] }}</h3>
                    <p>กำลังขนส่ง</p>
                </div>
                <div class="icon"><i class="fas fa-truck"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['completed_transfers'] }}</h3>
                    <p>สำเร็จ</p>
                </div>
                <div class="icon"><i class="fas fa-check"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['cancelled_transfers'] }}</h3>
                    <p>ยกเลิก</p>
                </div>
                <div class="icon"><i class="fas fa-times"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-olive">
                <div class="inner">
                    <h3>{{ number_format($stats['total_quantity']) }}</h3>
                    <p>รวม (ชิ้น)</p>
                </div>
                <div class="icon"><i class="fas fa-cubes"></i></div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title">กรองข้อมูล</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.transfers.report') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>วันที่เริ่มต้น</label>
                            <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>วันที่สิ้นสุด</label>
                            <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>คลังสินค้า</label>
                            <select name="warehouse" class="form-control">
                                <option value="">ทั้งหมด</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ request('warehouse') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-info btn-block"><i class="fas fa-search"></i> กรอง</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">รายการโอนย้าย ({{ $transfers->count() }} รายการ) | {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>รหัส</th>
                            <th>สินค้า</th>
                            <th>จากคลัง</th>
                            <th>ไปคลัง</th>
                            <th class="text-center">จำนวน</th>
                            <th class="text-center">สถานะ</th>
                            <th>วันที่</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transfers as $i => $transfer)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                <a href="{{ route('admin.transfers.show', $transfer) }}">{{ $transfer->transfer_code }}</a>
                            </td>
                            <td>{{ $transfer->product->full_name ?? '-' }}</td>
                            <td>{{ $transfer->fromWarehouse->name ?? '-' }}</td>
                            <td>{{ $transfer->toWarehouse->name ?? '-' }}</td>
                            <td class="text-center"><strong>{{ $transfer->quantity }}</strong></td>
                            <td class="text-center">
                                @php
                                    $color = match($transfer->status) {
                                        'pending' => 'warning', 'in_transit' => 'info',
                                        'completed' => 'success', 'cancelled' => 'danger',
                                        default => 'secondary'
                                    };
                                    $text = match($transfer->status) {
                                        'pending' => 'รอดำเนินการ', 'in_transit' => 'กำลังขนส่ง',
                                        'completed' => 'สำเร็จ', 'cancelled' => 'ยกเลิก',
                                        default => $transfer->status
                                    };
                                @endphp
                                <span class="badge badge-{{ $color }}">{{ $text }}</span>
                            </td>
                            <td><small>{{ $transfer->created_at->format('d/m/Y H:i') }}</small></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">ไม่พบข้อมูลในช่วงเวลาที่เลือก</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop
