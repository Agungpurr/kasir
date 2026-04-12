@extends('layouts.app')
@section('title', 'Dashboard')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
@endpush

@section('content')

{{-- ── Stat Cards ── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e6f9f2">
                <i class="bi bi-currency-dollar" style="color:#1D9E75"></i>
            </div>
            <div>
                <div class="stat-label">Pendapatan Hari Ini</div>
                <div class="stat-value">Rp {{ number_format($stats['total_today'], 0, ',', '.') }}</div>
                <div class="stat-sub"><i class="bi bi-arrow-up"></i> Hari ini</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#eff6ff">
                <i class="bi bi-receipt" style="color:#3b82f6"></i>
            </div>
            <div>
                <div class="stat-label">Transaksi Hari Ini</div>
                <div class="stat-value">{{ $stats['count_today'] }}</div>
                <div class="stat-sub">transaksi selesai</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fdf4ff">
                <i class="bi bi-graph-up" style="color:#a855f7"></i>
            </div>
            <div>
                <div class="stat-label">Pendapatan Bulan Ini</div>
                <div class="stat-value">Rp {{ number_format($stats['total_month'], 0, ',', '.') }}</div>
                <div class="stat-sub">bulan {{ now()->isoFormat('MMMM') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fff8e6">
                <i class="bi bi-exclamation-triangle" style="color:#f59e0b"></i>
            </div>
            <div>
                <div class="stat-label">Stok Menipis</div>
                <div class="stat-value">{{ $stats['low_stock'] }}</div>
                <div class="stat-sub">produk perlu restock</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- ── Grafik 7 hari ── --}}
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between p-3">
                <span class="fw-semibold" style="font-size:14px">Pendapatan 7 Hari Terakhir</span>
            </div>
            <div class="card-body">
                <canvas id="chartRevenue" height="90"></canvas>
            </div>
        </div>
    </div>

    {{-- ── Stok menipis ── --}}
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between p-3">
                <span class="fw-semibold" style="font-size:14px">Stok Menipis</span>
                <a href="{{ route('products.index') }}?low_stock=1" class="btn btn-sm btn-outline-primary" style="font-size:11px">Lihat semua</a>
            </div>
            <div class="card-body p-0">
                @forelse($lowStockProducts as $p)
                <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                    <div>
                        <div style="font-size:13px; font-weight:500">{{ $p->name }}</div>
                        <div style="font-size:11px; color:#6c757d">{{ $p->category->name }}</div>
                    </div>
                    <span class="badge {{ $p->stock == 0 ? 'badge-out' : 'badge-low' }}" style="font-size:11px">
                        Stok: {{ $p->stock }}
                    </span>
                </div>
                @empty
                <div class="text-center py-4 text-muted" style="font-size:13px">
                    <i class="bi bi-check-circle fs-4 d-block mb-1"></i>
                    Semua stok aman
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── Transaksi terbaru ── --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between p-3">
                <span class="fw-semibold" style="font-size:14px">Transaksi Terbaru</span>
                <a href="{{ route('transactions.history') }}" class="btn btn-sm btn-outline-primary" style="font-size:11px">Lihat semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Invoice</th><th>Kasir</th><th>Total</th>
                            <th>Bayar</th><th>Kembali</th><th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransactions as $t)
                        <tr>
                            <td><a href="{{ route('transactions.show', $t) }}" class="text-decoration-none fw-medium" style="color:var(--primary)">{{ $t->invoice_number }}</a></td>
                            <td>{{ $t->user->name }}</td>
                            <td class="fw-medium">Rp {{ number_format($t->total_price, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($t->total_payment, 0, ',', '.') }}</td>
                            <td style="color:#1D9E75">Rp {{ number_format($t->change, 0, ',', '.') }}</td>
                            <td class="text-muted">{{ $t->created_at->format('H:i') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada transaksi hari ini</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const chartData = @json($chartData);
const labels = [], revenues = [];
for (let i = 6; i >= 0; i--) {
    const d = new Date(); d.setDate(d.getDate() - i);
    const key = d.toISOString().split('T')[0];
    labels.push(d.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric' }));
    revenues.push(chartData[key]?.total ?? 0);
}
new Chart(document.getElementById('chartRevenue'), {
    type: 'bar',
    data: {
        labels,
        datasets: [{
            label: 'Pendapatan',
            data: revenues,
            backgroundColor: 'rgba(29,158,117,.15)',
            borderColor: '#1D9E75',
            borderWidth: 2,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                ticks: { callback: v => 'Rp ' + (v/1000).toFixed(0) + 'k', font: { size: 11 } },
                grid: { color: '#f0f0f0' }
            },
            x: { grid: { display: false }, ticks: { font: { size: 11 } } }
        }
    }
});
</script>
@endpush
