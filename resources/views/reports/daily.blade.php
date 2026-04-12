@extends('layouts.app')
@section('title', 'Laporan Harian')

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="mb-1 fw-bold">Laporan Harian</h5>
        <p class="text-muted mb-0" style="font-size:13px">{{ $date->isoFormat('dddd, D MMMM Y') }}</p>
    </div>
    <form method="GET" class="d-flex gap-2">
        <input type="date" name="date" class="form-control form-control-sm"
               value="{{ $date->format('Y-m-d') }}">
        <button class="btn btn-sm btn-primary">Tampilkan</button>
    </form>
</div>

{{-- Stat cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e6f9f2">
                <i class="bi bi-cash-stack" style="color:#1D9E75"></i>
            </div>
            <div>
                <div class="stat-label">Total Pendapatan</div>
                <div class="stat-value">Rp {{ number_format($summary['total_income'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:#eff6ff">
                <i class="bi bi-receipt" style="color:#3b82f6"></i>
            </div>
            <div>
                <div class="stat-label">Jumlah Transaksi</div>
                <div class="stat-value">{{ $summary['total_transactions'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fdf4ff">
                <i class="bi bi-graph-up" style="color:#a855f7"></i>
            </div>
            <div>
                <div class="stat-label">Rata-rata Transaksi</div>
                <div class="stat-value">Rp {{ number_format($summary['avg_transaction'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Tabel transaksi --}}
<div class="card">
    <div class="card-header p-3">
        <span class="fw-semibold" style="font-size:14px">Detail Transaksi</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>Invoice</th><th>Kasir</th><th>Items</th><th>Total</th><th>Bayar</th><th>Kembali</th><th>Waktu</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($transactions as $t)
                <tr>
                    <td class="fw-medium" style="color:var(--primary)">{{ $t->invoice_number }}</td>
                    <td>{{ $t->user->name }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $t->details->count() }} item</span></td>
                    <td class="fw-medium">Rp {{ number_format($t->total_price, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($t->total_payment, 0, ',', '.') }}</td>
                    <td style="color:#1D9E75">Rp {{ number_format($t->change, 0, ',', '.') }}</td>
                    <td class="text-muted">{{ $t->created_at->format('H:i') }}</td>
                    <td>
                        <a href="{{ route('transactions.show', $t) }}" class="btn btn-xs btn-outline-secondary" style="font-size:11px;padding:3px 8px">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        <i class="bi bi-calendar-x fs-3 d-block mb-2 opacity-25"></i>
                        Tidak ada transaksi pada tanggal ini
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
