<?php

namespace App\Models;

use Database\Factories\AddressFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    /** @use HasFactory<AddressFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'label',
        'name',
        'phone',
        'line1',
        'line2',
        'city',
        'state',
        'postal_code',
        'country',
        'is_default_shipping',
        'is_default_billing',
    ];

    protected function casts(): array
    {
        return [
            'is_default_shipping' => 'boolean',
            'is_default_billing' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function toSnapshot(): array
    {
        return [
            'name' => $this->name,
            'phone' => $this->phone,
            'line1' => $this->line1,
            'line2' => $this->line2,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
        ];
    }
}
