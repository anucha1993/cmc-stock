<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#1a73e8">
    <link rel="icon" type="image/png" href="{{ asset('logo1.png') }}">
    <title>สแกน - {{ $deliveryNote->delivery_number }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root {
            --app-blue: #1a73e8;
            --app-green: #34a853;
            --app-red: #ea4335;
            --app-yellow: #fbbc04;
            --app-bg: #f0f2f5;
            --safe-bottom: env(safe-area-inset-bottom, 0px);
        }
        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        html, body { margin: 0; padding: 0; height: 100%; overscroll-behavior: none; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--app-bg);
            color: #1f1f1f;
            padding-bottom: calc(80px + var(--safe-bottom));
        }

        /* ===== APP BAR ===== */
        .app-bar {
            background: var(--app-blue);
            color: #fff;
            padding: 14px 16px 10px;
            position: sticky; top: 0; z-index: 200;
            box-shadow: 0 2px 8px rgba(0,0,0,.15);
        }
        .app-bar .dn-number { font-size: 1.05rem; font-weight: 700; letter-spacing: .3px; }
        .app-bar .dn-meta { font-size: .78rem; opacity: .85; margin-top: 2px; }
        .app-bar .timer {
            background: rgba(255,255,255,.18);
            padding: 3px 10px; border-radius: 20px;
            font-size: .78rem; font-weight: 600;
            backdrop-filter: blur(4px);
        }
        .app-bar .timer.danger { background: var(--app-red); }

        /* ===== SCAN BAR (sticky) ===== */
        .scan-bar {
            position: sticky; top: 0; z-index: 190;
            background: #fff;
            padding: 10px 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,.08);
            border-bottom: 1px solid #e8eaed;
        }
        .scan-bar .input-wrap {
            display: flex; gap: 8px; align-items: center;
        }
        .scan-bar input {
            flex: 1;
            font-size: 1.15rem;
            font-weight: 600;
            text-align: center;
            border: 2.5px solid var(--app-blue);
            border-radius: 12px;
            padding: 10px 16px;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }
        .scan-bar input:focus {
            border-color: var(--app-green);
            box-shadow: 0 0 0 3px rgba(52,168,83,.2);
        }
        .scan-bar input::placeholder { color: #bbb; font-weight: 400; }
        .scan-bar .btn-scan {
            width: 48px; height: 48px; border-radius: 12px;
            background: var(--app-blue); color: #fff; border: none;
            font-size: 1.2rem; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            transition: background .15s;
        }
        .scan-bar .btn-scan:active { background: #1557b0; }

        /* ===== FEEDBACK FLASH ===== */
        .flash-overlay {
            position: fixed; inset: 0; z-index: 500;
            display: none; align-items: center; justify-content: center;
            pointer-events: none;
        }
        .flash-overlay.show { display: flex; animation: flashIn .5s ease-out forwards; }
        .flash-overlay .flash-icon {
            width: 90px; height: 90px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 2.4rem; color: #fff;
            box-shadow: 0 8px 32px rgba(0,0,0,.2);
        }
        .flash-overlay .flash-icon.ok { background: var(--app-green); }
        .flash-overlay .flash-icon.err { background: var(--app-red); }
        .flash-overlay .flash-icon.warn { background: var(--app-yellow); color: #333; }
        .flash-msg {
            margin-top: 12px; font-size: .95rem; font-weight: 600;
            background: rgba(0,0,0,.7); color: #fff;
            padding: 6px 18px; border-radius: 20px;
            max-width: 85vw; text-align: center;
        }
        @keyframes flashIn {
            0% { opacity: 0; transform: scale(.7); }
            40% { opacity: 1; transform: scale(1.05); }
            100% { opacity: 0; transform: scale(1); }
        }

        /* ===== PROGRESS SUMMARY ===== */
        .progress-summary {
            padding: 10px 14px;
            background: #fff;
            display: flex; align-items: center; gap: 12px;
            border-bottom: 1px solid #e8eaed;
        }
        .progress-summary .ring {
            width: 52px; height: 52px; flex-shrink: 0; position: relative;
        }
        .progress-summary .ring svg { transform: rotate(-90deg); }
        .progress-summary .ring-text {
            position: absolute; inset: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: .72rem; font-weight: 700; color: var(--app-blue);
        }
        .progress-summary .summary-text { font-size: .82rem; line-height: 1.4; }
        .progress-summary .summary-text strong { font-size: 1.1rem; }

        /* ===== ITEM CARDS ===== */
        .items-section { padding: 8px 12px; }
        .item-card {
            background: #fff;
            border-radius: 14px;
            margin-bottom: 10px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
            transition: transform .2s;
        }
        .item-card:active { transform: scale(.98); }
        .item-card .item-header {
            padding: 12px 14px 8px;
            display: flex; justify-content: space-between; align-items: flex-start;
        }
        .item-card .item-name { font-weight: 700; font-size: .92rem; line-height: 1.3; }
        .item-card .item-sku { font-size: .72rem; color: #888; margin-top: 1px; }
        .item-card .item-count {
            font-size: 1.3rem; font-weight: 800;
            white-space: nowrap; line-height: 1;
        }
        .item-card .item-count .sep { font-size: .85rem; color: #aaa; font-weight: 400; }
        .item-card .item-progress {
            height: 4px; background: #e8eaed; margin: 0 14px;
        }
        .item-card .item-progress .bar {
            height: 100%; border-radius: 2px;
            background: var(--app-blue);
            transition: width .4s ease;
        }
        .item-card .item-progress .bar.done { background: var(--app-green); }
        .item-card .item-progress .bar.over { background: var(--app-red); }

        /* scanned barcodes */
        .item-card .scanned-area {
            padding: 6px 14px 10px;
            display: flex; flex-wrap: wrap; gap: 5px;
        }
        .barcode-chip {
            display: inline-flex; align-items: center; gap: 4px;
            background: #e8f5e9; color: #2e7d32;
            font-size: .72rem; font-weight: 600;
            padding: 4px 8px 4px 10px;
            border-radius: 20px;
            transition: background .15s;
            cursor: pointer;
            -webkit-user-select: none; user-select: none;
        }
        .barcode-chip .chip-x {
            width: 18px; height: 18px; border-radius: 50%;
            background: rgba(0,0,0,.08);
            display: flex; align-items: center; justify-content: center;
            font-size: .65rem; color: #666;
            transition: background .15s, color .15s;
        }
        .barcode-chip:hover .chip-x, .barcode-chip:active .chip-x {
            background: var(--app-red); color: #fff;
        }
        .barcode-chip.removing {
            animation: chipOut .3s ease forwards;
        }
        @keyframes chipOut {
            to { opacity: 0; transform: scale(.6); max-width: 0; padding: 0; margin: 0; }
        }

        /* status badge on card */
        .item-card.completed { border-left: 4px solid var(--app-green); }
        .item-card.over { border-left: 4px solid var(--app-red); }
        .item-card.pending { border-left: 4px solid #e8eaed; }

        /* ===== CONFIRM MODAL ===== */
        .modal-backdrop-custom {
            position: fixed; inset: 0; z-index: 600;
            background: rgba(0,0,0,.45);
            display: none; align-items: flex-end; justify-content: center;
        }
        .modal-backdrop-custom.show { display: flex; }
        .modal-sheet {
            background: #fff; width: 100%; max-width: 420px;
            border-radius: 18px 18px 0 0;
            padding: 20px 20px calc(20px + var(--safe-bottom));
            animation: sheetUp .25s ease;
        }
        @keyframes sheetUp { from { transform: translateY(100%); } }
        .modal-sheet .sheet-title { font-size: 1rem; font-weight: 700; margin-bottom: 6px; }
        .modal-sheet .sheet-desc { font-size: .85rem; color: #666; margin-bottom: 16px; word-break: break-all; }
        .modal-sheet .sheet-actions { display: flex; gap: 10px; }
        .modal-sheet .sheet-actions button {
            flex: 1; padding: 12px; border: none; border-radius: 12px;
            font-size: .92rem; font-weight: 600; cursor: pointer;
        }
        .modal-sheet .btn-cancel { background: #f0f2f5; color: #333; }
        .modal-sheet .btn-confirm { background: var(--app-red); color: #fff; }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center; padding: 3rem 1rem; color: #aaa;
        }
        .empty-state i { font-size: 2.5rem; margin-bottom: .5rem; }

        /* Hide scrollbar */
        ::-webkit-scrollbar { width: 0; height: 0; }
    </style>
</head>
<body>

<!-- Flash feedback overlay -->
<div class="flash-overlay" id="flash">
    <div style="text-align:center">
        <div class="flash-icon" id="flash-icon"><i id="flash-i"></i></div>
        <div class="flash-msg" id="flash-msg"></div>
    </div>
</div>

<!-- Confirm delete bottom sheet -->
<div class="modal-backdrop-custom" id="confirm-modal" onclick="closeConfirm()">
    <div class="modal-sheet" onclick="event.stopPropagation()">
        <div class="sheet-title"><i class="fas fa-trash-alt text-danger"></i> ลบ Barcode ที่สแกน?</div>
        <div class="sheet-desc" id="confirm-desc">—</div>
        <div class="sheet-actions">
            <button class="btn-cancel" onclick="closeConfirm()">ยกเลิก</button>
            <button class="btn-confirm" id="btn-confirm-delete" onclick="doUnscan()">
                <i class="fas fa-trash"></i> ลบ
            </button>
        </div>
    </div>
</div>

<!-- ===== APP BAR ===== -->
<div class="app-bar">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <div class="dn-number"><i class="fas fa-barcode"></i> {{ $deliveryNote->delivery_number }}</div>
            <div class="dn-meta">
                <i class="fas fa-user"></i> {{ $deliveryNote->customer_name }}
                &middot; <i class="fas fa-calendar-alt"></i> {{ $deliveryNote->delivery_date->format('d/m/Y') }}
                @if($deliveryNote->sales_order_number)
                    <br><i class="fas fa-file-invoice"></i> ใบสั่งขาย: {{ $deliveryNote->sales_order_number }}
                @endif
            </div>
        </div>
        <div class="timer" id="timer">
            <i class="fas fa-clock"></i> <span id="countdown-text">--:--:--</span>
        </div>
    </div>
</div>

<!-- ===== SCAN BAR ===== -->
<div class="scan-bar">
    <div class="input-wrap">
        <input type="text"
               id="barcode-input"
               placeholder="ยิง Barcode ที่นี่"
               autocomplete="off"
               inputmode="none"
               autofocus>
        <button class="btn-scan" id="btn-manual-scan" type="button">
            <i class="fas fa-search"></i>
        </button>
    </div>
</div>

<!-- ===== PROGRESS SUMMARY ===== -->
@php
    $totalItems = $deliveryNote->items->sum('quantity');
    $totalScanned = $deliveryNote->items->sum('scanned_quantity');
    $pctAll = $totalItems > 0 ? round(($totalScanned / $totalItems) * 100) : 0;
@endphp
<div class="progress-summary">
    <div class="ring">
        <svg viewBox="0 0 36 36" width="52" height="52">
            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#e8eaed" stroke-width="3"/>
            <circle cx="18" cy="18" r="15.9" fill="none" stroke="var(--app-blue)" stroke-width="3"
                    stroke-dasharray="{{ $pctAll }}, 100" id="ring-circle"/>
        </svg>
        <div class="ring-text" id="ring-pct">{{ $pctAll }}%</div>
    </div>
    <div class="summary-text">
        สแกนแล้ว <strong><span id="total-scanned">{{ $totalScanned }}</span> / {{ $totalItems }}</strong> ชิ้น
        <br><span style="font-size:.75rem;color:#888">แตะ barcode บนรายการเพื่อลบ</span>
    </div>
</div>

<!-- ===== ITEMS LIST ===== -->
<div class="items-section" id="items-list">
    @foreach($deliveryNote->items as $item)
    @php
        $pct = $item->quantity > 0 ? min(($item->scanned_quantity / $item->quantity) * 100, 100) : 0;
        $isOver = $item->scanned_quantity > $item->quantity;
        $isDone = $item->scanned_quantity >= $item->quantity;
    @endphp
    <div class="item-card {{ $isDone ? ($isOver ? 'over' : 'completed') : 'pending' }}" id="item-{{ $item->id }}" data-product-id="{{ $item->product_id }}">
        <div class="item-header">
            <div>
                <div class="item-name">{{ $item->product->full_name }}</div>
                <div class="item-sku">SKU: {{ $item->product->sku }}</div>
            </div>
            <div class="item-count">
                <span id="count-{{ $item->id }}">{{ $item->scanned_quantity }}</span>
                <span class="sep">/</span>
                {{ $item->quantity }}
            </div>
        </div>
        <div class="item-progress"><div class="bar {{ $isDone ? ($isOver ? 'over' : 'done') : '' }}" id="progress-{{ $item->id }}" style="width:{{ $pct }}%"></div></div>
        <div class="scanned-area" id="scanned-{{ $item->id }}">
            @if($item->scanned_items && count($item->scanned_items) > 0)
                @foreach($item->scanned_items as $scanned)
                    <span class="barcode-chip" data-barcode="{{ $scanned['barcode'] }}" data-item-id="{{ $item->id }}" onclick="askUnscan(this)">
                        {{ $scanned['barcode'] }}
                        <span class="chip-x"><i class="fas fa-times"></i></span>
                    </span>
                @endforeach
            @endif
        </div>
    </div>
    @endforeach
</div>

@if($deliveryNote->items->count() === 0)
<div class="empty-state">
    <i class="fas fa-box-open"></i>
    <div>ไม่มีรายการสินค้า</div>
</div>
@endif

<script>
const SLUG = '{{ $deliveryNote->slug }}';
const TOKEN = '{{ $token }}';
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const TOTAL_QTY = {{ $totalItems }};
let isScanning = false;
let unscanTarget = null; // { barcode, itemId, chipEl }

// ===== Countdown =====
const expiresAt = new Date('{{ $deliveryNote->share_token_expires_at->toIso8601String() }}');
function updateCountdown() {
    const diff = expiresAt - new Date();
    if (diff <= 0) {
        document.getElementById('countdown-text').textContent = 'หมดอายุ';
        document.getElementById('timer').classList.add('danger');
        document.getElementById('barcode-input').disabled = true;
        document.getElementById('barcode-input').placeholder = 'ลิงก์หมดอายุแล้ว';
        return;
    }
    const h = String(Math.floor(diff / 3600000)).padStart(2, '0');
    const m = String(Math.floor((diff % 3600000) / 60000)).padStart(2, '0');
    const s = String(Math.floor((diff % 60000) / 1000)).padStart(2, '0');
    document.getElementById('countdown-text').textContent = `${h}:${m}:${s}`;
}
updateCountdown();
setInterval(updateCountdown, 1000);

// ===== Audio =====
const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
function playBeep(ok) {
    const osc = audioCtx.createOscillator();
    const gain = audioCtx.createGain();
    osc.connect(gain); gain.connect(audioCtx.destination);
    osc.frequency.value = ok ? 880 : 300;
    gain.gain.value = 0.3;
    osc.start(); osc.stop(audioCtx.currentTime + (ok ? 0.12 : 0.35));
}

// ===== Flash feedback =====
function flash(type, msg) {
    const el = document.getElementById('flash');
    const icon = document.getElementById('flash-icon');
    const iEl = document.getElementById('flash-i');
    const msgEl = document.getElementById('flash-msg');
    icon.className = 'flash-icon ' + type;
    iEl.className = type === 'ok' ? 'fas fa-check' : type === 'warn' ? 'fas fa-exclamation-triangle' : 'fas fa-times';
    msgEl.textContent = msg;
    el.classList.remove('show');
    void el.offsetWidth;
    el.classList.add('show');
    setTimeout(() => el.classList.remove('show'), 800);
}

// ===== Progress ring update =====
function updateRing() {
    let total = 0;
    document.querySelectorAll('[id^="count-"]').forEach(el => { total += parseInt(el.textContent) || 0; });
    document.getElementById('total-scanned').textContent = total;
    const pct = TOTAL_QTY > 0 ? Math.round((total / TOTAL_QTY) * 100) : 0;
    document.getElementById('ring-pct').textContent = pct + '%';
    document.getElementById('ring-circle').setAttribute('stroke-dasharray', pct + ', 100');
    if (pct >= 100) {
        document.getElementById('ring-circle').setAttribute('stroke', 'var(--app-green)');
        document.getElementById('ring-pct').style.color = 'var(--app-green)';
    } else {
        document.getElementById('ring-circle').setAttribute('stroke', 'var(--app-blue)');
        document.getElementById('ring-pct').style.color = 'var(--app-blue)';
    }
}

// ===== Scan input =====
const barcodeInput = document.getElementById('barcode-input');
barcodeInput.addEventListener('keypress', e => {
    if (e.key === 'Enter') { e.preventDefault(); submitScan(); }
});
document.getElementById('btn-manual-scan').addEventListener('click', submitScan);

// Keep focus on input
document.addEventListener('click', e => {
    if (!e.target.closest('.barcode-chip') && !e.target.closest('#confirm-modal') && !e.target.closest('.btn-scan')) {
        setTimeout(() => barcodeInput.focus(), 10);
    }
});

function submitScan() {
    const val = barcodeInput.value.trim();
    barcodeInput.value = '';
    barcodeInput.focus();
    if (!val || isScanning) return;
    doScan(val);
}

function doScan(barcode) {
    isScanning = true;
    fetch(`/dn/${SLUG}/scan`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ barcode, token: TOKEN }),
    })
    .then(r => {
        if (r.status === 403) {
            flash('err', 'ลิงก์หมดอายุ');
            barcodeInput.disabled = true;
            throw new Error('expired');
        }
        return r.json();
    })
    .then(data => {
        if (data.success) {
            flash(data.is_over_scanned ? 'warn' : 'ok', data.message);
            playBeep(true);
            addBarcodeToUI(data.data);
        } else {
            flash('err', data.message);
            playBeep(false);
        }
    })
    .catch(err => {
        if (err.message !== 'expired') { flash('err', 'เกิดข้อผิดพลาด'); playBeep(false); }
    })
    .finally(() => { isScanning = false; barcodeInput.focus(); });
}

function addBarcodeToUI(d) {
    const itemId = d.item_id;
    const card = document.getElementById(`item-${itemId}`);
    if (!card) return;

    // Update count
    const countEl = document.getElementById(`count-${itemId}`);
    countEl.textContent = d.scanned_quantity;

    // Update progress bar
    const pct = Math.min((d.scanned_quantity / d.total_quantity) * 100, 100);
    const bar = document.getElementById(`progress-${itemId}`);
    bar.style.width = pct + '%';
    bar.className = 'bar' + (d.scanned_quantity > d.total_quantity ? ' over' : d.scanned_quantity >= d.total_quantity ? ' done' : '');

    // Card state
    card.className = card.className.replace(/\b(completed|over|pending)\b/g, '').trim();
    if (d.scanned_quantity >= d.total_quantity) {
        card.classList.add(d.scanned_quantity > d.total_quantity ? 'over' : 'completed');
    } else {
        card.classList.add('pending');
    }

    // Add chip
    const area = document.getElementById(`scanned-${itemId}`);
    const chip = document.createElement('span');
    chip.className = 'barcode-chip';
    chip.dataset.barcode = d.barcode;
    chip.dataset.itemId = itemId;
    chip.onclick = function() { askUnscan(this); };
    chip.innerHTML = `${d.barcode} <span class="chip-x"><i class="fas fa-times"></i></span>`;
    area.appendChild(chip);

    // Animate card
    card.style.transition = 'transform .15s';
    card.style.transform = 'scale(1.02)';
    setTimeout(() => { card.style.transform = ''; }, 200);

    // Scroll into view
    card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

    updateRing();
}

// ===== Unscan =====
function askUnscan(chipEl) {
    unscanTarget = {
        barcode: chipEl.dataset.barcode,
        itemId: chipEl.dataset.itemId,
        chipEl: chipEl,
    };
    document.getElementById('confirm-desc').textContent = `Barcode: ${unscanTarget.barcode}`;
    document.getElementById('confirm-modal').classList.add('show');
}

function closeConfirm() {
    document.getElementById('confirm-modal').classList.remove('show');
    unscanTarget = null;
    barcodeInput.focus();
}

function doUnscan() {
    if (!unscanTarget) return;
    const { barcode, itemId, chipEl } = unscanTarget;
    const btn = document.getElementById('btn-confirm-delete');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch(`/dn/${SLUG}/unscan`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ barcode, item_id: itemId, token: TOKEN }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Animate remove chip
            chipEl.classList.add('removing');
            setTimeout(() => chipEl.remove(), 300);

            // Update count
            const d = data.data;
            document.getElementById(`count-${d.item_id}`).textContent = d.scanned_quantity;

            // Update progress
            const pct = Math.min((d.scanned_quantity / d.total_quantity) * 100, 100);
            const bar = document.getElementById(`progress-${d.item_id}`);
            bar.style.width = pct + '%';
            bar.className = 'bar' + (d.scanned_quantity > d.total_quantity ? ' over' : d.scanned_quantity >= d.total_quantity ? ' done' : '');

            // Card state
            const card = document.getElementById(`item-${d.item_id}`);
            card.className = card.className.replace(/\b(completed|over|pending)\b/g, '').trim();
            if (d.scanned_quantity >= d.total_quantity) {
                card.classList.add(d.scanned_quantity > d.total_quantity ? 'over' : 'completed');
            } else {
                card.classList.add('pending');
            }

            updateRing();
            flash('ok', 'ลบเรียบร้อย');
        } else {
            flash('err', data.message);
        }
    })
    .catch(() => flash('err', 'เกิดข้อผิดพลาด'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-trash"></i> ลบ';
        closeConfirm();
    });
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
