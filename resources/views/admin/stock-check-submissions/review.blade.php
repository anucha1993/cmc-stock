@extends('adminlte::page')

@section('title', 'ตรวจสอบการส่ง')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>ตรวจสอบการส่ง #{{ $submission->id }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าแรก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.stock-check-submissions.index') }}">รายการส่งตรวจสอบ</a></li>
                <li class="breadcrumb-item active">ตรวจสอบ</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <form method="POST" action="{{ route('admin.stock-check-submissions.process-decision', $submission) }}" id="reviewForm">
            @csrf
            
            <!-- Summary Row -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-primary">
                        <div class="card-header bg-primary">
                            <h3 class="card-title text-white">สรุปผลการตรวจสอบ</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">รายการที่พบ</span>
                                            <span class="info-box-number">{{ $analysis['found_items']->count() }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-warning"><i class="fas fa-exclamation"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">รายการขาดหาย</span>
                                            <span class="info-box-number">{{ $analysis['missing_items']->count() }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-info"><i class="fas fa-plus"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">ไม่มีในระบบ</span>
                                            <span class="info-box-number">{{ $analysis['extra_items']->count() }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">ปัญหาทั้งหมด</span>
                                            <span class="info-box-number">{{ $analysis['total_issues'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recommendations -->
            @if(!empty($analysis['recommendations']))
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">คำแนะนำ</h3>
                            </div>
                            <div class="card-body">
                                @foreach($analysis['recommendations'] as $rec)
                                    <div class="alert alert-{{ $rec['type'] === 'warning' ? 'warning' : 'info' }}">
                                        <h5><i class="fas fa-{{ $rec['type'] === 'warning' ? 'exclamation-triangle' : 'info-circle' }}"></i> {{ $rec['title'] }}</h5>
                                        {{ $rec['description'] }}
                                        <br><small><strong>แนะนำ:</strong> {{ $rec['action'] }}</small>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Items that need decisions -->
            @if($analysis['missing_items']->count() > 0 || $analysis['extra_items']->count() > 0 || $analysis['found_items']->count() > 0)
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">รายการที่ต้องตัดสินใจ</h3>
                            </div>
                            <div class="card-body">
                                <!-- Found Items -->
                                @if($analysis['found_items']->count() > 0)
                                    <h5 class="text-success"><i class="fas fa-check"></i> รายการที่พบ</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Barcode</th>
                                                    <th>ชื่อสินค้า</th>
                                                    <th>หมวดหมู่</th>
                                                    <th>จำนวนที่สแกน</th>
                                                    <th>การดำเนินการ</th>
                                                    <th>หมายเหตุ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($analysis['found_items'] as $index => $item)
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
                                                            <select name="item_decisions[found_{{ $index }}][action]" class="form-control form-control-sm">
                                                                <option value="add_to_system">เพิ่มเข้าระบบ</option>
                                                                <option value="update_location">อัปเดตตำแหน่ง</option>
                                                            </select>
                                                            <input type="hidden" name="item_decisions[found_{{ $index }}][stock_item_id]" value="{{ $item['stock_item_id'] ?? '' }}">
                                                            <input type="hidden" name="item_decisions[found_{{ $index }}][barcode]" value="{{ $item['barcode'] }}">
                                                        </td>
                                                        <td>
                                                            <input type="text" name="item_decisions[found_{{ $index }}][notes]" class="form-control form-control-sm" placeholder="หมายเหตุ...">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif

                                <!-- Missing Items -->
                                @if($analysis['missing_items']->count() > 0)
                                    <h5 class="text-warning mt-4"><i class="fas fa-exclamation"></i> รายการที่ขาดหาย</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Barcode</th>
                                                    <th>ชื่อสินค้า</th>
                                                    <th>หมวดหมู่</th>
                                                    <th>ตำแหน่ง</th>
                                                    <th>การดำเนินการ</th>
                                                    <th>หมายเหตุ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($analysis['missing_items'] as $index => $item)
                                                    <tr>
                                                        <td><code>{{ $item['barcode'] }}</code></td>
                                                        <td>{{ $item['product_name'] ?? 'ไม่ระบุ' }}</td>
                                                        <td>{{ $item['category_name'] ?? 'ไม่ระบุ' }}</td>
                                                        <td>{{ $item['location_code'] ?? 'ไม่ระบุ' }}</td>
                                                        <td>
                                                            <select name="item_decisions[missing_{{ $index }}][action]" class="form-control form-control-sm">
                                                                <option value="mark_missing">มาร์คว่าขาดหาย</option>
                                                                <option value="remove_from_list">ลบออกจากรายการ</option>
                                                            </select>
                                                            <input type="hidden" name="item_decisions[missing_{{ $index }}][stock_item_id]" value="{{ $item['stock_item_id'] ?? '' }}">
                                                            <input type="hidden" name="item_decisions[missing_{{ $index }}][barcode]" value="{{ $item['barcode'] }}">
                                                        </td>
                                                        <td>
                                                            <input type="text" name="item_decisions[missing_{{ $index }}][notes]" class="form-control form-control-sm" placeholder="หมายเหตุ...">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif

                                <!-- Extra Items -->
                                @if($analysis['extra_items']->count() > 0)
                                    <h5 class="text-info mt-4"><i class="fas fa-plus"></i> รายการที่ไม่มีในระบบ</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Barcode</th>
                                                    <th>จำนวนที่สแกน</th>
                                                    <th>การดำเนินการ</th>
                                                    <th>หมายเหตุ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($analysis['extra_items'] as $index => $item)
                                                    <tr>
                                                        <td><code>{{ $item['barcode'] }}</code></td>
                                                        <td>{{ $item['scanned_count'] }}</td>
                                                        <td>
                                                            <select name="item_decisions[extra_{{ $index }}][action]" class="form-control form-control-sm">
                                                                <option value="add_to_system">เพิ่มเข้าระบบ</option>
                                                                <option value="remove_from_list">ลบออกจากรายการ</option>
                                                            </select>
                                                            <input type="hidden" name="item_decisions[extra_{{ $index }}][barcode]" value="{{ $item['barcode'] }}">
                                                            <input type="hidden" name="item_decisions[extra_{{ $index }}][scanned_count]" value="{{ $item['scanned_count'] }}">
                                                        </td>
                                                        <td>
                                                            <input type="text" name="item_decisions[extra_{{ $index }}][notes]" class="form-control form-control-sm" placeholder="หมายเหตุ...">
                                                        </td>
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
            @endif

            <!-- Decision Section -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">การตัดสินใจ</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>ตัดสินใจ:</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="decision" id="approve" value="approve" {{ $analysis['total_issues'] == 0 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="approve">
                                            <i class="fas fa-check text-success"></i> อนุมัติทั้งหมด
                                        </label>
                                    </div>
                                    @if($analysis['total_issues'] > 0)
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="decision" id="partial" value="partial" checked>
                                            <label class="form-check-label" for="partial">
                                                <i class="fas fa-check-double text-primary"></i> อนุมัติบางส่วน
                                            </label>
                                        </div>
                                    @endif
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="decision" id="reject" value="reject">
                                        <label class="form-check-label" for="reject">
                                            <i class="fas fa-times text-danger"></i> ปฏิเสธ
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="review_notes">หมายเหตุการตรวจสอบ:</label>
                                <textarea name="review_notes" id="review_notes" class="form-control" rows="3" required 
                                          placeholder="กรอกหมายเหตุสำหรับการตัดสินใจนี้..."></textarea>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> บันทึกการตัดสินใจ
                                </button>
                                <a href="{{ route('admin.stock-check-submissions.show', $submission) }}" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-arrow-left"></i> ยกเลิก
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop

@section('css')
    <style>
        .form-check-inline .form-check-input {
            margin-right: 0.5rem;
        }
        .form-check-inline .form-check-label {
            margin-right: 1rem;
        }
        .table th, .table td {
            vertical-align: middle;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Update form validation based on decision
            $('input[name="decision"]').change(function() {
                const decision = $(this).val();
                const reviewNotes = $('#review_notes');
                
                if (decision === 'approve') {
                    reviewNotes.attr('placeholder', 'อนุมัติทั้งหมด - เหตุผล...');
                } else if (decision === 'partial') {
                    reviewNotes.attr('placeholder', 'อนุมัติบางส่วน - เหตุผลและรายละเอียดที่อนุมัติ...');
                } else if (decision === 'reject') {
                    reviewNotes.attr('placeholder', 'ปฏิเสธ - เหตุผลและแนวทางแก้ไข...');
                }
            });

            // Form validation
            $('#reviewForm').submit(function(e) {
                const decision = $('input[name="decision"]:checked').val();
                const reviewNotes = $('#review_notes').val().trim();
                
                if (!decision) {
                    e.preventDefault();
                    alert('กรุณาเลือกการตัดสินใจ');
                    return false;
                }
                
                if (!reviewNotes) {
                    e.preventDefault();
                    alert('กรุณากรอกหมายเหตุการตรวจสอบ');
                    $('#review_notes').focus();
                    return false;
                }
                
                // Confirm decision
                let confirmMessage = '';
                if (decision === 'approve') {
                    confirmMessage = 'ต้องการอนุมัติทั้งหมดและปรับปรุงสต๊อกหรือไม่?';
                } else if (decision === 'partial') {
                    confirmMessage = 'ต้องการอนุมัติบางส่วนตามที่เลือกหรือไม่?';
                } else {
                    confirmMessage = 'ต้องการปฏิเสธรายการนี้หรือไม่?';
                }
                
                if (!confirm(confirmMessage)) {
                    e.preventDefault();
                    return false;
                }
                
                // Show loading
                $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> กำลังประมวลผล...');
            });
        });
    </script>
@stop