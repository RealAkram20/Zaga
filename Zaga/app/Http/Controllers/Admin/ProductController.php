<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        $products = $query->latest()->paginate(20)->withQueryString();
        $categories = Product::distinct()->pluck('category');
        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = ['Laptops', 'Desktops', 'Tablets', 'Accessories', 'Peripherals', 'Storage'];
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'            => 'required|string|max:200',
            'category'         => 'required|string',
            'price'            => 'required|integer|min:1',
            'original_price'   => 'nullable|integer|min:1',
            'discount'         => 'nullable|integer|min:0|max:100',
            'rating'           => 'nullable|numeric|min:0|max:5',
            'reviews'          => 'nullable|integer|min:0',
            'description'      => 'required|string',
            'sku'              => 'nullable|string|max:50',
            'warranty'         => 'nullable|string|max:100',
            'stock'            => 'required|integer|min:0',
            'image'            => 'nullable|string|max:255',
            'credit_available' => 'boolean',
        ]);

        $data['in_stock'] = $data['stock'] > 0;
        $data['credit_available'] = $request->boolean('credit_available');

        Product::create($data);

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = ['Laptops', 'Desktops', 'Tablets', 'Accessories', 'Peripherals', 'Storage'];
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'title'            => 'required|string|max:200',
            'category'         => 'required|string',
            'price'            => 'required|integer|min:1',
            'original_price'   => 'nullable|integer|min:1',
            'discount'         => 'nullable|integer|min:0|max:100',
            'rating'           => 'nullable|numeric|min:0|max:5',
            'reviews'          => 'nullable|integer|min:0',
            'description'      => 'required|string',
            'sku'              => 'nullable|string|max:50',
            'warranty'         => 'nullable|string|max:100',
            'stock'            => 'required|integer|min:0',
            'image'            => 'nullable|string|max:255',
            'credit_available' => 'boolean',
        ]);

        $data['in_stock'] = $data['stock'] > 0;
        $data['credit_available'] = $request->boolean('credit_available');

        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }
}
