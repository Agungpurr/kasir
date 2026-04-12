@extends('layouts.app')
@section('title', 'Produk Terlaris')

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="mb-1 fw-bold">Produk Terlaris</h5>
        <p class="text-muted mb-0" style="font-size:13px">
            {{ $startDate->isoFormat('D MMM Y') }} – {{ $endDate->isoFormat('D MMM Y') }}
        </p>
    </div>
    <form method="GET" class="d-flex gap-2">
        <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}"
               class="form-control form-control-sm">
        <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}"
               class="form-control form-control-sm">
        <button class="btn btn-sm btn-primary">Tampilkan</button>
    </form>
</div>

{{-- Tabel produk terlaris --}}
<div class="card">
    <div class="card-header p-3">
        <span class="fw-semibold" style="font-size:14px">Ranking Produk</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width:50px">#</th>
                    <th>Produk</th>
                    <th>Kategori</th>
                    <th class="text-center">Total Terjual</th>
                    <th class="text-end">Total Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $index => $item)
                <tr>
                    <td class="text-center">
                        @if($index == 0)
                            <i class="bi bi-trophy-fill" style="color:#f59e0b"></i>
                        @elseif($index == 1)
                            <i class="bi bi-trophy-fill" style="color:#94a3b8"></i>
                        @elseif($index == 2)
                            <i class="bi bi-trophy-fill" style="color:#b45309"></i>
                        @else
                            <span class="text-muted">{{ $index + 1 }}</span>
                        @endif
                    </td>
                    <td class="fw-medium">{{ $item->product->name ?? '-' }}</td>
                    <td>
                        <span class="badge bg-light text-dark border">
                            {{ $item->product->category->name ?? '-' }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark border">{{ $item->total_qty }} pcs</span>
                    </td>
                    <td class="text-end fw-medium" style="color:#1D9E75">
                        Rp {{ number_format($item->total_revenue, 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-5">
                        <i class="bi bi-bar-chart fs-3 d-block mb-2 opacity-25"></i>
                        Tidak ada data pada periode ini
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection