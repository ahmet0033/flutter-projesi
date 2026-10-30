<?php

namespace App\Support;

use App\Models\StoreSetting;

class StoreSettings
{
    private ?StoreSetting $settings = null;

    public function current(): StoreSetting
    {
        return $this->settings ??= StoreSetting::query()->firstOrCreate(['id' => 1], StoreSetting::defaults());
    }

    public function refresh(): StoreSetting
    {
        return $this->settings = $this->current()->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(array $data): StoreSetting
    {
        $settings = $this->current();
        $settings->update($data);

        return $this->settings = $settings->refresh();
    }

    public function currency(): string
    {
        return (string) $this->current()->currency;
    }

    public function taxRate(): float
    {
        return round(((float) $this->current()->tax_rate) / 100, 4);
    }

    public function taxInclusive(): bool
    {
        return (bool) $this->current()->tax_inclusive;
    }

    public function orderPrefix(): string
    {
        return (string) $this->current()->order_prefix;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->current()->only([
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
        ]);
    }
}
