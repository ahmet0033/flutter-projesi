<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BrandController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $brands = Brand::query()
            ->active()
            ->withCount(['products' => fn ($query) => $query->active()])
            ->orderBy('name')
            ->get();

        return BrandResource::collection($brands);
    }

    public function show(Brand $brand): BrandResource
    {
        abort_unless($brand->is_active, 404);

        $brand->loadCount(['products' => fn ($query) => $query->active()]);

        return new BrandResource($brand);
    }
}
