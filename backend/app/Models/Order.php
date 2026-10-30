<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_SHIPPED = 'shipped';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_REFUNDED = 'refunded';

    public const PAYMENT_PENDING = 'pending';

    public const PAYMENT_PAID = 'paid';

    public const PAYMENT_FAILED = 'failed';

    public const PAYMENT_REFUNDED = 'refunded';

    protected $fillable = [
        'number',
        'user_id',
        'status',
        'payment_status',
        'payment_method',
        'currency',
        'subtotal_cents',
        'discount_cents',
        'shipping_cents',
        'tax_cents',
        'total_cents',
        'coupon_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_address',
        'billing_address',
        'notes',
        'idempotency_key',
        'placed_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal_cents' => 'integer',
            'discount_cents' => 'integer',
            'shipping_cents' => 'integer',
            'tax_cents' => 'integer',
            'total_cents' => 'integer',
            'shipping_address' => 'array',
            'billing_address' => 'array',
            'placed_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function canCancel(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING], true);
    }

    public function canRefund(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID
            && ! in_array($this->status, [self::STATUS_REFUNDED, self::STATUS_CANCELLED], true);
    }
}
