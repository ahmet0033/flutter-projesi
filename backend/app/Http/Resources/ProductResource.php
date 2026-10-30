<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'tagline' => $this->tagline,
            'description' => $this->description,
            'price' => $this->formattedPrice(),
            'price_cents' => $this->price_cents,
            'compare_at_price_cents' => $this->compare_at_price_cents,
            'currency' => $this->currency,
            'image' => $this->primaryImageUrl(),
            'images' => $this->whenLoaded('images', fn () => $this->images
                ->map(fn ($image) => [
                    'id' => $image->id,
                    'url' => $image->resolvedUrl(),
                    'alt' => $image->alt,
                ])
                ->values()),
            'specs' => $this->specs ?? [],
            'is_featured' => $this->is_featured,
            'in_stock' => $this->hasStock(),
            'rating' => $this->reviews_avg_rating !== null
                ? round((float) $this->reviews_avg_rating, 1)
                : null,
            'reviews_count' => $this->whenCounted('reviews'),
            'category' => $this->whenLoaded('category', fn () => new CategoryResource($this->category)),
            'brand' => $this->whenLoaded('brand', fn () => $this->brand ? new BrandResource($this->brand) : null),
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
        ];
    }
}
