<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class QuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipping_method_id' => ['nullable', 'integer', 'exists:shipping_methods,id'],
            'coupon_code' => ['nullable', 'string', 'max:64'],
        ];
    }
}
