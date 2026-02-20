<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('logo1.png') }}">
    <title>ใบตัดสต็อกเสร็จสิ้นแล้ว</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh">
    <div class="text-center px-4" style="max-width:420px">
        <div class="mb-4">
            <i class="fas fa-check-circle fa-4x text-success"></i>
        </div>
        <h3 class="mb-3">เสร็จสิ้นแล้ว</h3>
        <p class="text-muted">
            ใบตัดสต็อก <strong>{{ $deliveryNote->delivery_number }}</strong> ดำเนินการเสร็จสิ้นแล้ว
            <br>ไม่สามารถสแกนเพิ่มได้
        </p>
    </div>
</body>
</html>
