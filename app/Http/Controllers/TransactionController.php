<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    // ─────────────────────────────────────────
    // Halaman kasir utama
    // ─────────────────────────────────────────
    public function index()
    {
        $products = Product::with('category')
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get();

        $invoiceNumber = $this->generateInvoiceNumber();

        return view('transactions.index', compact('products', 'invoiceNumber'));
    }

    // ─────────────────────────────────────────
    // Proses pembayaran & simpan transaksi
    // ─────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'cart'          => 'required|array|min:1',
            'cart.*.id'     => 'required|exists:products,id',
            'cart.*.qty'    => 'required|integer|min:1',
            'total_payment' => 'required|integer|min:0',
            'payment_method' => 'required|in:tunai,qris',
        ]);

        $cart          = $request->cart;
        $invoiceNumber = $this->generateInvoiceNumber();

        try {
            DB::transaction(function () use ($cart, $request, $invoiceNumber) {

                $totalPrice = 0;

                // ── Validasi stok & hitung total ──
                foreach ($cart as $item) {
                    $product = Product::lockForUpdate()->findOrFail($item['id']);

                    if ($product->stock < $item['qty']) {
                        throw new \Exception("Stok {$product->name} tidak cukup. Tersisa: {$product->stock}");
                    }

                    $totalPrice += $product->price_sell * $item['qty'];
                }

                // ── Validasi uang bayar ──
                if ($request->total_payment < $totalPrice) {
                    throw new \Exception('Uang pembayaran kurang dari total belanja.');
                }

                // ── Simpan header transaksi ──
                $transaction = Transaction::create([
                    'invoice_number' => $invoiceNumber,
                    'user_id'        => Auth::id(),
                    'total_price'    => $totalPrice,
                    'total_payment'  => $request->total_payment,
                    'change'         => $request->total_payment - $totalPrice,
                    'payment_method' => $request->payment_method,
                ]);

                // ── Simpan detail & kurangi stok ──
                foreach ($cart as $item) {
                    $product = Product::find($item['id']);

                    TransactionDetail::create([
                        'transaction_id' => $transaction->id,
                        'product_id'     => $product->id,
                        'quantity'       => $item['qty'],
                        'price'          => $product->price_sell,
                        'subtotal'       => $product->price_sell * $item['qty'],
                    ]);

                    // Kurangi stok
                    $product->decrement('stock', $item['qty']);
                }
            });

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        // Ambil transaksi yang baru disimpan untuk struk
        $transaction = Transaction::with('details.product', 'user')
            ->where('invoice_number', $invoiceNumber)
            ->first();

        return response()->json([
            'success'     => true,
            'message'     => 'Transaksi berhasil!',
            'transaction' => $transaction,
            'invoice'     => $invoiceNumber,
        ]);
    }

    // ─────────────────────────────────────────
    // Detail transaksi / struk
    // ─────────────────────────────────────────
    public function show(Transaction $transaction)
    {
        $transaction->load('details.product', 'user');
        return view('transactions.show', compact('transaction'));
    }

    // ─────────────────────────────────────────
    // Riwayat transaksi (kasir hanya milik sendiri)
    // ─────────────────────────────────────────
    public function history(Request $request)
    {
        $query = Transaction::with('user')
            ->when(Auth::user()->role === 'kasir', fn($q) => $q->where('user_id', Auth::id()))
            ->when($request->filled('date'), fn($q) => $q->whereDate('created_at', $request->date))
            ->when($request->filled('invoice'), fn($q) => $q->where('invoice_number', 'like', '%' . $request->invoice . '%'));

        $transactions = $query->latest()->paginate(20)->withQueryString();

        return view('transactions.history', compact('transactions'));
    }

    // ─────────────────────────────────────────
    // Generate nomor invoice otomatis
    // Format: INV-YYYYMMDD-XXX
    // ─────────────────────────────────────────
    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . now()->format('Ymd') . '-';

        $lastToday = Transaction::where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('invoice_number');

        $nextNum = $lastToday
            ? (int) substr($lastToday, -3) + 1
            : 1;

        return $prefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
    }
}
