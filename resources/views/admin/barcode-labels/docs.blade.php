@extends('adminlte::page')

@section('title', 'เงื่อนไขการพิมพ์ Label Barcode - CMC-STOCK')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-book"></i> เงื่อนไขการพิมพ์ Label Barcode</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">แดชบอร์ด</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.barcode-labels.index') }}">พิมพ์ Label Barcode</a></li>
                <li class="breadcrumb-item active">เงื่อนไข / คู่มือ</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <!-- Main Documentation -->
        <div class="col-md-8">
            <!-- Section 1: Overview -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> 1. ภาพรวมระบบบาร์โค้ด</h3>
                </div>
                <div class="card-body">
                    <p>ระบบ CMC-STOCK ใช้บาร์โค้ด <strong>2 ระดับ</strong> ในการติดตามสินค้า:</p>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>ระดับ</th>
                                    <th>รูปแบบ</th>
                                    <th>ตัวอย่าง</th>
                                    <th>การใช้งาน</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge badge-primary">Product-level</span></td>
                                    <td><code>CMC</code> + เลข 8 หลัก</td>
                                    <td><code>CMC00000001</code></td>
                                    <td>ระบุ<strong>ประเภท</strong>สินค้า (1 สินค้า = 1 บาร์โค้ด)</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-success">Stock Item-level</span></td>
                                    <td>รหัสสินค้า + รหัสคลัง + วันเวลา + random</td>
                                    <td><code>PRD01WH0125012414300042</code></td>
                                    <td>ระบุ<strong>ชิ้นสินค้าเฉพาะ</strong> (1 ชิ้น = 1 บาร์โค้ด ไม่ซ้ำกัน)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="callout callout-info">
                        <h5><i class="fas fa-lightbulb"></i> หลักสำคัญ</h5>
                        <p class="mb-0">
                            บาร์โค้ด <strong>Stock Item</strong> แต่ละตัวไม่ซ้ำกันทั้งระบบ (Unique)
                            — ถ้าพิมพ์ซ้ำแล้วติดสินค้า 2 ตัว ระบบจะชี้ไปตัวเดียวกัน ทำให้ข้อมูลสต็อกผิดพลาด
                        </p>
                    </div>
                </div>
            </div>

            <!-- Section 2: Rules -->
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> 2. กฎสำคัญ — ห้ามทำ</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="text-danger"><i class="fas fa-ban"></i> ห้ามพิมพ์ Label ซ้ำแล้วติดสินค้าคนละตัว</h5>
                                    <p class="text-sm">
                                        1 Label = 1 สินค้าเท่านั้น ถ้าพิมพ์ซ้ำแล้วเอาไปติดตัวอื่น
                                        ระบบจะสับสนว่าสินค้าตัวไหนเป็นตัวไหน
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="text-danger"><i class="fas fa-ban"></i> ห้ามใช้ Label ข้ามสินค้า</h5>
                                    <p class="text-sm">
                                        Label ของสินค้า A ห้ามนำไปติดสินค้า B 
                                        แม้จะเป็นสินค้าชนิดเดียวกันแต่คนละชิ้น
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="text-danger"><i class="fas fa-ban"></i> ห้ามพิมพ์หลายสินค้าพร้อมกันแล้วหยิบปน</h5>
                                    <p class="text-sm">
                                        ควรพิมพ์ทีละสินค้า / ทีละกลุ่ม แล้วติดให้เสร็จก่อนพิมพ์กลุ่มถัดไป
                                        เพื่อป้องกันหยิบ Label สลับกัน
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="text-danger"><i class="fas fa-ban"></i> ห้ามทิ้ง Label ที่พิมพ์แล้วไม่ใช้</h5>
                                    <p class="text-sm">
                                        ถ้าพิมพ์แล้วไม่ใช้ ต้องทำลาย (ฉีก/ขีดฆ่า) 
                                        เพื่อป้องกันคนอื่นเอาไปติดผิดตัว
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Workflow -->
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-tasks"></i> 3. ขั้นตอนการพิมพ์ที่ถูกต้อง</h3>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="time-label">
                            <span class="bg-primary">ขั้นตอนปฏิบัติ</span>
                        </div>

                        <div>
                            <i class="fas fa-search bg-info"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header"><strong>ขั้นที่ 1:</strong> เลือกสินค้าที่ต้องการพิมพ์</h3>
                                <div class="timeline-body">
                                    เข้าหน้า <a href="{{ route('admin.barcode-labels.index') }}">พิมพ์ Label Barcode</a> 
                                    → เลือกสินค้า → เลือก Stock Items ที่ต้องการ
                                    <br><span class="text-success"><i class="fas fa-lightbulb"></i> แนะนำ: กดปุ่ม "ยังไม่พิมพ์" เพื่อเลือกเฉพาะรายการใหม่</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <i class="fas fa-print bg-warning"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header"><strong>ขั้นที่ 2:</strong> ตั้งค่าและพิมพ์</h3>
                                <div class="timeline-body">
                                    เลือกขนาด Label และจำนวนสำเนา (แนะนำ <strong>1 สำเนา</strong>) → กดพิมพ์
                                    <br><span class="text-warning"><i class="fas fa-exclamation-triangle"></i> ถ้ามีรายการที่เคยพิมพ์แล้ว ระบบจะขอเหตุผลก่อนพิมพ์ซ้ำ</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <i class="fas fa-tag bg-success"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header"><strong>ขั้นที่ 3:</strong> ติด Label ทันที</h3>
                                <div class="timeline-body">
                                    <strong>พิมพ์เสร็จ → ติดเลย</strong> อย่าเก็บไว้ติดทีหลัง
                                    <br>ติดในตำแหน่งที่สแกนง่าย ไม่บังข้อมูลสำคัญ
                                </div>
                            </div>
                        </div>

                        <div>
                            <i class="fas fa-barcode bg-purple"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header"><strong>ขั้นที่ 4:</strong> สแกนยืนยัน</h3>
                                <div class="timeline-body">
                                    เข้าหน้า <a href="{{ route('admin.barcode-labels.verify') }}">สแกนยืนยัน</a>
                                    → สแกนบาร์โค้ดบน Label ที่ติดแล้ว
                                    <br><span class="text-info"><i class="fas fa-check-double"></i> ระบบจะเช็คว่าบาร์โค้ดตรงกับสินค้าที่ถูกกำหนดหรือไม่</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <i class="fas fa-flag-checkered bg-dark"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header text-success"><strong>เสร็จสมบูรณ์!</strong></h3>
                                <div class="timeline-body">
                                    สินค้าพร้อมจัดเก็บ/จัดส่ง — บาร์โค้ดถูกต้องและยืนยันแล้ว
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 4: Reprint -->
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-redo"></i> 4. กรณีต้องพิมพ์ซ้ำ</h3>
                </div>
                <div class="card-body">
                    <p>สามารถพิมพ์ซ้ำได้ใน <strong>กรณีต่อไปนี้เท่านั้น:</strong></p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-group">
                                <li class="list-group-item list-group-item-success">
                                    <i class="fas fa-check text-success"></i> Label เสียหาย / ขาด / ลอก
                                </li>
                                <li class="list-group-item list-group-item-success">
                                    <i class="fas fa-check text-success"></i> Label สแกนไม่อ่าน (เครื่องพิมพ์มีปัญหา)
                                </li>
                                <li class="list-group-item list-group-item-success">
                                    <i class="fas fa-check text-success"></i> Label หลุดหาย (ต้องทำลาย Label เดิมก่อน ถ้าหาเจอ)
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <div class="callout callout-warning">
                                <h5><i class="fas fa-shield-alt"></i> ระบบป้องกัน</h5>
                                <ul class="mb-0">
                                    <li>ระบบจะแจ้งเตือนทุกครั้งที่พิมพ์ซ้ำ</li>
                                    <li>ต้องระบุ<strong>เหตุผล</strong>ก่อนพิมพ์ซ้ำ</li>
                                    <li>บันทึกประวัติทุกครั้ง (ใคร, เมื่อไหร่, เหตุผล)</li>
                                    <li>Admin สามารถตรวจสอบรายงานย้อนหลังได้</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="callout callout-danger mt-3">
                        <h5><i class="fas fa-exclamation-circle"></i> สิ่งที่ต้องทำเมื่อพิมพ์ซ้ำ</h5>
                        <ol class="mb-0">
                            <li><strong>ทำลาย Label เดิม</strong> — ฉีกทิ้ง / ขีดฆ่าให้ชัดเจน เพื่อป้องกันนำไปใช้ซ้ำ</li>
                            <li><strong>ระบุเหตุผล</strong>ในระบบก่อนพิมพ์ซ้ำ</li>
                            <li><strong>ติด Label ใหม่ทันที</strong>แล้วสแกนยืนยัน</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Section 5: Label Sizes -->
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-ruler"></i> 5. ขนาด Label และการใช้งาน</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>ขนาด</th>
                                    <th>มิติ</th>
                                    <th>ข้อมูลที่แสดง</th>
                                    <th>เหมาะกับ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge badge-secondary">เล็ก</span></td>
                                    <td>4 × 2 ซม.</td>
                                    <td>ชื่อสินค้า (25), SKU, บาร์โค้ด</td>
                                    <td>สินค้าชิ้นเล็ก, พื้นที่จำกัด</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-primary">กลาง</span></td>
                                    <td>6 × 3 ซม.</td>
                                    <td>ชื่อสินค้า (35), SKU, บาร์โค้ด, SN</td>
                                    <td>สินค้าทั่วไป <strong>(แนะนำ)</strong></td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-success">ใหญ่</span></td>
                                    <td>8 × 4 ซม.</td>
                                    <td>ชื่อสินค้า (50), SKU, บาร์โค้ด, SN, คลัง</td>
                                    <td>สินค้าชิ้นใหญ่, ต้องการข้อมูลครบ</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Section 6: FAQ -->
            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-question-circle"></i> 6. คำถามที่พบบ่อย (FAQ)</h3>
                </div>
                <div class="card-body">
                    <div id="faq-accordion">
                        <div class="card">
                            <div class="card-header" id="faq1">
                                <h5 class="mb-0">
                                    <button class="btn btn-link" data-toggle="collapse" data-target="#faq1-body">
                                        <i class="fas fa-chevron-right"></i> สินค้าใหม่ยังไม่มี Stock Item จะพิมพ์ Label ได้ไหม?
                                    </button>
                                </h5>
                            </div>
                            <div id="faq1-body" class="collapse" data-parent="#faq-accordion">
                                <div class="card-body">
                                    ได้ โดยใช้ <strong>"พิมพ์แบบ Product-level"</strong> ซึ่งจะใช้บาร์โค้ดของสินค้า (CMC...)
                                    แทนบาร์โค้ดรายชิ้น แต่วิธีนี้ไม่สามารถระบุตัวตนสินค้าแต่ละชิ้นได้
                                    — หากต้องการ Stock Item-level ต้อง<strong>สั่งผลิต</strong>ก่อน
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="faq2">
                                <h5 class="mb-0">
                                    <button class="btn btn-link" data-toggle="collapse" data-target="#faq2-body">
                                        <i class="fas fa-chevron-right"></i> พิมพ์ Label แล้วสแกนไม่อ่าน ต้องทำอย่างไร?
                                    </button>
                                </h5>
                            </div>
                            <div id="faq2-body" class="collapse" data-parent="#faq-accordion">
                                <div class="card-body">
                                    <ol>
                                        <li>ลอก Label เก่าออกแล้วทำลาย</li>
                                        <li>กลับไปหน้าพิมพ์ Label → เลือกรายการเดิม</li>
                                        <li>ระบบจะเตือนว่าเคยพิมพ์แล้ว → ระบุเหตุผล "สแกนไม่อ่าน"</li>
                                        <li>พิมพ์ใหม่ → ติด → สแกนยืนยัน</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="faq3">
                                <h5 class="mb-0">
                                    <button class="btn btn-link" data-toggle="collapse" data-target="#faq3-body">
                                        <i class="fas fa-chevron-right"></i> ทำไมต้องสแกนยืนยันหลังติด Label?
                                    </button>
                                </h5>
                            </div>
                            <div id="faq3-body" class="collapse" data-parent="#faq-accordion">
                                <div class="card-body">
                                    การสแกนยืนยันมี 2 วัตถุประสงค์:
                                    <ul>
                                        <li><strong>ตรวจสอบว่าติดถูกตัว</strong> — ข้อมูลในระบบตรงกับสินค้าจริง</li>
                                        <li><strong>ตรวจสอบว่าสแกนได้จริง</strong> — หมึกชัด, ไม่เบลอ, เครื่องสแกนอ่านได้</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="faq4">
                                <h5 class="mb-0">
                                    <button class="btn btn-link" data-toggle="collapse" data-target="#faq4-body">
                                        <i class="fas fa-chevron-right"></i> ใครมีสิทธิ์พิมพ์ Label ได้บ้าง?
                                    </button>
                                </h5>
                            </div>
                            <div id="faq4-body" class="collapse" data-parent="#faq-accordion">
                                <div class="card-body">
                                    ตามระบบสิทธิ์:
                                    <ul>
                                        <li><strong>พนักงานคลัง (Staff)</strong> ขึ้นไป — สามารถพิมพ์ได้</li>
                                        <li><strong>ผู้ดูข้อมูล (Viewer)</strong> — ดูได้อย่างเดียว ไม่สามารถพิมพ์</li>
                                        <li><strong>คนรถ (Driver)</strong> — ไม่สามารถพิมพ์ได้</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="faq5">
                                <h5 class="mb-0">
                                    <button class="btn btn-link" data-toggle="collapse" data-target="#faq5-body">
                                        <i class="fas fa-chevron-right"></i> ดูประวัติการพิมพ์ย้อนหลังได้ที่ไหน?
                                    </button>
                                </h5>
                            </div>
                            <div id="faq5-body" class="collapse" data-parent="#faq-accordion">
                                <div class="card-body">
                                    เข้าหน้า <a href="{{ route('admin.barcode-labels.history') }}">ประวัติการพิมพ์ Label</a>
                                    — สามารถกรองตามวันที่, ผู้พิมพ์, เฉพาะพิมพ์ซ้ำ, หรือเฉพาะรอยืนยัน
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Stats & Quick Links -->
        <div class="col-md-4">
            <!-- Quick Links -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-link"></i> ลิงก์ด่วน</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <a href="{{ route('admin.barcode-labels.index') }}">
                                <i class="fas fa-print text-primary"></i> พิมพ์ Label Barcode
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a href="{{ route('admin.barcode-labels.verify') }}">
                                <i class="fas fa-barcode text-success"></i> สแกนยืนยันติด Label
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a href="{{ route('admin.barcode-labels.history') }}">
                                <i class="fas fa-history text-info"></i> ประวัติการพิมพ์
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Stats -->
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-bar"></i> สถิติ</h3>
                </div>
                <div class="card-body">
                    <div class="info-box bg-gradient-primary">
                        <span class="info-box-icon"><i class="fas fa-print"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">พิมพ์ทั้งหมด</span>
                            <span class="info-box-number">{{ number_format($stats['total_prints']) }} ครั้ง</span>
                        </div>
                    </div>
                    <div class="info-box bg-gradient-success">
                        <span class="info-box-icon"><i class="fas fa-tags"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Label ทั้งหมด</span>
                            <span class="info-box-number">{{ number_format($stats['total_labels']) }} ใบ</span>
                        </div>
                    </div>
                    <div class="info-box bg-gradient-warning">
                        <span class="info-box-icon"><i class="fas fa-redo"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">พิมพ์ซ้ำ</span>
                            <span class="info-box-number">{{ number_format($stats['total_reprints']) }} ครั้ง</span>
                        </div>
                    </div>
                    <div class="info-box bg-gradient-info">
                        <span class="info-box-icon"><i class="fas fa-check-double"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">อัตราการยืนยัน</span>
                            <span class="info-box-number">{{ $stats['verified_rate'] }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Version Info -->
            <div class="card">
                <div class="card-body text-center text-muted">
                    <small>
                        <i class="fas fa-file-alt"></i> เอกสารนี้อัปเดตล่าสุด: 20/02/2026<br>
                        ระบบ CMC-STOCK v1.0
                    </small>
                </div>
            </div>
        </div>
    </div>
@stop
