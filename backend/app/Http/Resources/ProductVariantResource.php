<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'price_cents' => $this->price_cents,
            'price' => $this->price_cents === null
                ? null
                : '$'.number_format($this->price_cents / 100, 2),
            'stock_quantity' => $this->stock_quantity,
            'in_stock' => $this->inStock(),
            'options' => $this->options ?? [],
        ];
    }
}
