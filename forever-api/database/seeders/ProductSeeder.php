<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('item_de_ventas')->delete();
        DB::table('ventas')->delete();
        DB::table('products')->delete();
        
        DB::statement('ALTER SEQUENCE products_id_seq RESTART WITH 1');

        $products = [
            // BEBIDAS
            ['sku' => '015', 'name' => 'Forever Aloe Vera Gel', 'cc_value' => 0.094, 'price_bs' => 215.00, 'stock' => 50, 'categoria' => 'Bebidas'],
            ['sku' => '077', 'name' => "Forever Aloe Bits N' Peaches", 'cc_value' => 0.094, 'price_bs' => 215.00, 'stock' => 30, 'categoria' => 'Bebidas'],
            ['sku' => '034', 'name' => 'Forever Aloe Berry Nectar', 'cc_value' => 0.094, 'price_bs' => 215.00, 'stock' => 30, 'categoria' => 'Bebidas'],
            ['sku' => '200', 'name' => 'Aloe Blossom Herbal Tea', 'cc_value' => 0.070, 'price_bs' => 150.00, 'stock' => 100, 'categoria' => 'Bebidas'],
            ['sku' => '196', 'name' => 'Forever Freedom', 'cc_value' => 0.133, 'price_bs' => 280.00, 'stock' => 20, 'categoria' => 'Bebidas'],

            // NUTRICIÓN (Diaria, Focalizada, Inmune, Vida Activa, Peso)
            ['sku' => '376', 'name' => 'Forever Arctic Sea', 'cc_value' => 0.127, 'price_bs' => 250.00, 'stock' => 40, 'categoria' => 'Nutrición'],
            ['sku' => '354', 'name' => 'Forever Kids', 'cc_value' => 0.060, 'price_bs' => 120.00, 'stock' => 60, 'categoria' => 'Nutrición'],
            ['sku' => '439', 'name' => 'Forever Daily', 'cc_value' => 0.080, 'price_bs' => 180.00, 'stock' => 50, 'categoria' => 'Nutrición'],
            ['sku' => '206', 'name' => 'Forever Calcium', 'cc_value' => 0.089, 'price_bs' => 200.00, 'stock' => 30, 'categoria' => 'Nutrición'],
            ['sku' => '037', 'name' => 'Forever Nature-Min', 'cc_value' => 0.088, 'price_bs' => 170.00, 'stock' => 40, 'categoria' => 'Nutrición'],
            ['sku' => '068', 'name' => 'Forever Fields of Greens', 'cc_value' => 0.050, 'price_bs' => 110.00, 'stock' => 50, 'categoria' => 'Nutrición'],
            ['sku' => '610', 'name' => 'Forever Active Pro-B', 'cc_value' => 0.147, 'price_bs' => 280.00, 'stock' => 25, 'categoria' => 'Nutrición'],
            ['sku' => '464', 'name' => 'Forever Fiber', 'cc_value' => 0.115, 'price_bs' => 210.00, 'stock' => 30, 'categoria' => 'Nutrición'],
            ['sku' => '624', 'name' => 'Forever iVision', 'cc_value' => 0.140, 'price_bs' => 280.00, 'stock' => 20, 'categoria' => 'Nutrición'],
            ['sku' => '188', 'name' => 'Forever B12 Plus', 'cc_value' => 0.056, 'price_bs' => 130.00, 'stock' => 40, 'categoria' => 'Nutrición'],
            ['sku' => '072', 'name' => 'Forever Lycium Plus', 'cc_value' => 0.124, 'price_bs' => 250.00, 'stock' => 20, 'categoria' => 'Nutrición'],
            ['sku' => '215', 'name' => 'Forever Multi-Maca', 'cc_value' => 0.106, 'price_bs' => 220.00, 'stock' => 25, 'categoria' => 'Nutrición'],
            ['sku' => '556', 'name' => 'Infinite Firming Complex', 'cc_value' => 0.207, 'price_bs' => 450.00, 'stock' => 15, 'categoria' => 'Nutrición'],
            ['sku' => '375', 'name' => 'Vitalize Woman', 'cc_value' => 0.127, 'price_bs' => 240.00, 'stock' => 20, 'categoria' => 'Nutrición'],
            ['sku' => '566', 'name' => 'Forever Immune Gummy', 'cc_value' => 0.148, 'price_bs' => 250.00, 'stock' => 35, 'categoria' => 'Nutrición'],
            ['sku' => '065', 'name' => 'Forever Garlic-Thyme', 'cc_value' => 0.079, 'price_bs' => 160.00, 'stock' => 40, 'categoria' => 'Nutrición'],
            ['sku' => '048', 'name' => 'Forever Absorbent-C', 'cc_value' => 0.065, 'price_bs' => 140.00, 'stock' => 60, 'categoria' => 'Nutrición'],
            ['sku' => '355', 'name' => 'Forever ImmuBlend', 'cc_value' => 0.118, 'price_bs' => 230.00, 'stock' => 25, 'categoria' => 'Nutrición'],
            ['sku' => '473', 'name' => 'ARGI+', 'cc_value' => 0.293, 'price_bs' => 680.00, 'stock' => 20, 'categoria' => 'Nutrición'],
            ['sku' => '470', 'name' => 'Forever Lite Ultra Vanilla', 'cc_value' => 0.122, 'price_bs' => 280.00, 'stock' => 30, 'categoria' => 'Nutrición'],
            ['sku' => '471', 'name' => 'Forever Lite Ultra Chocolate', 'cc_value' => 0.122, 'price_bs' => 280.00, 'stock' => 30, 'categoria' => 'Nutrición'],
            ['sku' => '289', 'name' => 'Forever Lean', 'cc_value' => 0.167, 'price_bs' => 350.00, 'stock' => 25, 'categoria' => 'Nutrición'],
            ['sku' => '071', 'name' => 'Forever Garcinia Plus', 'cc_value' => 0.124, 'price_bs' => 250.00, 'stock' => 30, 'categoria' => 'Nutrición'],
            ['sku' => '463', 'name' => 'Forever Therm', 'cc_value' => 0.114, 'price_bs' => 230.00, 'stock' => 40, 'categoria' => 'Nutrición'],

            // COMBOS (Packs)
            ['sku' => '801', 'name' => 'Fast Start Pack', 'cc_value' => 2.000, 'price_bs' => 3500.00, 'stock' => 10, 'categoria' => 'Combos'],
            ['sku' => '001', 'name' => 'A Touch of Forever', 'cc_value' => 2.000, 'price_bs' => 3500.00, 'stock' => 5, 'categoria' => 'Combos'],
            ['sku' => '810', 'name' => 'Personal Care Pack', 'cc_value' => 0.145, 'price_bs' => 350.00, 'stock' => 20, 'categoria' => 'Combos'],
            ['sku' => '805', 'name' => 'C9 Pack Nutricional', 'cc_value' => 0.675, 'price_bs' => 1250.00, 'stock' => 15, 'categoria' => 'Combos'],

            // COLMENA
            ['sku' => '207', 'name' => 'Forever Bee Honey', 'cc_value' => 0.079, 'price_bs' => 145.00, 'stock' => 40, 'categoria' => 'Colmena'],
            ['sku' => '036', 'name' => 'Forever Royal Jelly', 'cc_value' => 0.130, 'price_bs' => 270.00, 'stock' => 20, 'categoria' => 'Colmena'],
            ['sku' => '051', 'name' => 'Aloe Propolis Creme', 'cc_value' => 0.079, 'price_bs' => 180.00, 'stock' => 50, 'categoria' => 'Colmena'],
            ['sku' => '027', 'name' => 'Forever Bee Propolis', 'cc_value' => 0.127, 'price_bs' => 250.00, 'stock' => 30, 'categoria' => 'Colmena'],
            ['sku' => '026', 'name' => 'Forever Bee Pollen', 'cc_value' => 0.063, 'price_bs' => 130.00, 'stock' => 60, 'categoria' => 'Colmena'],

            // CUIDADO PERSONAL
            ['sku' => '064', 'name' => 'Aloe Heat Lotion', 'cc_value' => 0.056, 'price_bs' => 130.00, 'stock' => 50, 'categoria' => 'Cuidado Personal'],
            ['sku' => '205', 'name' => 'Aloe MSM Gel', 'cc_value' => 0.084, 'price_bs' => 180.00, 'stock' => 40, 'categoria' => 'Cuidado Personal'],
            ['sku' => '264', 'name' => 'Forever Active HA', 'cc_value' => 0.136, 'price_bs' => 260.00, 'stock' => 20, 'categoria' => 'Cuidado Personal'],
            ['sku' => '551', 'name' => 'Forever Move', 'cc_value' => 0.290, 'price_bs' => 650.00, 'stock' => 15, 'categoria' => 'Cuidado Personal'],
            ['sku' => '061', 'name' => 'Aloe Vera Gelly', 'cc_value' => 0.056, 'price_bs' => 120.00, 'stock' => 100, 'categoria' => 'Cuidado Personal'],
            ['sku' => '063', 'name' => 'Aloe Moisturizing Lotion', 'cc_value' => 0.056, 'price_bs' => 120.00, 'stock' => 60, 'categoria' => 'Cuidado Personal'],
            ['sku' => '617', 'name' => 'Aloe Sunscreen', 'cc_value' => 0.083, 'price_bs' => 160.00, 'stock' => 40, 'categoria' => 'Cuidado Personal'],
            ['sku' => '238', 'name' => 'Forever Aloe Scrub', 'cc_value' => 0.065, 'price_bs' => 135.00, 'stock' => 45, 'categoria' => 'Cuidado Personal'],
            ['sku' => '647', 'name' => 'Aloe Body Lotion', 'cc_value' => 0.091, 'price_bs' => 190.00, 'stock' => 50, 'categoria' => 'Cuidado Personal'],
            ['sku' => '022', 'name' => 'Forever Aloe Lips', 'cc_value' => 0.016, 'price_bs' => 35.00, 'stock' => 200, 'categoria' => 'Cuidado Personal'],
            ['sku' => '612', 'name' => 'Aloe Activator', 'cc_value' => 0.064, 'price_bs' => 120.00, 'stock' => 40, 'categoria' => 'Cuidado Personal'],
            ['sku' => '028', 'name' => 'Forever Bright Toothgel', 'cc_value' => 0.032, 'price_bs' => 70.00, 'stock' => 150, 'categoria' => 'Cuidado Personal'],
        ];

        foreach ($products as $product) {
            Product::create([
                'sku'          => $product['sku'],
                'name'         => $product['name'],       
                'cc_value'     => $product['cc_value'],   
                'price_bs'     => $product['price_bs'],   
                'stock'        => $product['stock'],
                'categoria'    => $product['categoria'], // 🔥 INYECTAMOS LA CATEGORÍA
                'foto_persona' => null                    
            ]);
        }
    }
}