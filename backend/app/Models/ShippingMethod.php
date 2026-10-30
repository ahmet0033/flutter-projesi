<?php

namespace App\Models;

use Database\Factories\ShippingMethodFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    /** @use HasFactory<ShippingMethodFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'price_cents',
        'free_over_cents',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price_cents' => 'integer',
            'free_over_cents' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function priceFor(int $subtotalCents): int
    {
        if ($this->free_over_cents !== null && $subtotalCents >= $this->free_over_cents) {
            return 0;
        }

        return $this->price_cents;
    }
}
