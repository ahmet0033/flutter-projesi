<?php

namespace App\Models;

use Database\Factories\ProductVariantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    /** @use HasFactory<ProductVariantFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'price_cents',
        'stock_quantity',
        'options',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_cents' => 'integer',
            'stock_quantity' => 'integer',
            'options' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function effectivePriceCents(): int
    {
        return $this->price_cents ?? $this->product->price_cents;
    }

    public function inStock(): bool
    {
        return $this->stock_quantity > 0;
    }
}
