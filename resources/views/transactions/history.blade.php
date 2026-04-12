{{-- transactions/history.blade.php --}}
@extends('layouts.app')
@section('title', 'Riwayat Transaksi')

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <h5 class="fw-bold mb-0">Riwayat Transaksi</h5>
    <form method="GET" class="d-flex gap-2">
        <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}" placeholder="Filter tanggal">
        <input type="text" name="invoice" class="form-control form-control-sm" value="{{ request('invoice') }}" placeholder="Cari invoice...">
        <button class="btn btn-sm btn-primary">Cari</button>
        <a href="{{ route('transactions.history') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>Invoice</th><th>Kasir</th><th>Total</th><th>Bayar</th><th>Kembali</th><th>Tanggal</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($transactions as $t)
                <tr>
                    <td><span class="fw-medium" style="color:var(--primary)">{{ $t->invoice_number }}</span></td>
                    <td>{{ $t->user->name }}</td>
                    <td class="fw-medium">Rp {{ number_format($t->total_price, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($t->total_payment, 0, ',', '.') }}</td>
                    <td style="color:#1D9E75">Rp {{ number_format($t->change, 0, ',', '.') }}</td>
                    <td class="text-muted">{{ $t->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{{ route('transactions.show', $t) }}" class="btn btn-xs btn-outline-primary" style="font-size:11px;padding:3px 8px">
                            <i class="bi bi-eye me-1"></i>Struk
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-5">Belum ada transaksi</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transactions->hasPages())
    <div class="card-footer bg-white py-2">
        {{ $transactions->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
