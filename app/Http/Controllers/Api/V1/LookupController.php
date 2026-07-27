<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Allergen;
use App\Models\Category;
use App\Models\Cuisine;
use App\Models\DietaryTag;
use App\Models\Ingredient;
use App\Models\Nutrient;
use Illuminate\Http\JsonResponse;

final class LookupController extends Controller
{
    public function ingredients(): JsonResponse
    {
        return response()->json(Ingredient::query()->select(['public_id', 'name', 'slug'])->orderBy('name')->paginate(50));
    }

    public function cuisines(): JsonResponse
    {
        return response()->json(Cuisine::query()->select(['public_id', 'name', 'slug'])->orderBy('name')->paginate(50));
    }

    public function categories(): JsonResponse
    {
        return response()->json(Category::query()->select(['public_id', 'name', 'slug'])->orderBy('name')->paginate(50));
    }

    public function nutrients(): JsonResponse
    {
        return response()->json(['data' => Nutrient::query()->where('is_active', true)->orderBy('sort_order')->get(['public_id', 'code', 'name', 'category', 'unit', 'decimal_precision', 'daily_reference_value'])]);
    }

    public function allergens(): JsonResponse
    {
        return response()->json(['data' => Allergen::query()->where('is_active', true)->orderBy('name')->get(['public_id', 'code', 'name'])]);
    }

    public function dietaryTags(): JsonResponse
    {
        return response()->json(['data' => DietaryTag::query()->where('is_active', true)->orderBy('name')->get(['public_id', 'code', 'name', 'description'])]);
    }
}
