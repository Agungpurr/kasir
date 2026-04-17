@extends('layouts.app')
@section('title', 'Manajemen Kategori')

@section('content')

<div class="row g-3">
    {{-- Form tambah --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header p-3">
                <span class="fw-semibold" style="font-size:14px">Tambah Kategori</span>
            </div>
            <div class="card-body">
                <form action="{{ route('categories.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-medium" style="font-size:13px">Nama Kategori</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="Contoh: Makanan">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-plus-lg me-1"></i>Tambah
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Daftar kategori --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header p-3">
                <span class="fw-semibold" style="font-size:14px">Daftar Kategori</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Kategori</th>
                            <th>Jumlah Produk</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $i => $cat)
                        <tr>
                            <td class="text-muted">{{ $i + 1 }}</td>
                            <td class="fw-medium">{{ $cat->name }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $cat->products_count }} produk</span></td>
                            <td>
                                {{-- Edit inline modal --}}
                                <button class="btn btn-xs btn-outline-primary me-1" style="font-size:11px;padding:3px 8px"
                                        data-bs-toggle="modal" data-bs-target="#editModal{{ $cat->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                
                                {{-- ⭐ PERBAIKAN 1: FORM DELETE ⭐ --}}
                                <form action="{{ url('/kategori/' . $cat->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Hapus kategori {{ $cat->name }}?')">
                                    @csrf 
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-outline-danger" style="font-size:11px;padding:3px 8px">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        {{-- Modal edit --}}
                        <div class="modal fade" id="editModal{{ $cat->id }}" tabindex="-1">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content border-0 rounded-3">
                                    <div class="modal-header border-0 pb-0">
                                        <h6 class="modal-title fw-bold">Edit Kategori</h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        {{-- ⭐ PERBAIKAN 2: FORM EDIT ⭐ --}}
                                        <form action="{{ url('/kategori/' . $cat->id) }}" method="POST">
                                            @csrf 
                                            @method('PUT')
                                            <div class="mb-3">
                                                <input type="text" name="name" class="form-control"
                                                       value="{{ $cat->name }}" required>
                                            </div>
                                            <button type="submit" class="btn btn-primary w-100 btn-sm">Simpan</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Belum ada kategori</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection