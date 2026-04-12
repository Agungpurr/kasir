@extends('layouts.app')
@section('title', 'Laporan Bulanan')

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="mb-1 fw-bold">Laporan Bulanan</h5>
        <p class="text-muted mb-0" style="font-size:13px">
            {{ \Carbon\Carbon::create($year, $month)->isoFormat('MMMM Y') }}
        </p>
    </div>
    <form method="GET" class="d-flex gap-2">
        <select name="month" class="form-select form-select-sm">
            @for ($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" @selected($m == $month)>
                    {{ \Carbon\Carbon::create()->month($m)->isoFormat('MMMM') }}
                </option>
            @endfor
        </select>
        <input type="number" name="year" value="{{ $year }}" min="2020" max="2099"
               class="form-control form-control-sm" style="width:90px">
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
                <div class="stat-label">Total Transaksi</div>
                <div class="stat-value">{{ $summary['total_transactions'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fdf4ff">
                <i class="bi bi-trophy" style="color:#a855f7"></i>
            </div>
            <div>
                <div class="stat-label">Hari Terbaik</div>
                <div class="stat-value" style="font-size:15px">
                    @if($summary['best_day'])
                        {{ \Carbon\Carbon::parse($summary['best_day']->date)->isoFormat('D MMM') }}
                        <small class="text-muted fw-normal" style="font-size:11px">
                            Rp {{ number_format($summary['best_day']->total, 0, ',', '.') }}
                        </small>
                    @else
                        -
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tabel per hari --}}
<div class="card">
    <div class="card-header p-3">
        <span class="fw-semibold" style="font-size:14px">Ringkasan Per Hari</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th class="text-center">Jumlah Transaksi</th>
                    <th class="text-end">Total Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dailySummary as $row)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($row->date)->isoFormat('dddd, D MMMM Y') }}</td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark border">{{ $row->count }} transaksi</span>
                    </td>
                    <td class="text-end fw-medium" style="color:#1D9E75">
                        Rp {{ number_format($row->total, 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center text-muted py-5">
                        <i class="bi bi-calendar-x fs-3 d-block mb-2 opacity-25"></i>
                        Tidak ada transaksi bulan ini
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection