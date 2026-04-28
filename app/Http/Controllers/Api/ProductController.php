<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private const DEMO_ROWS = [
        ['sku' => 'DRK-001', 'name' => 'Americano', 'price_cents' => 350, 'category' => 'Drinks'],
        ['sku' => 'DRK-002', 'name' => 'Latte', 'price_cents' => 495, 'category' => 'Drinks'],
        ['sku' => 'BKR-101', 'name' => 'Butter Croissant', 'price_cents' => 320, 'category' => 'Bakery'],
        ['sku' => 'BKR-102', 'name' => 'Blueberry Muffin', 'price_cents' => 375, 'category' => 'Bakery'],
        ['sku' => 'FD-201', 'name' => 'Turkey Sandwich', 'price_cents' => 899, 'category' => 'Food'],
        ['sku' => 'FD-202', 'name' => 'Garden Salad', 'price_cents' => 750, 'category' => 'Food'],
        ['sku' => 'SNK-301', 'name' => 'Kettle Chips', 'price_cents' => 225, 'category' => 'Snacks'],
        ['sku' => 'DRK-010', 'name' => 'Sparkling Water', 'price_cents' => 199, 'category' => 'Drinks'],
    ];

    public function index(Request $request)
    {
        $products = Product::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $products->map(fn (Product $p) => $this->formatProduct($p))->values(),
        ]);
    }

    public function seed(Request $request)
    {
        foreach (self::DEMO_ROWS as $row) {
            Product::query()->updateOrCreate(
                ['sku' => $row['sku']],
                [
                    'name' => $row['name'],
                    'price_cents' => $row['price_cents'],
                    'category' => $row['category'],
                    'active' => true,
                ]
            );
        }

        return response()->json(['message' => 'Demo catalog loaded']);
    }

    private function formatProduct(Product $p): array
    {
        return [
            'id' => (string) $p->id,
            'name' => $p->name,
            'priceCents' => $p->price_cents,
            'category' => $p->category,
            'sku' => $p->sku,
            'active' => $p->active,
        ];
    }
}
