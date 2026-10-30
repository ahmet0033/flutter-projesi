<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'currency' => $this->currency,
            'subtotal_cents' => $this->subtotal_cents,
            'discount_cents' => $this->discount_cents,
            'shipping_cents' => $this->shipping_cents,
            'tax_cents' => $this->tax_cents,
            'total_cents' => $this->total_cents,
            'coupon' => $this->whenLoaded('coupon', fn () => $this->coupon ? [
                'id' => $this->coupon->id,
                'code' => $this->coupon->code,
            ] : null),
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'shipping_address' => $this->shipping_address,
            'billing_address' => $this->billing_address,
            'notes' => $this->notes,
            'placed_at' => $this->placed_at,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
        ];
    }
}
