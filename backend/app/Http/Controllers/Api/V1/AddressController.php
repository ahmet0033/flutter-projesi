<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AddressController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $addresses = $request->user()->addresses()
            ->orderByDesc('is_default_shipping')
            ->latest()
            ->get();

        return AddressResource::collection($addresses);
    }

    public function store(AddressRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $first = $user->addresses()->count() === 0;

        $address = $user->addresses()->create($data + [
            'is_default_shipping' => $first,
            'is_default_billing' => $first,
        ]);

        $this->applyDefaults($user->id, $address, $data);

        return (new AddressResource($address->fresh()))
            ->response()
            ->setStatusCode(201);
    }

    public function update(AddressRequest $request, Address $address): AddressResource
    {
        $this->ensureOwner($request, $address);

        $data = $request->validated();
        $address->update($data);
        $this->applyDefaults($address->user_id, $address, $data);

        return new AddressResource($address->fresh());
    }

    public function destroy(Request $request, Address $address): JsonResponse
    {
        $this->ensureOwner($request, $address);
        $wasDefaultShipping = $address->is_default_shipping;
        $wasDefaultBilling = $address->is_default_billing;
        $userId = $address->user_id;
        $address->delete();

        $this->restoreDefaults($userId, $wasDefaultShipping, $wasDefaultBilling);

        return response()->json(['message' => 'Address deleted.']);
    }

    private function ensureOwner(Request $request, Address $address): void
    {
        abort_unless($address->user_id === $request->user()->id, 403);
    }

    private function applyDefaults(int $userId, Address $address, array $data): void
    {
        if (! empty($data['is_default_shipping'])) {
            Address::query()
                ->where('user_id', $userId)
                ->whereKeyNot($address->id)
                ->update(['is_default_shipping' => false]);
        }

        if (! empty($data['is_default_billing'])) {
            Address::query()
                ->where('user_id', $userId)
                ->whereKeyNot($address->id)
                ->update(['is_default_billing' => false]);
        }
    }

    private function restoreDefaults(int $userId, bool $shipping, bool $billing): void
    {
        $first = Address::query()->where('user_id', $userId)->oldest()->first();

        if (! $first) {
            return;
        }

        if ($shipping && ! Address::query()->where('user_id', $userId)->where('is_default_shipping', true)->exists()) {
            $first->update(['is_default_shipping' => true]);
        }

        if ($billing && ! Address::query()->where('user_id', $userId)->where('is_default_billing', true)->exists()) {
            $first->update(['is_default_billing' => true]);
        }
    }
}
