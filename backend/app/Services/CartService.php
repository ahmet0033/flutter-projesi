<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function resolve(Request $request): Cart
    {
        $user = $request->user();

        if ($user) {
            return Cart::firstOrCreate([
                'user_id' => $user->id,
                'status' => 'active',
            ]);
        }

        $token = $request->header('X-Cart-Token');

        if (! is_string($token) || ! Str::isUuid($token)) {
            $token = (string) Str::uuid();
        }

        return Cart::firstOrCreate([
            'token' => $token,
            'status' => 'active',
        ]);
    }

    public function load(Cart $cart): Cart
    {
        return $cart->load([
            'items.variant.product.category',
            'items.variant.product.brand',
            'items.variant.product.images',
        ]);
    }

    public function add(Cart $cart, ProductVariant $variant, int $quantity): Cart
    {
        if (! $variant->is_active || ! $variant->product?->is_active) {
            throw ValidationException::withMessages([
                'product_variant_id' => 'This product is not available.',
            ]);
        }

        $quantity = max(1, $quantity);
        $item = $cart->items()->firstOrNew(['product_variant_id' => $variant->id]);
        $newQuantity = ($item->exists ? $item->quantity : 0) + $quantity;

        if ($newQuantity > $variant->stock_quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'The requested quantity is not available.',
            ]);
        }

        $item->quantity = $newQuantity;
        $item->save();

        return $this->load($cart);
    }

    public function update(Cart $cart, CartItem $item, int $quantity): Cart
    {
        $quantity = max(1, $quantity);

        if ($quantity > $item->variant->stock_quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'The requested quantity is not available.',
            ]);
        }

        $item->update(['quantity' => $quantity]);

        return $this->load($cart);
    }

    public function remove(Cart $cart, CartItem $item): Cart
    {
        $item->delete();

        return $this->load($cart);
    }

    public function merge(User $user, ?string $guestToken): Cart
    {
        $userCart = Cart::firstOrCreate([
            'user_id' => $user->id,
            'status' => 'active',
        ]);

        if (! $guestToken || ! Str::isUuid($guestToken)) {
            return $this->load($userCart);
        }

        $guestCart = Cart::query()
            ->where('token', $guestToken)
            ->whereNull('user_id')
            ->where('status', 'active')
            ->first();

        if (! $guestCart) {
            return $this->load($userCart);
        }

        foreach ($guestCart->items()->with('variant')->get() as $guestItem) {
            $existing = $userCart->items()
                ->where('product_variant_id', $guestItem->product_variant_id)
                ->first();

            $newQuantity = ($existing?->quantity ?? 0) + $guestItem->quantity;
            $newQuantity = min($newQuantity, $guestItem->variant->stock_quantity);

            if ($newQuantity > 0) {
                $userCart->items()->updateOrCreate(
                    ['product_variant_id' => $guestItem->product_variant_id],
                    ['quantity' => $newQuantity],
                );
            }
        }

        $guestCart->items()->delete();
        $guestCart->delete();

        return $this->load($userCart);
    }
}
