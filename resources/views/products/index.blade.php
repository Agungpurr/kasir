@extends('layouts.app')
@section('title', 'Manajemen Produk')

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="mb-1 fw-bold">Produk</h5>
        <p class="text-muted mb-0" style="font-size:13px">{{ $products->total() }} produk terdaftar</p>
    </div>
    <a href="{{ route('products.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Tambah Produk
    </a>
</div>

{{-- Filter bar --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0"
                           placeholder="Cari nama produk..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <div class="form-check form-check-sm mb-0">
                    <input class="form-check-input" type="checkbox" name="low_stock" value="1" id="chkLow"
                           {{ request('low_stock') ? 'checked' : '' }}>
                    <label class="form-check-label" for="chkLow" style="font-size:13px">Stok menipis</label>
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary ms-1">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Tabel --}}
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th><th>Nama Produk</th><th>Kategori</th>
                    <th>Harga Beli</th><th>Harga Jual</th>
                    <th>Margin</th><th>Stok</th><th>Status</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $i => $p)
                <tr>
                    <td class="text-muted">{{ $products->firstItem() + $i }}</td>
                    <td class="fw-medium">{{ $p->name }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $p->category->name }}</span></td>
                    <td>Rp {{ number_format($p->price_buy, 0, ',', '.') }}</td>
                    <td class="fw-medium">Rp {{ number_format($p->price_sell, 0, ',', '.') }}</td>
                    <td style="color:var(--primary)">Rp {{ number_format($p->profit_margin, 0, ',', '.') }}</td>
                    <td>{{ $p->stock }}</td>
                    <td>
                        @if($p->stock == 0)
                            <span class="badge badge-out">Habis</span>
                        @elseif($p->isLowStock())
                            <span class="badge badge-low">Menipis</span>
                        @else
                            <span class="badge badge-ok">Tersedia</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('products.edit', $p) }}" class="btn btn-xs btn-outline-primary me-1" style="font-size:11px;padding:3px 8px">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('products.destroy', $p) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Hapus produk {{ $p->name }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-outline-danger" style="font-size:11px;padding:3px 8px">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-5">
                        <i class="bi bi-box-seam fs-3 d-block mb-2 opacity-25"></i>
                        Belum ada produk
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center py-2">
        <span class="text-muted" style="font-size:12px">
            Menampilkan {{ $products->firstItem() }}–{{ $products->lastItem() }} dari {{ $products->total() }}
        </span>
        {{ $products->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
