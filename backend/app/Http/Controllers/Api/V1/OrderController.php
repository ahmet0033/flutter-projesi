<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PlaceOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function __construct(
        private readonly CartService $carts,
        private readonly OrderService $orders,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = $request->user()->orders()
            ->with(['items', 'payments'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return OrderResource::collection($orders);
    }

    public function show(Request $request, Order $order): OrderResource
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        return new OrderResource($order->load(['items', 'payments', 'coupon']));
    }

    public function store(PlaceOrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['idempotency_key'] ??= $request->header('Idempotency-Key');

        $order = $this->orders->place(
            $request->user(),
            $this->carts->resolve($request),
            $data,
        );

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201);
    }

    public function cancel(Request $request, Order $order): OrderResource
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        return new OrderResource($this->orders->cancel($order));
    }
}
