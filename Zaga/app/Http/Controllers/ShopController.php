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

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('sort')) {
            match ($request->sort) {
                'price_asc'  => $query->orderBy('price', 'asc'),
                'price_desc' => $query->orderBy('price', 'desc'),
                'rating'     => $query->orderBy('rating', 'desc'),
                default      => $query->latest(),
            };
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = Product::distinct()->pluck('category');

        return view('shop.index', compact('products', 'categories'));
    }

    public function show(Product $product)
    {
        return view('shop.show', compact('product'));
    }
}
