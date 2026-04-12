<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'price_buy',
        'price_sell',
        'stock',
        'min_stock',
    ];

    // ── Relasi ──
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    // ── Helper: apakah stok menipis? ──
    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock && $this->stock > 0;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock <= 0;
    }

    // ── Accessor: margin laba ──
    public function getProfitMarginAttribute(): int
    {
        return $this->price_sell - $this->price_buy;
    }
}
