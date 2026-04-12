{{-- resources/views/products/_form.blade.php --}}
{{-- Dipakai oleh create.blade.php dan edit.blade.php --}}

<div class="row g-3">
    <div class="col-12">
        <label class="form-label fw-medium" style="font-size:13px">Nama Produk <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $product->name ?? '') }}" placeholder="Contoh: Nasi Goreng Spesial">
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label fw-medium" style="font-size:13px">Kategori <span class="text-danger">*</span></label>
        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
            <option value="">— Pilih Kategori —</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                {{ $cat->name }}
            </option>
            @endforeach
        </select>
        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-medium" style="font-size:13px">Harga Beli <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text bg-light">Rp</span>
            <input type="number" name="price_buy" class="form-control @error('price_buy') is-invalid @enderror"
                   value="{{ old('price_buy', $product->price_buy ?? '') }}" placeholder="0" min="0">
        </div>
        @error('price_buy')<div class="text-danger mt-1" style="font-size:12px">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-medium" style="font-size:13px">Harga Jual <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text bg-light">Rp</span>
            <input type="number" name="price_sell" class="form-control @error('price_sell') is-invalid @enderror"
                   value="{{ old('price_sell', $product->price_sell ?? '') }}" placeholder="0" min="0"
                   id="priceSell" oninput="calcMargin()">
        </div>
        @error('price_sell')<div class="text-danger mt-1" style="font-size:12px">{{ $message }}</div>@enderror
    </div>

    {{-- Margin preview --}}
    <div class="col-12">
        <div class="px-3 py-2 rounded" style="background:#f0fdf8; font-size:12px; color:#0f6e56" id="marginInfo">
            <i class="bi bi-info-circle me-1"></i>
            Margin: <span id="marginVal">—</span>
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-medium" style="font-size:13px">Stok Awal <span class="text-danger">*</span></label>
        <input type="number" name="stock" class="form-control @error('stock') is-invalid @enderror"
               value="{{ old('stock', $product->stock ?? '') }}" placeholder="0" min="0">
        @error('stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-medium" style="font-size:13px">Minimum Stok <span class="text-danger">*</span></label>
        <input type="number" name="min_stock" class="form-control @error('min_stock') is-invalid @enderror"
               value="{{ old('min_stock', $product->min_stock ?? 5) }}" placeholder="5" min="1">
        <div class="form-text" style="font-size:11px">Sistem akan memberi peringatan jika stok ≤ nilai ini</div>
        @error('min_stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<script>
function calcMargin() {
    const buy  = parseInt(document.querySelector('[name=price_buy]')?.value) || 0;
    const sell = parseInt(document.getElementById('priceSell')?.value) || 0;
    const margin = sell - buy;
    const pct    = buy > 0 ? ((margin / buy) * 100).toFixed(1) : 0;
    document.getElementById('marginVal').textContent =
        margin >= 0
            ? `Rp ${margin.toLocaleString('id-ID')} (${pct}%)`
            : '⚠ Harga jual lebih kecil dari harga beli!';
    document.getElementById('marginInfo').style.background = margin < 0 ? '#fff8e6' : '#f0fdf8';
    document.getElementById('marginInfo').style.color      = margin < 0 ? '#a16207' : '#0f6e56';
}
document.querySelector('[name=price_buy]')?.addEventListener('input', calcMargin);
calcMargin();
</script>
