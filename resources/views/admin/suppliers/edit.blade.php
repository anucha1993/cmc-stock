@extends('adminlte::page')

@section('title', 'แก้ไขผู้จำหน่าย')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>แก้ไขผู้จำหน่าย</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.suppliers.index') }}">ผู้จำหน่าย</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.suppliers.show', $supplier) }}">{{ $supplier->name }}</a></li>
                <li class="breadcrumb-item active">แก้ไข</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">แก้ไขข้อมูลผู้จำหน่าย: {{ $supplier->name }}</h3>
                </div>
                <form action="{{ route('admin.suppliers.update', $supplier) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <!-- Basic Info -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">ชื่อผู้จำหน่าย <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $supplier->name) }}" 
                                           placeholder="ชื่อผู้จำหน่าย"
                                           required>
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="company">ชื่อบริษัท</label>
                                    <input type="text" 
                                           class="form-control @error('company') is-invalid @enderror" 
                                           id="company" 
                                           name="company" 
                                           value="{{ old('company', $supplier->company) }}" 
                                           placeholder="ชื่อบริษัท (ถ้ามี)">
                                    @error('company')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Contact Info -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact_person">ผู้ติดต่อ</label>
                                    <input type="text" 
                                           class="form-control @error('contact_person') is-invalid @enderror" 
                                           id="contact_person" 
                                           name="contact_person" 
                                           value="{{ old('contact_person', $supplier->contact_person) }}" 
                                           placeholder="ชื่อผู้ติดต่อ">
                                    @error('contact_person')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">เบอร์โทรศัพท์</label>
                                    <input type="text" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" 
                                           name="phone" 
                                           value="{{ old('phone', $supplier->phone) }}" 
                                           placeholder="เบอร์โทรติดต่อ">
                                    @error('phone')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">อีเมล</label>
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email', $supplier->email) }}" 
                                           placeholder="อีเมลติดต่อ">
                                    @error('email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="website">เว็บไซต์</label>
                                    <input type="url" 
                                           class="form-control @error('website') is-invalid @enderror" 
                                           id="website" 
                                           name="website" 
                                           value="{{ old('website', $supplier->website) }}" 
                                           placeholder="https://example.com">
                                    @error('website')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Address Info -->
                        <div class="form-group">
                            <label for="address">ที่อยู่</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" 
                                      name="address" 
                                      rows="3" 
                                      placeholder="ที่อยู่ผู้จำหน่าย">{{ old('address', $supplier->address) }}</textarea>
                            @error('address')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="district">อำเภอ/เขต</label>
                                    <input type="text" 
                                           class="form-control @error('district') is-invalid @enderror" 
                                           id="district" 
                                           name="district" 
                                           value="{{ old('district', $supplier->district) }}" 
                                           placeholder="อำเภอ/เขต">
                                    @error('district')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="province">จังหวัด</label>
                                    <input type="text" 
                                           class="form-control @error('province') is-invalid @enderror" 
                                           id="province" 
                                           name="province" 
                                           value="{{ old('province', $supplier->province) }}" 
                                           placeholder="จังหวัด">
                                    @error('province')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="postal_code">รหัสไปรษณีย์</label>
                                    <input type="text" 
                                           class="form-control @error('postal_code') is-invalid @enderror" 
                                           id="postal_code" 
                                           name="postal_code" 
                                           value="{{ old('postal_code', $supplier->postal_code) }}" 
                                           placeholder="รหัสไปรษณีย์"
                                           maxlength="5">
                                    @error('postal_code')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Business Info -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tax_id">เลขประจำตัวผู้เสียภาษี</label>
                                    <input type="text" 
                                           class="form-control @error('tax_id') is-invalid @enderror" 
                                           id="tax_id" 
                                           name="tax_id" 
                                           value="{{ old('tax_id', $supplier->tax_id) }}" 
                                           placeholder="เลขประจำตัวผู้เสียภาษี"
                                           maxlength="13">
                                    @error('tax_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="payment_terms">เงื่อนไขการชำระเงิน</label>
                                    <select class="form-control @error('payment_terms') is-invalid @enderror" 
                                            id="payment_terms" 
                                            name="payment_terms">
                                        <option value="">เลือกเงื่อนไขการชำระเงิน</option>
                                        <option value="cash" {{ old('payment_terms', $supplier->payment_terms) == 'cash' ? 'selected' : '' }}>เงินสด</option>
                                        <option value="credit_7" {{ old('payment_terms', $supplier->payment_terms) == 'credit_7' ? 'selected' : '' }}>เครดิต 7 วัน</option>
                                        <option value="credit_15" {{ old('payment_terms', $supplier->payment_terms) == 'credit_15' ? 'selected' : '' }}>เครดิต 15 วัน</option>
                                        <option value="credit_30" {{ old('payment_terms', $supplier->payment_terms) == 'credit_30' ? 'selected' : '' }}>เครดิต 30 วัน</option>
                                        <option value="credit_60" {{ old('payment_terms', $supplier->payment_terms) == 'credit_60' ? 'selected' : '' }}>เครดิต 60 วัน</option>
                                        <option value="other" {{ old('payment_terms', $supplier->payment_terms) == 'other' ? 'selected' : '' }}>อื่นๆ</option>
                                    </select>
                                    @error('payment_terms')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="form-group">
                            <label for="notes">หมายเหตุ</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3" 
                                      placeholder="หมายเหตุเพิ่มเติม">{{ old('notes', $supplier->notes) }}</textarea>
                            @error('notes')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="is_active" 
                                       name="is_active" 
                                       {{ old('is_active', $supplier->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">เปิดใช้งาน</label>
                            </div>
                        </div>

                        <!-- Last updated info -->
                        <div class="alert alert-info">
                            <small>
                                <i class="fas fa-info-circle"></i>
                                สร้างเมื่อ: {{ $supplier->created_at->format('d/m/Y H:i') }} | 
                                อัปเดตล่าสุด: {{ $supplier->updated_at->format('d/m/Y H:i') }}
                            </small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-4">
                                <a href="{{ route('admin.suppliers.show', $supplier) }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> ย้อนกลับ
                                </a>
                            </div>
                            <div class="col-md-8 text-right">
                                <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary mr-2">
                                    <i class="fas fa-times"></i> ยกเลิก
                                </a>
                                <button type="submit" class="btn btn-warning">
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

@section('css')
    <style>
        .form-group label {
            font-weight: 600;
        }
        .custom-control-label {
            font-weight: 500;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Auto-focus first input
            $('#name').focus();

            // Format postal code
            $('#postal_code').on('input', function() {
                var value = $(this).val().replace(/\D/g, ''); // Remove non-digits
                $(this).val(value);
            });

            // Format tax ID
            $('#tax_id').on('input', function() {
                var value = $(this).val().replace(/\D/g, ''); // Remove non-digits
                $(this).val(value);
            });
        });
    </script>
@stop