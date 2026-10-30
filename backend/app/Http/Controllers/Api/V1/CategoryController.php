<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $categories = Category::query()
            ->active()
            ->withCount(['products' => fn ($query) => $query->active()])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return CategoryResource::collection($categories);
    }

    public function show(Category $category): CategoryResource
    {
        abort_unless($category->is_active, 404);

        $category->loadCount(['products' => fn ($query) => $query->active()]);

        return new CategoryResource($category);
    }
}
