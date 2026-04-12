@extends('layouts.app')
@section('title', 'Struk ' . $transaction->invoice_number)

@push('styles')
<style>
@media print {
    #sidebar, .topbar, .no-print { display: none !important; }
    #main-wrap { margin-left: 0 !important; }
    .main-content { padding: 0 !important; }
    .struk-wrap { max-width: 320px; margin: 0 auto; }
}
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-5">
        <div class="d-flex align-items-center gap-3 mb-4 no-print">
            <a href="{{ route('transactions.history') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h5 class="fw-bold mb-0">Detail Struk</h5>
            <button class="btn btn-sm btn-outline-primary ms-auto" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Cetak
            </button>
        </div>

        <div class="card struk-wrap">
            <div class="card-body p-4">
                {{-- Header struk --}}
                <div class="text-center mb-4">
                    <div class="fw-bold fs-5">SmartPOS</div>
                    <div class="text-muted" style="font-size:12px">Kasir Digital</div>
                    <hr>
                    <div style="font-size:13px">
                        <div class="fw-bold">{{ $transaction->invoice_number }}</div>
                        <div class="text-muted">{{ $transaction->created_at->format('d/m/Y H:i:s') }}</div>
                        <div class="text-muted">Kasir: {{ $transaction->user->name }}</div>
                    </div>
                </div>

                {{-- Items --}}
                <table class="table table-sm" style="font-size:13px">
                    <tbody>
                        @foreach($transaction->details as $d)
                        <tr>
                            <td class="border-0 ps-0">
                                {{ $d->product->name }}<br>
                                <small class="text-muted">{{ $d->quantity }} × Rp {{ number_format($d->price, 0, ',', '.') }}</small>
                            </td>
                            <td class="border-0 pe-0 text-end fw-medium">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-top">
                            <td class="ps-0 fw-bold">Total</td>
                            <td class="pe-0 text-end fw-bold">Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="ps-0 text-muted" style="font-size:12px">Bayar</td>
                            <td class="pe-0 text-end text-muted" style="font-size:12px">Rp {{ number_format($transaction->total_payment, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="ps-0 fw-medium" style="color:var(--primary)">Kembali</td>
                            <td class="pe-0 text-end fw-medium" style="color:var(--primary)">Rp {{ number_format($transaction->change, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>

                <hr>
                <div class="text-center text-muted" style="font-size:12px">
                    Terima kasih telah berbelanja!
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
