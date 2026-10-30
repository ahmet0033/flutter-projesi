<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    protected $fillable = [
        'store_name',
        'currency',
        'tax_rate',
        'tax_inclusive',
        'order_prefix',
        'support_email',
        'support_phone',
        'address',
        'instagram_url',
        'x_url',
        'facebook_url',
    ];

    protected function casts(): array
    {
        return [
            'tax_rate' => 'decimal:2',
            'tax_inclusive' => 'boolean',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'store_name' => 'RedEast',
            'currency' => 'USD',
            'tax_rate' => 20.00,
            'tax_inclusive' => true,
            'order_prefix' => 'RE',
            'support_email' => 'support@redeast.agency',
            'support_phone' => null,
            'address' => null,
            'instagram_url' => 'https://instagram.com/redeast',
            'x_url' => null,
            'facebook_url' => null,
        ];
    }
}
