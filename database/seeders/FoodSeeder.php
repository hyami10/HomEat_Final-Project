<?php

namespace Database\Seeders;

use App\Models\Food;
use Illuminate\Database\Seeder;

class FoodSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name' => 'Nasi Goreng Spesial',
                'description' => 'Nasi goreng dengan telur mata sapi, sate ayam, dan kerupuk udang.',
                'price' => 25000,
                'category' => 'Main Course',
                'stock' => 50,
                'image' => 'images/menu/nasgor.jpg',
            ],
            [
                'name' => 'Sate Ayam Madura',
                'description' => 'Sate ayam dengan bumbu kacang khas Madura yang legit.',
                'price' => 30000,
                'category' => 'Main Course',
                'stock' => 40,
                'image' => 'images/menu/sate.jpg',
            ],
            [
                'name' => 'Soto Ayam Lamongan',
                'description' => 'Kuah kaldu ayam hangat dengan koya gurih dan jeruk nipis.',
                'price' => 28000,
                'category' => 'Soup',
                'stock' => 35,
                'image' => 'images/menu/soto.jpg',
            ],
            [
                'name' => 'Pisang Goreng Madu',
                'description' => 'Pisang kepok dilapisi madu karamelisasi, cocok untuk camilan.',
                'price' => 18000,
                'category' => 'Snack',
                'stock' => 60,
                'image' => 'images/menu/pisang.jpg',
            ],
            [
                'name' => 'Es Teh Manis Premium',
                'description' => 'Teh melati premium dengan gula aren dan es batu kristal.',
                'price' => 10000,
                'category' => 'Beverage',
                'stock' => 150,
                'image' => 'images/menu/esteh.webp',
            ],
        ];

        foreach ($items as $item) {
            Food::updateOrCreate(
                ['name' => $item['name']],
                $item
            );
        }
    }
}
