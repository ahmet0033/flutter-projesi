<?php

namespace App\Http\Resources;

use App\Support\StoreSettings;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'token' => $this->token,
            'item_count' => $this->itemCount(),
            'subtotal_cents' => $this->subtotalCents(),
            'currency' => app(StoreSettings::class)->currency(),
            'items' => CartItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
