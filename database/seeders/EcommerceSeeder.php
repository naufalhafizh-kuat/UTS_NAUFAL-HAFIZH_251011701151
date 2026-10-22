<?php

namespace Database\Seeders;

use App\Models\Ecommerce;
use Illuminate\Database\Seeder;

class EcommerceSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['name' => 'Kaos Polos Hitam',     'category' => 'Kaos',     'price' => 79000,  'image' => 'produk-1.jpg',  'description' => 'Kaos katun combed 30s warna hitam, adem dan nyaman dipakai sehari-hari.'],
            ['name' => 'Kaos Oversize Putih',  'category' => 'Kaos',     'price' => 95000,  'image' => 'produk-2.jpg',  'description' => 'Kaos oversize putih bahan tebal dengan potongan longgar yang kekinian.'],
            ['name' => 'Kemeja Oxford Putih',  'category' => 'Kemeja',   'price' => 149000, 'image' => 'produk-3.jpg',  'description' => 'Kemeja oxford putih lengan panjang, rapi untuk kerja maupun santai.'],
            ['name' => 'Kemeja Flannel Hitam', 'category' => 'Kemeja',   'price' => 159000, 'image' => 'produk-4.jpg',  'description' => 'Kemeja flannel hitam yang hangat dan cocok untuk gaya kasual.'],
            ['name' => 'Hoodie Hitam',         'category' => 'Jaket',    'price' => 189000, 'image' => 'produk-5.jpg',  'description' => 'Hoodie fleece tebal dengan kantong depan, hangat dan stylish.'],
            ['name' => 'Jaket Bomber Abu',     'category' => 'Jaket',    'price' => 229000, 'image' => 'produk-6.jpg',  'description' => 'Jaket bomber abu-abu water-resistant dengan lapisan dalam yang lembut.'],
            ['name' => 'Celana Chino Hitam',   'category' => 'Celana',   'price' => 165000, 'image' => 'produk-7.jpg',  'description' => 'Celana chino slim fit hitam dengan bahan stretch yang nyaman bergerak.'],
            ['name' => 'Celana Jogger Abu',    'category' => 'Celana',   'price' => 125000, 'image' => 'produk-8.jpg',  'description' => 'Celana jogger abu-abu ringan dengan karet di pergelangan, santai dan praktis.'],
            ['name' => 'Jeans Barrel Hitam',   'category' => 'Jeans',    'price' => 249000, 'image' => 'produk-9.jpg',  'description' => 'Jeans model barrel pinggang reguler, potongan modern dengan bahan denim tebal.'],
            ['name' => 'Jeans Lurus Abu',      'category' => 'Jeans',    'price' => 229000, 'image' => 'produk-10.jpg', 'description' => 'Jeans potongan lurus warna abu-abu, mudah dipadukan dengan atasan apa saja.'],
            ['name' => 'Sweater Turtleneck Putih', 'category' => 'Knitwear', 'price' => 199000, 'image' => 'produk-11.jpg', 'description' => 'Sweater turtleneck rajutan halus yang hangat dan nyaman di kulit.'],
            ['name' => 'Cardigan Rajut Abu',   'category' => 'Knitwear', 'price' => 219000, 'image' => 'produk-12.jpg', 'description' => 'Cardigan rajut abu-abu dengan kancing depan, cocok untuk berlapis.'],
        ];

        foreach ($data as $item) {
            Ecommerce::create($item);
        }
    }
}