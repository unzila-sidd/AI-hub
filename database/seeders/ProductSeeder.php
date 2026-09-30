<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['Espresso', 'BEV-001', 'Beverages', 3.50, 1.10, 60],
            ['Cappuccino', 'BEV-002', 'Beverages', 4.00, 1.50, 50],
            ['Latte', 'BEV-003', 'Beverages', 4.25, 1.60, 45],
            ['Green Tea', 'BEV-004', 'Beverages', 2.50, 0.80, 80],
            ['Cheese Sandwich', 'FOOD-001', 'Food', 5.00, 2.50, 30],
            ['Chicken Wrap', 'FOOD-002', 'Food', 6.50, 3.20, 25],
            ['Caesar Salad', 'FOOD-003', 'Food', 7.00, 3.80, 15],
            ['Mineral Water', 'BEV-005', 'Beverages', 1.00, 0.40, 120],
            ['Pens (pack)', 'STA-001', 'Stationery', 2.00, 0.90, 40],
            ['Notebook', 'STA-002', 'Stationery', 3.25, 1.60, 35],
        ];

        foreach ($products as [$name, $sku, $category, $price, $cost, $stock]) {
            Product::updateOrCreate(
                ['sku' => $sku],
                compact('name', 'sku', 'category', 'price', 'cost', 'stock')
            );
        }
    }
}