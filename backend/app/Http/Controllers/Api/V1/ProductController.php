<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ProductIndexRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(ProductIndexRequest $request): AnonymousResourceCollection
    {
        $query = Product::query()
            ->active()
            ->with(['category', 'brand', 'variants', 'images'])
            ->withAvg(['reviews' => fn (Builder $q) => $q->where('is_approved', true)], 'rating')
            ->withCount(['reviews' => fn (Builder $q) => $q->where('is_approved', true)]);

        $this->applyFilters($query, $request);

        match ($request->string('sort')->toString()) {
            'price_asc' => $query->orderBy('price_cents'),
            'price_desc' => $query->orderByDesc('price_cents'),
            'name' => $query->orderBy('name'),
            default => $query->latest(),
        };

        return ProductResource::collection(
            $query->paginate($request->integer('per_page', 12))->withQueryString(),
        );
    }

    public function show(Product $product): ProductResource
    {
        abort_unless($product->is_active, 404);

        $product->load(['category', 'brand', 'variants', 'images']);
        $product->loadAvg(['reviews' => fn (Builder $q) => $q->where('is_approved', true)], 'rating');
        $product->loadCount(['reviews' => fn (Builder $q) => $q->where('is_approved', true)]);

        return new ProductResource($product);
    }

    private function applyFilters(Builder $query, ProductIndexRequest $request): void
    {
        if ($category = $request->string('category')->toString()) {
            $query->whereHas('category', fn (Builder $q) => $q->where('slug', $category));
        }

        if ($brand = $request->string('brand')->toString()) {
            $query->whereHas('brand', fn (Builder $q) => $q->where('slug', $brand));
        }

        if ($search = trim($request->string('search')->toString())) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('tagline', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('min_price')) {
            $query->where('price_cents', '>=', (int) round((float) $request->input('min_price') * 100));
        }

        if ($request->filled('max_price')) {
            $query->where('price_cents', '<=', (int) round((float) $request->input('max_price') * 100));
        }

        if ($request->has('featured')) {
            $query->where('is_featured', $request->boolean('featured'));
        }
    }
}
