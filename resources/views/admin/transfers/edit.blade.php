@extends('adminlte::page')

@section('title', 'แก้ไขใบโอนสินค้า')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>แก้ไขใบโอนสินค้า #{{ $transfer->transfer_code }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.transfers.index') }}">การโอนสินค้า</a></li>
                <li class="breadcrumb-item active">แก้ไข</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            {{-- Transfer Info (Read-only) --}}
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">ข้อมูลการโอน (ไม่สามารถแก้ไข)</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>รหัสใบโอน</label>
                                <input type="text" class="form-control" value="{{ $transfer->transfer_code }}" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>สถานะ</label>
                                <input type="text" class="form-control" value="รอดำเนินการ" disabled>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>คลังต้นทาง</label>
                                <input type="text" class="form-control" value="{{ $transfer->fromWarehouse->name ?? '-' }} ({{ $transfer->fromWarehouse->code ?? '' }})" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>คลังปลายทาง</label>
                                <input type="text" class="form-control" value="{{ $transfer->toWarehouse->name ?? '-' }} ({{ $transfer->toWarehouse->code ?? '' }})" disabled>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>สินค้า</label>
                        <input type="text" class="form-control" value="{{ $transfer->product->full_name ?? '-' }} ({{ $transfer->product->sku ?? '' }})" disabled>
                    </div>
                </div>
            </div>

            {{-- Editable Fields --}}
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">แก้ไขรายละเอียด</h3>
                </div>
                <form action="{{ route('admin.transfers.update', $transfer) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quantity">จำนวนที่โอน <span class="text-danger">*</span></label>
                                    <input type="number"
                                           class="form-control @error('quantity') is-invalid @enderror"
                                           id="quantity"
                                           name="quantity"
                                           value="{{ old('quantity', $transfer->quantity) }}"
                                           min="1"
                                           required>
                                    @error('quantity')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="priority">ความสำคัญ <span class="text-danger">*</span></label>
                                    <select class="form-control @error('priority') is-invalid @enderror"
                                            id="priority"
                                            name="priority"
                                            required>
                                        <option value="low" {{ old('priority', $transfer->priority) == 'low' ? 'selected' : '' }}>ต่ำ</option>
                                        <option value="normal" {{ old('priority', $transfer->priority) == 'normal' ? 'selected' : '' }}>ปกติ</option>
                                        <option value="high" {{ old('priority', $transfer->priority) == 'high' ? 'selected' : '' }}>สูง</option>
                                        <option value="urgent" {{ old('priority', $transfer->priority) == 'urgent' ? 'selected' : '' }}>เร่งด่วน</option>
                                    </select>
                                    @error('priority')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="notes">หมายเหตุ</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes"
                                      name="notes"
                                      rows="4"
                                      placeholder="หมายเหตุเพิ่มเติม">{{ old('notes', $transfer->notes) }}</textarea>
                            @error('notes')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <a href="{{ route('admin.transfers.show', $transfer) }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> ย้อนกลับ
                                </a>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> บันทึกการแก้ไข
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
