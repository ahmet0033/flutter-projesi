<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provider' => $this->provider,
            'method' => $this->method,
            'status' => $this->status,
            'amount_cents' => $this->amount_cents,
            'transaction_id' => $this->transaction_id,
            'paid_at' => $this->paid_at,
        ];
    }
}
