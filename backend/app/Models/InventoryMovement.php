<?php

namespace App\Models;

use Database\Factories\InventoryMovementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryMovement extends Model
{
    /** @use HasFactory<InventoryMovementFactory> */
    use HasFactory;

    protected $fillable = [
        'product_variant_id',
        'quantity_delta',
        'reason',
        'reference_type',
        'reference_id',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'quantity_delta' => 'integer',
        ];
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
