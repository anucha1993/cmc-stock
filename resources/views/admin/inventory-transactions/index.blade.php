@extends('adminlte::page')

@section('title', 'ประวัติการเคลื่อนไหวสต๊อก')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>ประวัติการเคลื่อนไหวสต๊อก</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item active">ประวัติการเคลื่อนไหวสต๊อก</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Filter Card -->
    <div class="card card-outline card-info collapsed-card">
        <div class="card-header">
            <h3 class="card-title">ค้นหาและกรองข้อมูล</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.inventory-transactions.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>ค้นหา</label>
                            <input type="text" name="search" class="form-control" 
                                   value="{{ request('search') }}" 
                                   placeholder="ชื่อสินค้า, SKU, หมายเหตุ">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>ประเภท</label>
                            <select name="transaction_type" class="form-control">
                                <option value="">ทั้งหมด</option>
                                <option value="in" {{ request('transaction_type') == 'in' ? 'selected' : '' }}>เข้า</option>
                                <option value="out" {{ request('transaction_type') == 'out' ? 'selected' : '' }}>ออก</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>คลัง</label>
                            <select name="warehouse_id" class="form-control">
                                <option value="">ทั้งหมด</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" 
                                            {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>วันที่เริ่มต้น</label>
                            <input type="date" name="start_date" class="form-control" 
                                   value="{{ request('start_date') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>วันที่สิ้นสุด</label>
                            <input type="date" name="end_date" class="form-control" 
                                   value="{{ request('end_date') }}">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label>&nbsp;</label><br>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="{{ route('admin.inventory-transactions.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Transactions List -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">รายการเคลื่อนไหว ({{ $transactions->total() }} รายการ)</h3>
        </div>
        <div class="card-body">
            @if($transactions->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th width="12%">วันที่/เวลา</th>
                                <th width="10%">รหัสธุรกรรม</th>
                                <th width="15%">สินค้า</th>
                                <th width="12%">คลัง</th>
                                <th width="8%">ประเภท</th>
                                <th width="8%">จำนวน</th>
                                <th width="12%">อ้างอิง</th>
                                <th>หมายเหตุ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                                <tr>
                                    <td>
                                        {{ $transaction->transaction_date->format('d/m/Y') }}<br>
                                        <small class="text-muted">{{ $transaction->transaction_date->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <code>{{ $transaction->transaction_code }}</code>
                                    </td>
                                    <td>
                                        <strong>{{ $transaction->product->name }}</strong><br>
                                        <small class="text-muted">SKU: {{ $transaction->product->sku }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $transaction->warehouse->code }}</span><br>
                                        <small>{{ $transaction->warehouse->name }}</small>
                                    </td>
                                    <td class="text-center">
                                        @if($transaction->transaction_type === 'in')
                                            <span class="badge badge-success">
                                                <i class="fas fa-arrow-down"></i> เข้า
                                            </span>
                                        @else
                                            <span class="badge badge-danger">
                                                <i class="fas fa-arrow-up"></i> ออก
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <strong>{{ number_format($transaction->quantity) }}</strong>
                                    </td>
                                    <td>
                                        @if($transaction->reference_type && $transaction->reference_id)
                                            <span class="badge badge-secondary">
                                                {{ str_replace('_', ' ', $transaction->reference_type) }}
                                            </span><br>
                                            <small>ID: {{ $transaction->reference_id }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($transaction->notes)
                                            {{ $transaction->notes }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $transactions->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">ไม่พบรายการเคลื่อนไหว</h5>
                    <p class="text-muted">ลองปรับเงื่อนไขการค้นหาใหม่</p>
                </div>
            @endif
        </div>
    </div>
@stop

@section('css')
    <style>
        .badge {
            font-size: 0.9em;
        }
        .table td {
            vertical-align: middle;
        }
    </style>
@stop