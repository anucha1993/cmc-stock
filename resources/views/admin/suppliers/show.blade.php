@extends('adminlte::page')

@section('title', 'รายละเอียดผู้จำหน่าย')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>รายละเอียดผู้จำหน่าย</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.suppliers.index') }}">ผู้จำหน่าย</a></li>
                <li class="breadcrumb-item active">{{ $supplier->name }}</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <!-- Supplier Info -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ข้อมูลผู้จำหน่าย: {{ $supplier->name }}</h3>
                    <div class="card-tools">
                        @if($supplier->is_active)
                            <span class="badge badge-success badge-lg">เปิดใช้งาน</span>
                        @else
                            <span class="badge badge-secondary badge-lg">ปิดใช้งาน</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">ชื่อผู้จำหน่าย:</th>
                                    <td><strong>{{ $supplier->name }}</strong></td>
                                </tr>
                                @if($supplier->company)
                                <tr>
                                    <th>ชื่อบริษัท:</th>
                                    <td>{{ $supplier->company }}</td>
                                </tr>
                                @endif
                                @if($supplier->contact_person)
                                <tr>
                                    <th>ผู้ติดต่อ:</th>
                                    <td>{{ $supplier->contact_person }}</td>
                                </tr>
                                @endif
                                @if($supplier->phone)
                                <tr>
                                    <th>เบอร์โทร:</th>
                                    <td>
                                        <i class="fas fa-phone text-primary"></i> 
                                        <a href="tel:{{ $supplier->phone }}">{{ $supplier->phone }}</a>
                                    </td>
                                </tr>
                                @endif
                                @if($supplier->email)
                                <tr>
                                    <th>อีเมล:</th>
                                    <td>
                                        <i class="fas fa-envelope text-primary"></i> 
                                        <a href="mailto:{{ $supplier->email }}">{{ $supplier->email }}</a>
                                    </td>
                                </tr>
                                @endif
                                @if($supplier->website)
                                <tr>
                                    <th>เว็บไซต์:</th>
                                    <td>
                                        <i class="fas fa-globe text-primary"></i> 
                                        <a href="{{ $supplier->website }}" target="_blank">{{ $supplier->website }}</a>
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                @if($supplier->tax_id)
                                <tr>
                                    <th width="40%">เลขประจำตัวผู้เสียภาษี:</th>
                                    <td>{{ $supplier->tax_id }}</td>
                                </tr>
                                @endif
                                @if($supplier->payment_terms)
                                <tr>
                                    <th>เงื่อนไขการชำระเงิน:</th>
                                    <td>
                                        @php
                                            $paymentTerms = [
                                                'cash' => 'เงินสด',
                                                'credit_7' => 'เครดิต 7 วัน',
                                                'credit_15' => 'เครดิต 15 วัน',
                                                'credit_30' => 'เครดิต 30 วัน',
                                                'credit_60' => 'เครดิต 60 วัน',
                                                'other' => 'อื่นๆ'
                                            ];
                                        @endphp
                                        {{ $paymentTerms[$supplier->payment_terms] ?? $supplier->payment_terms }}
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <th>วันที่เพิ่ม:</th>
                                    <td>{{ $supplier->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>อัปเดตล่าสุด:</th>
                                    <td>{{ $supplier->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Address -->
                    @if($supplier->address || $supplier->district || $supplier->province || $supplier->postal_code)
                        <div class="row mt-3">
                            <div class="col-12">
                                <h5><i class="fas fa-map-marker-alt"></i> ที่อยู่</h5>
                                <div class="alert alert-light">
                                    @if($supplier->address)
                                        {{ $supplier->address }}<br>
                                    @endif
                                    @if($supplier->district || $supplier->province || $supplier->postal_code)
                                        {{ $supplier->district ? $supplier->district . ' ' : '' }}
                                        {{ $supplier->province ? $supplier->province . ' ' : '' }}
                                        {{ $supplier->postal_code }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Notes -->
                    @if($supplier->notes)
                        <div class="row mt-3">
                            <div class="col-12">
                                <h5><i class="fas fa-sticky-note"></i> หมายเหตุ</h5>
                                <div class="alert alert-info">
                                    {{ $supplier->notes }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Products from this supplier -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">สินค้าจากผู้จำหน่ายนี้</h3>
                    <div class="card-tools">
                        <span class="badge badge-info">{{ $supplier->products->count() }} รายการ</span>
                    </div>
                </div>
                <div class="card-body">
                    @if($supplier->products->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>สินค้า</th>
                                        <th>SKU</th>
                                        <th>หมวดหมู่</th>
                                        <th>ราคา</th>
                                        <th>สต็อก</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($supplier->products->take(10) as $product)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.products.show', $product) }}">
                                                    {{ $product->name }}
                                                </a>
                                            </td>
                                            <td><code>{{ $product->sku }}</code></td>
                                            <td>{{ $product->category->name ?? '-' }}</td>
                                            <td>฿{{ number_format($product->price, 2) }}</td>
                                            <td>
                                                <span class="badge badge-{{ $product->quantity > $product->min_stock ? 'success' : 'warning' }}">
                                                    {{ number_format($product->quantity) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($supplier->products->count() > 10)
                            <div class="text-center mt-3">
                                <a href="{{ route('admin.products.index', ['supplier' => $supplier->id]) }}" 
                                   class="btn btn-outline-primary">
                                    ดูสินค้าทั้งหมด ({{ $supplier->products->count() }} รายการ)
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-box-open fa-3x mb-3"></i><br>
                            ยังไม่มีสินค้าจากผู้จำหน่ายนี้
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions & Stats -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">การดำเนินการ</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.suppliers.edit', $supplier) }}" 
                       class="btn btn-warning btn-block mb-2">
                        <i class="fas fa-edit"></i> แก้ไข
                    </a>
                    <button type="button" class="btn btn-danger btn-block mb-2" 
                            onclick="confirmDelete()">
                        <i class="fas fa-trash"></i> ลบ
                    </button>
                    <a href="{{ route('admin.suppliers.index') }}" 
                       class="btn btn-secondary btn-block">
                        <i class="fas fa-arrow-left"></i> ย้อนกลับ
                    </a>
                </div>
            </div>

            <!-- Statistics -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">สถิติ</h3>
                </div>
                <div class="card-body">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-boxes"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">จำนวนสินค้า</span>
                            <span class="info-box-number">{{ $supplier->products->count() }}</span>
                        </div>
                    </div>

                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-money-bill-wave"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">มูลค่ารวม</span>
                            <span class="info-box-number">
                                ฿{{ number_format($supplier->products->sum(function($product) {
                                    return $product->price * $product->quantity;
                                }), 2) }}
                            </span>
                        </div>
                    </div>

                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-warehouse"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">สต็อกรวม</span>
                            <span class="info-box-number">{{ number_format($supplier->products->sum('quantity')) }}</span>
                        </div>
                    </div>

                    <div class="info-box">
                        <span class="info-box-icon bg-primary"><i class="fas fa-chart-line"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">ราคาเฉลี่ย</span>
                            <span class="info-box-number">
                                ฿{{ $supplier->products->count() > 0 ? number_format($supplier->products->avg('price'), 2) : '0.00' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ยืนยันการลบ</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>คุณแน่ใจหรือไม่ที่จะลบผู้จำหน่าย <strong>{{ $supplier->name }}</strong>?</p>
                    <p class="text-warning"><i class="fas fa-exclamation-triangle"></i> การดำเนินการนี้ไม่สามารถยกเลิกได้</p>
                    @if($supplier->products->count() > 0)
                        <p class="text-danger">
                            <i class="fas fa-exclamation-circle"></i> 
                            ผู้จำหน่ายนี้มีสินค้า {{ $supplier->products->count() }} รายการ 
                            กรุณาลบสินค้าหรือเปลี่ยนผู้จำหน่ายก่อน
                        </p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    @if($supplier->products->count() == 0)
                        <form action="{{ route('admin.suppliers.destroy', $supplier) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">ลบ</button>
                        </form>
                    @else
                        <button type="button" class="btn btn-danger" disabled>ไม่สามารถลบได้</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .badge-lg {
            font-size: 1em;
            padding: 8px 12px;
        }
        .info-box {
            margin-bottom: 1rem;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
    </style>
@stop

@section('js')
    <script>
        function confirmDelete() {
            $('#deleteModal').modal('show');
        }

        // Alert auto hide
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
@stop