<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('logo1.png') }}">
    <title>ลิงก์หมดอายุ</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh">
    <div class="text-center px-4" style="max-width:420px">
        <div class="mb-4">
            <i class="fas fa-clock fa-4x text-danger"></i>
        </div>
        <h3 class="mb-3">ลิงก์หมดอายุแล้ว</h3>
        <p class="text-muted">
            ลิงก์สำหรับสแกน Barcode นี้หมดอายุแล้ว (ใช้ได้ 3 ชั่วโมง)
            <br>กรุณาขอลิงก์ใหม่จากผู้ดูแลระบบ
        </p>
        <a href="{{ url('/') }}" class="btn btn-outline-secondary mt-3">
            <i class="fas fa-home"></i> กลับหน้าหลัก
        </a>
    </div>
</body>
</html>
