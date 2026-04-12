<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // ─────────────────────────────────────────
    // Daftar produk (admin)
    // ─────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Product::with('category');

        // Filter pencarian
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter kategori
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter stok menipis
        if ($request->boolean('low_stock')) {
            $query->whereColumn('stock', '<=', 'min_stock');
        }

        $products   = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    // ─────────────────────────────────────────
    // Form tambah produk
    // ─────────────────────────────────────────
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('products.create', compact('categories'));
    }

    // ─────────────────────────────────────────
    // Simpan produk baru
    // ─────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255|unique:products,name',
            'price_buy'   => 'required|integer|min:0',
            'price_sell'  => 'required|integer|min:0|gte:price_buy',
            'stock'       => 'required|integer|min:0',
            'min_stock'   => 'required|integer|min:1',
        ], [
            'price_sell.gte' => 'Harga jual tidak boleh lebih kecil dari harga beli.',
            'name.unique'    => 'Nama produk sudah ada.',
        ]);

        Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    // ─────────────────────────────────────────
    // Form edit produk
    // ─────────────────────────────────────────
    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    // ─────────────────────────────────────────
    // Update produk
    // ─────────────────────────────────────────
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255|unique:products,name,' . $product->id,
            'price_buy'   => 'required|integer|min:0',
            'price_sell'  => 'required|integer|min:0|gte:price_buy',
            'stock'       => 'required|integer|min:0',
            'min_stock'   => 'required|integer|min:1',
        ], [
            'price_sell.gte' => 'Harga jual tidak boleh lebih kecil dari harga beli.',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    // ─────────────────────────────────────────
    // Hapus produk
    // ─────────────────────────────────────────
    public function destroy(Product $product)
    {
        // Cek apakah produk pernah ada di transaksi
        if ($product->transactionDetails()->exists()) {
            return redirect()->route('products.index')
                ->with('error', 'Produk tidak bisa dihapus karena sudah ada di riwayat transaksi.');
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    // ─────────────────────────────────────────
    // API: Cari produk untuk kasir (JSON)
    // ─────────────────────────────────────────
    public function search(Request $request)
    {
        $products = Product::with('category')
            ->where('stock', '>', 0)
            ->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%');
            })
            ->select('id', 'category_id', 'name', 'price_sell', 'stock')
            ->limit(20)
            ->get();

        return response()->json($products);
    }
}
