<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'unit_price_cents' => $this->unitPriceCents(),
            'line_total_cents' => $this->lineTotalCents(),
            'product' => new ProductResource($this->whenLoaded('variant.product')),
            'variant' => $this->whenLoaded('variant', fn () => [
                'id' => $this->variant->id,
                'name' => $this->variant->name,
                'sku' => $this->variant->sku,
                'price_cents' => $this->variant->price_cents,
                'stock_quantity' => $this->variant->stock_quantity,
            ]),
        ];
    }
}
