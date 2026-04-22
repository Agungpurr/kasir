@extends('layouts.app')
@section('title', 'Kasir')

@push('styles')
<style>
.kasir-wrap { display: flex; gap: 16px; height: calc(100vh - 120px); }
.product-panel { flex: 1; display: flex; flex-direction: column; min-width: 0; }
.cart-panel { width: 340px; flex-shrink: 0; display: flex; flex-direction: column; background: #fff; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.07); }

/* Product grid */
.cat-pills { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 12px; }
.cat-pill { padding: 5px 14px; border-radius: 20px; border: 1.5px solid #e0e0e0; font-size: 12px; cursor: pointer; background: #fff; color: #555; transition: all .15s; }
.cat-pill.active { background: var(--primary); border-color: var(--primary); color: #fff; }
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 200px));
    gap: 12px;
    justify-content: start;
    overflow-y: auto;
    flex: 1;
}
.p-card { background: #fff; border: 1.5px solid #e9ecef; border-radius: 10px; padding: 12px; cursor: pointer; transition: all .15s; position: relative; }
.p-card:hover { border-color: var(--primary); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(29,158,117,.12); }
.p-card.sold-out { opacity: .5; cursor: not-allowed; pointer-events: none; }
.p-emoji { font-size: 28px; margin-bottom: 6px; }
.p-name { font-size: 12px; font-weight: 600; color: #1a1f2e; margin-bottom: 3px; line-height: 1.3; }
.p-price { font-size: 12px; color: var(--primary); font-weight: 600; }
.p-stock { font-size: 10px; color: #9ca3af; margin-top: 2px; }
.stock-pip { position: absolute; top: 7px; right: 7px; font-size: 9px; padding: 2px 6px; border-radius: 20px; }

/* Payment Method */
.pay-method { display: flex; gap: 8px; margin-bottom: 12px; }
.pay-btn { flex: 1; padding: 8px; border-radius: 8px; border: 1.5px solid #e0e0e0; background: #fff;
    font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center;
    justify-content: center; gap: 6px; transition: all .15s; color: #555; }
.pay-btn.active { border-color: var(--primary); background: #f0faf5; color: var(--primary); }
.pay-btn:hover:not(.active) { border-color: #aaa; }

/* QRIS Modal */
.qris-wrap { text-align: center; padding: 8px 0; }
.qris-code { width: 200px; height: 200px; margin: 12px auto; border-radius: 12px;
    border: 2px solid #e9ecef; padding: 8px; background: #fff; }
.qris-amount { font-size: 22px; font-weight: 700; color: var(--primary); margin: 8px 0 4px; }
.qris-status { display: inline-flex; align-items: center; gap: 6px; font-size: 12px;
    padding: 5px 14px; border-radius: 20px; margin-top: 8px; }
.qris-status.waiting { background: #fff8e1; color: #f59e0b; }
.qris-status.paid    { background: #e8f5e9; color: #16a34a; }

/* Cart */
.cart-header { padding: 14px 16px; border-bottom: 1px solid #f0f0f0; }
.cart-invoice { font-size: 11px; color: #9ca3af; margin-top: 2px; }
.cart-body { flex: 1; overflow-y: auto; }
.cart-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 120px; color: #9ca3af; font-size: 13px; }
.cart-item { display: flex; align-items: center; gap: 8px; padding: 9px 16px; border-bottom: 1px solid #f8f8f8; }
.ci-emoji { font-size: 20px; flex-shrink: 0; }
.ci-info { flex: 1; min-width: 0; }
.ci-name { font-size: 12px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ci-price { font-size: 11px; color: #9ca3af; }
.ci-qty { display: flex; align-items: center; gap: 5px; flex-shrink: 0; }
.qty-btn { width: 22px; height: 22px; border-radius: 4px; border: 1px solid #e0e0e0; background: #f8f9fa; cursor: pointer; font-size: 13px; display: flex; align-items: center; justify-content: center; line-height: 1; }
.qty-btn:hover { background: #e9ecef; }
.ci-sub { font-size: 12px; font-weight: 600; min-width: 64px; text-align: right; flex-shrink: 0; }
.cart-footer { padding: 14px 16px; border-top: 1px solid #f0f0f0; }
.total-row { display: flex; justify-content: space-between; font-size: 13px; padding: 2px 0; color: #555; }
.total-row.grand { font-size: 15px; font-weight: 700; color: #1a1f2e; border-top: 1px solid #e9ecef; padding-top: 8px; margin-top: 4px; }
</style>
@endpush

@section('content')
<div class="kasir-wrap">

    {{-- ══ PANEL PRODUK ══ --}}
    <div class="product-panel">
        {{-- Search --}}
        <div class="input-group mb-3">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" id="searchInput" class="form-control border-start-0 ps-0"
                   placeholder="Cari produk..." oninput="filterProducts()">
        </div>

        {{-- Kategori pills --}}
        <div class="cat-pills" id="catPills"></div>

        {{-- Grid produk --}}
        <div class="product-grid" id="productGrid"></div>
    </div>

    {{-- ══ PANEL KERANJANG ══ --}}
    <div class="cart-panel">
        <div class="cart-header">
            <div class="fw-semibold" style="font-size:14px">Keranjang</div>
            <div class="cart-invoice" id="invoiceDisplay">{{ $invoiceNumber }}</div>
        </div>

        <div class="cart-body" id="cartBody">
            <div class="cart-empty" id="cartEmpty">
                <i class="bi bi-cart3 fs-3 mb-1 opacity-25"></i>
                Belum ada produk dipilih
            </div>
        </div>

       <div class="cart-footer">
    <div class="total-row"><span>Subtotal</span><span id="subtotalDisp">Rp 0</span></div>
    <div class="total-row"><span>Diskon</span><span>Rp 0</span></div>
    <div class="total-row grand"><span>Total</span><span id="totalDisp">Rp 0</span></div>

    {{-- Pilihan metode --}}
    <div class="pay-method mt-3">
        <button class="pay-btn active" id="btnMethodTunai" onclick="setPayMethod('tunai')">
            <i class="bi bi-cash-coin"></i> Tunai
        </button>
        <button class="pay-btn" id="btnMethodQris" onclick="setPayMethod('qris')">
            <i class="bi bi-qr-code-scan"></i> QRIS
        </button>
    </div>

    {{-- Input uang tunai --}}
    <div id="tunaiSection">
        <label class="form-label" style="font-size:12px;color:#6c757d;margin-bottom:4px">Uang bayar</label>
        <input type="number" id="paymentInput" class="form-control form-control-sm"
               placeholder="Masukkan nominal..." oninput="calcChange()" min="0">
        <div class="d-flex justify-content-between align-items-center mt-2 mb-3 px-2 py-2 rounded"
             style="background:#f8f9fa;font-size:13px">
            <span>Kembalian</span>
            <span id="changeDisp" class="fw-semibold" style="color:var(--primary)">Rp 0</span>
        </div>
    </div>

    {{-- Info QRIS --}}
    <div id="qrisSection" style="display:none">
        <div class="d-flex justify-content-between align-items-center mb-3 px-2 py-2 rounded"
             style="background:#f0faf5;font-size:13px">
            <span>Scan QR untuk bayar</span>
            <i class="bi bi-qr-code text-success fs-5"></i>
        </div>
    </div>

    <button class="btn btn-primary w-100 fw-semibold" id="btnBayar" onclick="processPayment()" disabled>
        <i class="bi bi-check-circle me-1"></i> Bayar
    </button>
    <button class="btn btn-link w-100 text-muted mt-1" style="font-size:12px" onclick="clearCart()">
        Kosongkan keranjang
    </button>
</div>
    </div>
</div>

{{-- Modal Struk --}}
<div class="modal fade" id="modalStruk" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content border-0 rounded-3">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold">Transaksi Berhasil!</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="strukturContent"></div>
            <div class="modal-footer border-0 pt-0 gap-2">
                <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                <button class="btn btn-primary btn-sm" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i>Cetak
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal QRIS --}}
<div class="modal fade" id="modalQris" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold">Pembayaran QRIS</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="cancelQris()"></button>
            </div>
            <div class="modal-body qris-wrap">
                <div style="font-size:12px;color:#9ca3af">Shop – Scan & Bayar</div>
                <div class="qris-amount" id="qrisAmount">Rp 0</div>

                {{-- QR Code (gunakan Google Charts API sebagai generator QR) --}}
                <img id="qrisImg" class="qris-code" src="" alt="QR Code">

                <div id="qrisStatusBadge" class="qris-status waiting">
                    <span class="spinner-border spinner-border-sm"></span>
                    Menunggu pembayaran...
                </div>
                <div style="font-size:11px;color:#9ca3af;margin-top:10px">
                    QR berlaku <span id="qrisCountdown" class="fw-semibold">05:00</span>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 gap-2 flex-column">
                {{-- Tombol simulasi (development) - hapus di production --}}
                <button class="btn btn-success btn-sm w-100" onclick="simulatePaid()">
                    <i class="bi bi-check-circle me-1"></i> Simulasi Bayar (Dev)
                </button>
                <button class="btn btn-outline-secondary btn-sm w-100" data-bs-dismiss="modal" onclick="cancelQris()">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const allProducts = @json($products);
let cart = {}, activecat = 'Semua', payMethod = 'tunai';
let qrisTimer = null, qrisModal = null, qrisPaid = false;

const fmt = n => 'Rp ' + parseInt(n).toLocaleString('id-ID');

// ── Kategori
function buildCats() {
    const cats = ['Semua', ...new Set(allProducts.map(p => p.category?.name ?? 'Lainnya'))];
    document.getElementById('catPills').innerHTML = cats.map(c =>
        `<button class="cat-pill ${c === activecat ? 'active' : ''}" onclick="setcat('${c}')">${c}</button>`
    ).join('');
}
function setcat(c) { activecat = c; buildCats(); renderProducts(); }

// ── Produk
function renderProducts() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    const filtered = allProducts.filter(p => {
        const matchCat = activecat === 'Semua' || (p.category?.name ?? 'Lainnya') === activecat;
        return matchCat && p.name.toLowerCase().includes(q);
    });
    const grid = document.getElementById('productGrid');
    if (!filtered.length) {
        grid.innerHTML = `<div class="text-center text-muted py-4" style="grid-column:1/-1;font-size:13px">Produk tidak ditemukan</div>`;
        return;
    }
    grid.innerHTML = filtered.map(p => {
        const low = p.stock > 0 && p.stock <= 5, out = p.stock <= 0;
        return `<div class="p-card ${out ? 'sold-out' : ''}" onclick="addToCart(${p.id})">
            <div class="p-emoji">📦</div>
            <div class="p-name">${p.name}</div>
            <div class="p-price">${fmt(p.price_sell)}</div>
            <div class="p-stock">Stok: ${p.stock}</div>
            ${out ? '<span class="stock-pip badge-out">Habis</span>' : low ? '<span class="stock-pip badge-low">Tipis</span>' : ''}
        </div>`;
    }).join('');
}
function filterProducts() { renderProducts(); }

// ── Keranjang
function addToCart(id) {
    const p = allProducts.find(x => x.id === id);
    if (!p || p.stock <= 0) return;
    if (cart[id]) {
        if (cart[id].qty >= p.stock) { showToast('Stok tidak mencukupi', 'warning'); return; }
        cart[id].qty++;
    } else {
        cart[id] = { ...p, qty: 1 };
    }
    renderCart(); showToast(p.name + ' ditambahkan');
}
function changeQty(id, d) {
    if (!cart[id]) return;
    const p = allProducts.find(x => x.id == id);
    cart[id].qty += d;
    if (cart[id].qty <= 0) delete cart[id];
    else if (p && cart[id].qty > p.stock) { cart[id].qty = p.stock; showToast('Maks stok: ' + p.stock, 'warning'); }
    renderCart();
}
function renderCart() {
    const ids = Object.keys(cart);
    const body = document.getElementById('cartBody');
    body.querySelectorAll('.cart-item').forEach(el => el.remove());
    const empty = document.getElementById('cartEmpty');
    if (!ids.length) { empty.style.display = 'flex'; setTotals(0); return; }
    empty.style.display = 'none';
    let total = 0;
    ids.forEach(id => {
        const i = cart[id], sub = i.price_sell * i.qty;
        total += sub;
        const div = document.createElement('div');
        div.className = 'cart-item';
        div.innerHTML = `
            <span class="ci-emoji">📦</span>
            <div class="ci-info"><div class="ci-name">${i.name}</div><div class="ci-price">${fmt(i.price_sell)}</div></div>
            <div class="ci-qty">
                <button class="qty-btn" onclick="changeQty(${id},-1)">−</button>
                <span style="font-size:13px;font-weight:600;min-width:16px;text-align:center">${i.qty}</span>
                <button class="qty-btn" onclick="changeQty(${id},1)">+</button>
            </div>
            <div class="ci-sub">${fmt(sub)}</div>`;
        body.appendChild(div);
    });
    setTotals(total);
}
function setTotals(total) {
    document.getElementById('subtotalDisp').textContent = fmt(total);
    document.getElementById('totalDisp').textContent = fmt(total);
    calcChange();
    updateBayarBtn();
}
function calcChange() {
    const total = parseInt(document.getElementById('totalDisp').textContent.replace(/\D/g,'')) || 0;
    const pay = parseInt(document.getElementById('paymentInput').value) || 0;
    document.getElementById('changeDisp').textContent = fmt(pay >= total ? pay - total : 0);
    updateBayarBtn();
}
function updateBayarBtn() {
    const total = parseInt(document.getElementById('totalDisp').textContent.replace(/\D/g,'')) || 0;
    const pay = parseInt(document.getElementById('paymentInput')?.value) || 0;
    const btn = document.getElementById('btnBayar');
    if (payMethod === 'tunai') {
        btn.disabled = pay < total || total === 0;
    } else {
        btn.disabled = total === 0;
    }
}
function clearCart() {
    cart = {};
    document.getElementById('paymentInput').value = '';
    renderCart();
}

// ── Metode Bayar
function setPayMethod(m) {
    payMethod = m;
    document.getElementById('btnMethodTunai').classList.toggle('active', m === 'tunai');
    document.getElementById('btnMethodQris').classList.toggle('active', m === 'qris');
    document.getElementById('tunaiSection').style.display = m === 'tunai' ? 'block' : 'none';
    document.getElementById('qrisSection').style.display = m === 'qris' ? 'block' : 'none';
    updateBayarBtn();
}

// ── QRIS
function openQrisModal(total) {
    qrisPaid = false;
    // Generate QR dari Google Charts (ganti URL ini dengan QRIS string merchant asli di production)
    const qrisString = `00020101021126570011ID.DANA.WWW011893600915301048903902090104890390303UMI51440014ID.CO.QRIS.WWW0215ID10264980047810303UMI5204737253033605802ID5905agung6011Kota Bekasi610517158630473B6`;
    const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(qrisString)}`;
    document.getElementById('qrisImg').src = qrUrl;
    document.getElementById('qrisAmount').textContent = fmt(total);
    document.getElementById('qrisStatusBadge').className = 'qris-status waiting';
    document.getElementById('qrisStatusBadge').innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menunggu pembayaran...';

    // Countdown 5 menit
    let secs = 300;
    clearInterval(qrisTimer);
    qrisTimer = setInterval(() => {
        secs--;
        const m = String(Math.floor(secs/60)).padStart(2,'0');
        const s = String(secs%60).padStart(2,'0');
        document.getElementById('qrisCountdown').textContent = `${m}:${s}`;
        if (secs <= 0) { clearInterval(qrisTimer); if (!qrisPaid) cancelQris(); }
    }, 1000);

    qrisModal = new bootstrap.Modal(document.getElementById('modalQris'));
    qrisModal.show();
}

function cancelQris() {
    clearInterval(qrisTimer);
    qrisPaid = false;
}

// Simulasi pembayaran berhasil (hapus di production, ganti dengan webhook/polling real)
function simulatePaid() {
    qrisPaid = true;
    clearInterval(qrisTimer);
    document.getElementById('qrisStatusBadge').className = 'qris-status paid';
    document.getElementById('qrisStatusBadge').innerHTML = '<i class="bi bi-check-circle-fill"></i> Pembayaran Diterima!';
    setTimeout(() => {
        qrisModal.hide();
        submitTransaction(0, 0); // QRIS: kembalian 0, bayar = total
    }, 1500);
}

// ── Proses Bayar
async function processPayment() {
    const total = parseInt(document.getElementById('totalDisp').textContent.replace(/\D/g,'')) || 0;
    if (total === 0) return;

    if (payMethod === 'qris') {
        openQrisModal(total);
        return;
    }

    // Tunai
    const payment = parseInt(document.getElementById('paymentInput').value) || 0;
    if (payment < total) { showToast('Uang bayar kurang!', 'danger'); return; }
    await submitTransaction(payment, payment - total);
}

async function submitTransaction(payment, kembalian) {
    const total = parseInt(document.getElementById('totalDisp').textContent.replace(/\D/g,'')) || 0;
    const cartArr = Object.values(cart).map(i => ({ id: i.id, qty: i.qty }));
    const actualPayment = payMethod === 'qris' ? total : payment;

    document.getElementById('btnBayar').disabled = true;
    document.getElementById('btnBayar').innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';

    try {
        const res = await fetch('{{ route("transactions.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                cart: cartArr,
                total_payment: actualPayment,
                payment_method: payMethod,  // kirim ke backend
            }),
        });
        const data = await res.json();
        if (data.success) {
            showStruk(data.transaction, actualPayment, kembalian, payMethod);
            cart = {};
            document.getElementById('paymentInput').value = '';
            renderCart();
            document.getElementById('invoiceDisplay').textContent = data.invoice;
        } else {
            showToast(data.message, 'danger');
        }
    } catch (e) {
        showToast('Terjadi kesalahan, coba lagi.', 'danger');
    }

    document.getElementById('btnBayar').disabled = false;
    document.getElementById('btnBayar').innerHTML = '<i class="bi bi-check-circle me-1"></i> Bayar';
}

// ── Struk
function showStruk(trx, bayar, kembali, method) {
    const methodLabel = method === 'qris'
        ? '<span class="badge bg-success">QRIS</span>'
        : '<span class="badge bg-secondary">Tunai</span>';
    const items = trx.details.map(d =>
        `<tr><td>${d.product.name}</td><td class="text-end">${d.quantity}x</td><td class="text-end">Rp ${parseInt(d.subtotal).toLocaleString('id-ID')}</td></tr>`
    ).join('');
    document.getElementById('strukturContent').innerHTML = `
        <div class="text-center mb-3">
            <div class="fw-bold" style="font-size:13px">Shop</div>
            <div class="text-muted" style="font-size:11px">${trx.invoice_number}</div>
            <div class="mt-1">${methodLabel}</div>
        </div>
        <table class="table table-sm mb-2" style="font-size:12px">
            <tbody>${items}</tbody>
            <tfoot>
                <tr class="fw-bold"><td colspan="2">Total</td><td class="text-end">Rp ${parseInt(trx.total_price).toLocaleString('id-ID')}</td></tr>
                <tr><td colspan="2">Bayar</td><td class="text-end">Rp ${parseInt(bayar).toLocaleString('id-ID')}</td></tr>
                ${method !== 'qris' ? `<tr style="color:var(--primary)"><td colspan="2">Kembali</td><td class="text-end">Rp ${parseInt(kembali).toLocaleString('id-ID')}</td></tr>` : ''}
            </tfoot>
        </table>
        <div class="text-center text-muted" style="font-size:11px">Terima kasih!</div>`;
    new bootstrap.Modal(document.getElementById('modalStruk')).show();
}

// ── Toast
function showToast(msg, type = 'success') {
    const t = document.createElement('div');
    t.className = `alert alert-${type} position-fixed shadow`;
    t.style.cssText = 'bottom:20px;right:20px;z-index:9999;font-size:13px;padding:10px 16px;min-width:200px;border-radius:10px;transition:opacity .3s';
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 300); }, 2500);
}

// ── Init
buildCats(); renderProducts();
</script>
@endpush
