<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Basket;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Recipe;
use App\Models\Shop;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $request->validate([
            'search' => 'required|string|min:1',
            'type'   => 'required|in:product,brand,shop,basket,recipe',
        ]);

        $search = $request->search;
        $type   = $request->type;

        $query = match ($type) {

            /*
            |--------------------------------------------------------------------------
            | Product Search
            |--------------------------------------------------------------------------
            */
            'product' => Product::deepSearch($search)
                ->where('is_active', true)
                ->inCatalogCategory()
                ->with(['media'])
                ->withAvg('ratings', 'rating')
                ->orderByDesc('ratings_avg_rating'),

            /*
            |--------------------------------------------------------------------------
            | Brand Search
            |--------------------------------------------------------------------------
            */
            'brand' => Brand::deepSearch($search)
                ->withAvg('ratings', 'rating')
                ->orderByDesc('ratings_avg_rating'),

            /*
            |--------------------------------------------------------------------------
            | Shop Search
            |--------------------------------------------------------------------------
            */
            'shop' => Shop::deepSearch($search)
                ->withAvg('ratings', 'rating')
                ->orderByDesc('ratings_avg_rating'),

            /*
            |--------------------------------------------------------------------------
            | Recipe Search
            |--------------------------------------------------------------------------
            */
            'recipe' => Recipe::deepSearch($search)
                ->where('is_active', true)
                ->withAvg('ratings', 'rating')
                ->orderByDesc('ratings_avg_rating'),

            /*
            |--------------------------------------------------------------------------
            | Basket Search
            |--------------------------------------------------------------------------
            */
            'basket' => Basket::deepSearch($search)
                ->where('is_active', true)
                ->withAvg('ratings', 'rating')
                ->orderByDesc('ratings_avg_rating'),
        };

        $results = $query
            ->limit(20)
            ->get()
            ->map(function ($item) {
                return [
                    'id'    => $item->id,
                    'name'  => $item->name,
                    'image' => $item->image_url ?? null,
                ];
            });

        return response()->json([
            'data' => $results
        ]);
    }
}
