@extends('adminlte::page')

@section('title', 'รายงานแพสินค้า')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>รายงานแพสินค้า</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.packages.index') }}">แพสินค้า</a></li>
                <li class="breadcrumb-item active">รายงาน</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    {{-- Stats --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total_packages'] }}</h3>
                    <p>แพทั้งหมด</p>
                </div>
                <div class="icon"><i class="fas fa-archive"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['active_packages'] }}</h3>
                    <p>แพที่ใช้งาน</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>฿{{ number_format($stats['total_package_value'], 2) }}</h3>
                    <p>มูลค่าขายรวม</p>
                </div>
                <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>฿{{ number_format($stats['total_cost_value'], 2) }}</h3>
                    <p>ต้นทุนรวม</p>
                </div>
                <div class="icon"><i class="fas fa-receipt"></i></div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title">กรองข้อมูล</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.packages.report') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>วันที่เริ่มต้น</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>วันที่สิ้นสุด</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>ผู้จำหน่าย</label>
                            <select name="supplier_id" class="form-control">
                                <option value="">ทั้งหมด</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>หมวดหมู่</label>
                            <select name="category_id" class="form-control">
                                <option value="">ทั้งหมด</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
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
            <h3 class="card-title">รายการแพสินค้า ({{ $packages->count() }} รายการ)</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ชื่อแพ</th>
                            <th>ผู้จำหน่าย</th>
                            <th>หมวดหมู่</th>
                            <th class="text-center">จำนวนสินค้า</th>
                            <th class="text-center">สถานะ</th>
                            <th>วันที่สร้าง</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($packages as $i => $package)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                <a href="{{ route('admin.packages.show', $package) }}">
                                    <strong>{{ $package->name }}</strong>
                                </a>
                                @if($package->code)
                                    <br><small class="text-muted">{{ $package->code }}</small>
                                @endif
                            </td>
                            <td>{{ $package->supplier->name ?? '-' }}</td>
                            <td>{{ $package->category->name ?? '-' }}</td>
                            <td class="text-center"><span class="badge badge-info">{{ $package->products_count }}</span></td>
                            <td class="text-center">
                                @if($package->is_active)
                                    <span class="badge badge-success">ใช้งาน</span>
                                @else
                                    <span class="badge badge-secondary">ปิด</span>
                                @endif
                            </td>
                            <td><small>{{ $package->created_at->format('d/m/Y') }}</small></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">ไม่พบข้อมูล</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop
