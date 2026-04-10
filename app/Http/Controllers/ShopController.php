<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function home()
    {
        $featured = Product::where('in_stock', true)->latest()->take(8)->get();
        return view('home', compact('featured'));
    }

    public function index(Request $request)
    {
        $query = Product::where('in_stock', true);

        // Search
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%");
            });
        }

        // Category (supports multiple checkboxes)
        if ($request->filled('category')) {
            $query->whereIn('category', (array) $request->category);
        }

        // Max price slider
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Rating (supports multiple checkboxes)
        if ($request->filled('rating')) {
            $minRating = min((array) $request->rating);
            $query->where('rating', '>=', $minRating);
        }

        // Sort
        match ($request->sort) {
            'price_asc'  => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'rating'     => $query->orderBy('rating', 'desc'),
            'newest'     => $query->latest(),
            default      => $query->orderBy('id'),
        };

        $products   = $query->paginate(12)->withQueryString();
        $categories = Product::distinct()->orderBy('category')->pluck('category');
        $maxPrice   = Product::max('price') ?? 5000000;

        return view('shop.index', compact('products', 'categories', 'maxPrice'));
    }

    public function show(Product $product)
    {
        return view('shop.show', compact('product'));
    }
}
