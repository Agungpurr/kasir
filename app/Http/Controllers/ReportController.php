<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // ─────────────────────────────────────────
    // Dashboard ringkasan hari ini
    // ─────────────────────────────────────────
    public function dashboard()
    {
        $today = today();

        // ── Stat cards ──
        $stats = [
            'total_today'  => Transaction::whereDate('created_at', $today)->sum('total_price'),
            'count_today'  => Transaction::whereDate('created_at', $today)->count(),
            'total_month'  => Transaction::whereMonth('created_at', $today->month)
                                         ->whereYear('created_at', $today->year)
                                         ->sum('total_price'),
            'low_stock'    => Product::whereColumn('stock', '<=', 'min_stock')->count(),
        ];

        // ── 7 transaksi terakhir ──
        $recentTransactions = Transaction::with('user')
            ->latest()
            ->limit(7)
            ->get();

        // ── Grafik 7 hari terakhir ──
        $chartData = Transaction::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_price) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // ── Produk stok menipis ──
        $lowStockProducts = Product::with('category')
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock')
            ->limit(5)
            ->get();

        return view('reports.dashboard', compact(
            'stats',
            'recentTransactions',
            'chartData',
            'lowStockProducts'
        ));
    }

    // ─────────────────────────────────────────
    // Laporan harian — filter by tanggal
    // ─────────────────────────────────────────
    public function daily(Request $request)
    {
        $date = $request->filled('date')
            ? \Carbon\Carbon::parse($request->date)
            : today();

        $transactions = Transaction::with('user', 'details')
            ->whereDate('created_at', $date)
            ->latest()
            ->get();

        $summary = [
            'total_income'       => $transactions->sum('total_price'),
            'total_transactions' => $transactions->count(),
            'avg_transaction'    => $transactions->count()
                ? round($transactions->avg('total_price'))
                : 0,
        ];

        return view('reports.daily', compact('transactions', 'summary', 'date'));
    }

    // ─────────────────────────────────────────
    // Laporan bulanan — filter by bulan & tahun
    // ─────────────────────────────────────────
    public function monthly(Request $request)
    {
        $month = $request->integer('month', now()->month);
        $year  = $request->integer('year',  now()->year);

        // Ringkasan per hari dalam bulan
        $dailySummary = Transaction::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_price) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $summary = [
            'total_income'       => $dailySummary->sum('total'),
            'total_transactions' => $dailySummary->sum('count'),
            'best_day'           => $dailySummary->sortByDesc('total')->first(),
        ];

        return view('reports.monthly', compact('dailySummary', 'summary', 'month', 'year'));
    }

    // ─────────────────────────────────────────
    // Produk terlaris
    // ─────────────────────────────────────────
    public function bestSelling(Request $request)
    {
        $startDate = $request->filled('start_date')
            ? \Carbon\Carbon::parse($request->start_date)->startOfDay()
            : now()->startOfMonth();

        $endDate = $request->filled('end_date')
            ? \Carbon\Carbon::parse($request->end_date)->endOfDay()
            : now()->endOfDay();

        $products = TransactionDetail::select(
                'product_id',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(subtotal) as total_revenue')
            )
            ->with('product.category')
            ->whereHas('transaction', fn($q) =>
                $q->whereBetween('created_at', [$startDate, $endDate])
            )
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(20)
            ->get();

        return view('reports.best-selling', compact('products', 'startDate', 'endDate'));
    }
}
