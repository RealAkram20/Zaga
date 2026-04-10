<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title', 'category', 'price', 'original_price', 'discount',
        'rating', 'reviews', 'description', 'features', 'sku', 'warranty',
        'in_stock', 'stock', 'image', 'additional_images', 'credit_available',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'additional_images' => 'array',
            'in_stock' => 'boolean',
            'credit_available' => 'boolean',
        ];
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'UGX ' . number_format($this->price);
    }
}
