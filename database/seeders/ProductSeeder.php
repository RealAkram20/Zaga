<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    // UGX conversion: base prices in USD * 3700
    const UGX = 3700;

    public function run(): void
    {
        Product::truncate();

        $categories  = ['Laptops', 'Desktops', 'Tablets', 'Accessories', 'Peripherals', 'Storage'];
        $brands      = ['Astra', 'Orion', 'Zephyr', 'Nimbus', 'Vertex', 'Photon'];
        $laptopMods  = ['Air', 'Pro', 'Slim', 'Max', 'Studio'];
        $desktopMods = ['Ranger', 'Titan', 'Core', 'Quantum'];
        $tabletMods  = ['Tab', 'Note', 'Pad', 'Slate'];
        $accNames    = ['Headset', 'Charger', 'Dock', 'Case', 'Adapter', 'Power Bank'];
        $periphNames = ['Keyboard', 'Mouse', 'Monitor', 'Webcam', 'Microphone'];
        $storeNames  = ['SSD', 'HDD', 'NVMe', 'Portable SSD'];

        // Image mappings by category
        $categoryImages = [
            'Laptops'     => array_map(fn($i) => "images/l{$i}.jpg",  range(1, 12)),
            'Desktops'    => array_map(fn($i) => "images/d{$i}.jpg",  range(1, 12)),
            'Tablets'     => array_map(fn($i) => "images/t{$i}.jpg",  range(1, 8)),
            'Accessories' => array_map(fn($i) => "images/h{$i}.jpg",  range(1, 7)),
            'Peripherals' => array_map(fn($i) => "images/p{$i}.jpg",  range(1, 6)),
            'Storage'     => array_map(fn($i) => "images/s{$i}.jpg",  range(1, 6)),
        ];

        $products = [];
        mt_srand(42); // reproducible

        for ($i = 0; $i < 120; $i++) {
            $category = $categories[$i % count($categories)];
            $id = $i + 1;

            [$title, $baseUsd, $description, $features] = $this->makeProduct(
                $category, $id, $brands, $laptopMods, $desktopMods,
                $tabletMods, $accNames, $periphNames, $storeNames
            );

            $basePrice = (int) round($baseUsd * self::UGX / 1000) * 1000;
            $rating    = round(3.0 + (mt_rand(0, 200) / 100), 1);
            $reviews   = mt_rand(0, 1200);
            $inStock   = mt_rand(0, 99) > 4;
            $stock     = $inStock ? mt_rand(5, 200) : 0;
            $imgPool   = $categoryImages[$category];
            $image     = $imgPool[$id % count($imgPool)];

            $discount      = null;
            $originalPrice = null;
            if (mt_rand(0, 99) > 79) {
                $pct           = [10, 15, 20, 25][mt_rand(0, 3)];
                $discount      = $pct;
                $originalPrice = $basePrice;
                $basePrice     = (int) round($basePrice * (1 - $pct / 100) / 1000) * 1000;
            }

            $products[] = [
                'title'            => $title,
                'category'         => $category,
                'price'            => $basePrice,
                'original_price'   => $originalPrice,
                'discount'         => $discount,
                'rating'           => $rating,
                'reviews'          => $reviews,
                'description'      => $description,
                'features'         => json_encode($features),
                'sku'              => 'TS-' . str_pad($id, 5, '0', STR_PAD_LEFT),
                'warranty'         => (1 + ($id % 3)) . ' Year' . (($id % 3) === 1 ? '' : 's'),
                'in_stock'         => $inStock,
                'stock'            => $stock,
                'image'            => $image,
                'additional_images'=> json_encode([]),
                'credit_available' => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
        }

        foreach (array_chunk($products, 20) as $chunk) {
            Product::insert($chunk);
        }

        $this->command->info('120 products seeded successfully.');
    }

    private function makeProduct(
        string $category, int $id,
        array $brands, array $laptopMods, array $desktopMods,
        array $tabletMods, array $accNames, array $periphNames, array $storeNames
    ): array {
        $brand = $brands[$id % count($brands)];

        return match ($category) {
            'Laptops' => [
                $brand . ' ' . $laptopMods[$id % count($laptopMods)] . ' ' . (2020 + ($id % 6)),
                499 + (mt_rand(0, 2500)),
                "The {$brand} laptop delivers powerful performance with modern processors, vivid display, and long battery life. Perfect for productivity and content creation.",
                ['Intel/AMD latest-gen CPU', '8-32GB RAM options', 'Fast NVMe SSD', 'Backlit keyboard', 'Wi-Fi 6'],
            ],
            'Desktops' => [
                $brand . ' ' . $desktopMods[$id % count($desktopMods)] . ' Desktop ' . str_pad($id % 999, 3, '0', STR_PAD_LEFT),
                399 + (mt_rand(0, 3000)),
                "High-performance desktop built for gaming, creativity, and business. Expandable and ready for heavy workloads.",
                ['High-performance CPU', 'Discrete GPU options', 'Large cooling system', 'Multiple storage bays', 'Upgradeable RAM'],
            ],
            'Tablets' => [
                $brand . ' ' . $tabletMods[$id % count($tabletMods)] . ' ' . ($id % 4 === 0 ? 'Plus' : 'Mini'),
                199 + (mt_rand(0, 1200)),
                "Sleek tablet for media, reading, and light productivity. Crisp display and long battery life.",
                ['High-resolution touch display', 'Stylus support', 'Wi-Fi & LTE options', 'Long battery life'],
            ],
            'Accessories' => [
                'Universal ' . $accNames[$id % count($accNames)] . ' by ' . $brand,
                9 + (mt_rand(0, 190)),
                "Premium accessory compatible with most devices. Built for durability and performance.",
                ['Durable build', 'Warranty included', 'Universal compatibility'],
            ],
            'Peripherals' => [
                $brand . ' ' . $periphNames[$id % count($periphNames)] . ($id % 3 === 0 ? ' X' : ''),
                19 + (mt_rand(0, 500)),
                "Reliable peripheral for daily use. Comfortable design and precise performance.",
                ['Comfort-focused design', 'Plug & play', 'Multi-device pairing'],
            ],
            default => [ // Storage
                $brand . ' ' . $storeNames[$id % count($storeNames)] . ' ' . (($id % 5) * 128) . 'GB',
                29 + (mt_rand(0, 600)),
                "Fast and reliable storage for your important files, media, and backups.",
                ['High endurance', 'Fast read/write speeds', 'Compact design'],
            ],
        };
    }
}
