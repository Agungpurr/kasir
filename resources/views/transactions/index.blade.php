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
.product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 10px; overflow-y: auto; flex: 1; padding-right: 4px; }
.p-card { background: #fff; border: 1.5px solid #e9ecef; border-radius: 10px; padding: 12px; cursor: pointer; transition: all .15s; position: relative; }
.p-card:hover { border-color: var(--primary); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(29,158,117,.12); }
.p-card.sold-out { opacity: .5; cursor: not-allowed; pointer-events: none; }
.p-emoji { font-size: 28px; margin-bottom: 6px; }
.p-name { font-size: 12px; font-weight: 600; color: #1a1f2e; margin-bottom: 3px; line-height: 1.3; }
.p-price { font-size: 12px; color: var(--primary); font-weight: 600; }
.p-stock { font-size: 10px; color: #9ca3af; margin-top: 2px; }
.stock-pip { position: absolute; top: 7px; right: 7px; font-size: 9px; padding: 2px 6px; border-radius: 20px; }

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

            <div class="mt-3 mb-2">
                <label class="form-label" style="font-size:12px; color:#6c757d; margin-bottom:4px">Uang bayar</label>
                <input type="number" id="paymentInput" class="form-control form-control-sm"
                       placeholder="Masukkan nominal..." oninput="calcChange()" min="0">
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3 px-2 py-2 rounded"
                 style="background:#f8f9fa; font-size:13px">
                <span>Kembalian</span>
                <span id="changeDisp" class="fw-semibold" style="color:var(--primary)">Rp 0</span>
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
@endsection

@push('scripts')
<script>
const allProducts = @json($products);
let cart = {}, activecat = 'Semua';

const fmt = n => 'Rp ' + parseInt(n).toLocaleString('id-ID');

// ── Build kategori pills
function buildCats() {
    const cats = ['Semua', ...new Set(allProducts.map(p => p.category?.name ?? 'Lainnya'))];
    document.getElementById('catPills').innerHTML = cats.map(c =>
        `<button class="cat-pill ${c === activecat ? 'active' : ''}" onclick="setcat('${c}')">${c}</button>`
    ).join('');
}

function setcat(c) { activecat = c; buildCats(); renderProducts(); }

// ── Render produk
function renderProducts() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    const filtered = allProducts.filter(p => {
        const matchCat = activecat === 'Semua' || (p.category?.name ?? 'Lainnya') === activecat;
        const matchQ   = p.name.toLowerCase().includes(q);
        return matchCat && matchQ;
    });
    const grid = document.getElementById('productGrid');
    if (!filtered.length) {
        grid.innerHTML = `<div class="text-center text-muted py-4" style="grid-column:1/-1;font-size:13px">Produk tidak ditemukan</div>`;
        return;
    }
    grid.innerHTML = filtered.map(p => {
        const low = p.stock > 0 && p.stock <= 5;
        const out = p.stock <= 0;
        return `<div class="p-card ${out ? 'sold-out' : ''}" onclick="addToCart(${p.id})">
            <div class="p-emoji">📦</div>
            <div class="p-name">${p.name}</div>
            <div class="p-price">${fmt(p.price_sell)}</div>
            <div class="p-stock">Stok: ${p.stock}</div>
            ${out ? '<span class="stock-pip badge-out">Habis</span>'
                  : low ? '<span class="stock-pip badge-low">Tipis</span>'
                        : ''}
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
    const empty = document.getElementById('cartEmpty');
    const body  = document.getElementById('cartBody');

    // hapus item lama kecuali empty
    body.querySelectorAll('.cart-item').forEach(el => el.remove());

    if (!ids.length) {
        empty.style.display = 'flex';
        setTotals(0); return;
    }
    empty.style.display = 'none';

    let total = 0;
    ids.forEach(id => {
        const i = cart[id];
        const sub = i.price_sell * i.qty;
        total += sub;
        const div = document.createElement('div');
        div.className = 'cart-item';
        div.innerHTML = `
            <span class="ci-emoji">📦</span>
            <div class="ci-info">
                <div class="ci-name">${i.name}</div>
                <div class="ci-price">${fmt(i.price_sell)}</div>
            </div>
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
    document.getElementById('totalDisp').textContent    = fmt(total);
    calcChange();
    document.getElementById('btnBayar').disabled = (total === 0);
}

function calcChange() {
    const total = parseInt(document.getElementById('totalDisp').textContent.replace(/\D/g,'')) || 0;
    const pay   = parseInt(document.getElementById('paymentInput').value) || 0;
    const change = pay - total;
    document.getElementById('changeDisp').textContent = fmt(change >= 0 ? change : 0);
    document.getElementById('btnBayar').disabled = (pay < total || total === 0);
}

function clearCart() {
    cart = {};
    document.getElementById('paymentInput').value = '';
    renderCart();
}

// ── Proses bayar
async function processPayment() {
    const total   = parseInt(document.getElementById('totalDisp').textContent.replace(/\D/g,'')) || 0;
    const payment = parseInt(document.getElementById('paymentInput').value) || 0;
    if (payment < total) { showToast('Uang bayar kurang!', 'danger'); return; }

    const cartArr = Object.values(cart).map(i => ({ id: i.id, qty: i.qty }));

    document.getElementById('btnBayar').disabled = true;
    document.getElementById('btnBayar').innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';

    try {
        const res = await fetch('{{ route("transactions.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ cart: cartArr, total_payment: payment }),
        });
        const data = await res.json();

        if (data.success) {
            showStruk(data.transaction, payment, payment - total);
            cart = {};
            document.getElementById('paymentInput').value = '';
            renderCart();
            // Update invoice number
            const now = new Date();
            const d = now.toISOString().slice(0,10).replace(/-/g,'');
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

function showStruk(trx, bayar, kembali) {
    const items = trx.details.map(d =>
        `<tr><td>${d.product.name}</td><td class="text-end">${d.quantity}x</td><td class="text-end">Rp ${parseInt(d.subtotal).toLocaleString('id-ID')}</td></tr>`
    ).join('');
    document.getElementById('strukturContent').innerHTML = `
        <div class="text-center mb-3">
            <div class="fw-bold" style="font-size:13px">SmartPOS</div>
            <div class="text-muted" style="font-size:11px">${trx.invoice_number}</div>
        </div>
        <table class="table table-sm mb-2" style="font-size:12px">
            <tbody>${items}</tbody>
            <tfoot>
                <tr class="fw-bold"><td colspan="2">Total</td><td class="text-end">Rp ${parseInt(trx.total_price).toLocaleString('id-ID')}</td></tr>
                <tr><td colspan="2">Bayar</td><td class="text-end">Rp ${parseInt(bayar).toLocaleString('id-ID')}</td></tr>
                <tr style="color:var(--primary)"><td colspan="2">Kembali</td><td class="text-end">Rp ${parseInt(kembali).toLocaleString('id-ID')}</td></tr>
            </tfoot>
        </table>
        <div class="text-center text-muted" style="font-size:11px">Terima kasih!</div>`;
    new bootstrap.Modal(document.getElementById('modalStruk')).show();
}

// ── Toast notifikasi
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
