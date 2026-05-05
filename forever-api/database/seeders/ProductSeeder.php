<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Limpiamos las tablas en orden para evitar errores de llaves foráneas
        // Primero los detalles, luego las cabeceras, al final los productos
        DB::table('item_de_ventas')->delete();
        DB::table('ventas')->delete();
        DB::table('products')->delete();
        
        // Reiniciamos el contador de ID (PostgreSQL)
        DB::statement('ALTER SEQUENCE products_id_seq RESTART WITH 1');

        // 2. Definimos la lista completa de productos
        $products = [
            // BEBIDAS
            ['sku' => '015', 'name' => 'Forever Aloe Vera Gel', 'cc_value' => 0.094, 'price_bs' => 215.00, 'stock' => 50],
            ['sku' => '077', 'name' => "Forever Aloe Bits N' Peaches", 'cc_value' => 0.094, 'price_bs' => 215.00, 'stock' => 30],
            ['sku' => '034', 'name' => 'Forever Aloe Berry Nectar', 'cc_value' => 0.094, 'price_bs' => 215.00, 'stock' => 30],
            ['sku' => '200', 'name' => 'Aloe Blossom Herbal Tea', 'cc_value' => 0.070, 'price_bs' => 150.00, 'stock' => 100],
            ['sku' => '196', 'name' => 'Forever Freedom', 'cc_value' => 0.133, 'price_bs' => 280.00, 'stock' => 20],

            // NUTRICIÓN DIARIA
            ['sku' => '376', 'name' => 'Forever Arctic Sea', 'cc_value' => 0.127, 'price_bs' => 250.00, 'stock' => 40],
            ['sku' => '354', 'name' => 'Forever Kids', 'cc_value' => 0.060, 'price_bs' => 120.00, 'stock' => 60],
            ['sku' => '439', 'name' => 'Forever Daily', 'cc_value' => 0.080, 'price_bs' => 180.00, 'stock' => 50],
            ['sku' => '206', 'name' => 'Forever Calcium', 'cc_value' => 0.089, 'price_bs' => 200.00, 'stock' => 30],
            ['sku' => '037', 'name' => 'Forever Nature-Min', 'cc_value' => 0.088, 'price_bs' => 170.00, 'stock' => 40],
            ['sku' => '068', 'name' => 'Forever Fields of Greens', 'cc_value' => 0.050, 'price_bs' => 110.00, 'stock' => 50],
            ['sku' => '610', 'name' => 'Forever Active Pro-B', 'cc_value' => 0.147, 'price_bs' => 280.00, 'stock' => 25],
            ['sku' => '464', 'name' => 'Forever Fiber', 'cc_value' => 0.115, 'price_bs' => 210.00, 'stock' => 30],

            // NUTRICIÓN FOCALIZADA
            ['sku' => '624', 'name' => 'Forever iVision', 'cc_value' => 0.140, 'price_bs' => 280.00, 'stock' => 20],
            ['sku' => '188', 'name' => 'Forever B12 Plus', 'cc_value' => 0.056, 'price_bs' => 130.00, 'stock' => 40],
            ['sku' => '072', 'name' => 'Forever Lycium Plus', 'cc_value' => 0.124, 'price_bs' => 250.00, 'stock' => 20],
            ['sku' => '215', 'name' => 'Forever Multi-Maca', 'cc_value' => 0.106, 'price_bs' => 220.00, 'stock' => 25],
            ['sku' => '556', 'name' => 'Infinite Firming Complex', 'cc_value' => 0.207, 'price_bs' => 450.00, 'stock' => 15],
            ['sku' => '375', 'name' => 'Vitalize Woman', 'cc_value' => 0.127, 'price_bs' => 240.00, 'stock' => 20],

            // SALUD INMUNE
            ['sku' => '566', 'name' => 'Forever Immune Gummy', 'cc_value' => 0.148, 'price_bs' => 250.00, 'stock' => 35],
            ['sku' => '065', 'name' => 'Forever Garlic-Thyme', 'cc_value' => 0.079, 'price_bs' => 160.00, 'stock' => 40],
            ['sku' => '048', 'name' => 'Forever Absorbent-C', 'cc_value' => 0.065, 'price_bs' => 140.00, 'stock' => 60],
            ['sku' => '355', 'name' => 'Forever ImmuBlend', 'cc_value' => 0.118, 'price_bs' => 230.00, 'stock' => 25],

            // VIDA ACTIVA
            ['sku' => '473', 'name' => 'ARGI+', 'cc_value' => 0.293, 'price_bs' => 680.00, 'stock' => 20],
            ['sku' => '064', 'name' => 'Aloe Heat Lotion', 'cc_value' => 0.056, 'price_bs' => 130.00, 'stock' => 50],
            ['sku' => '205', 'name' => 'Aloe MSM Gel', 'cc_value' => 0.084, 'price_bs' => 180.00, 'stock' => 40],
            ['sku' => '264', 'name' => 'Forever Active HA', 'cc_value' => 0.136, 'price_bs' => 260.00, 'stock' => 20],
            ['sku' => '551', 'name' => 'Forever Move', 'cc_value' => 0.290, 'price_bs' => 650.00, 'stock' => 15],
            ['sku' => '470', 'name' => 'Forever Lite Ultra Vanilla', 'cc_value' => 0.122, 'price_bs' => 280.00, 'stock' => 30],
            ['sku' => '471', 'name' => 'Forever Lite Ultra Chocolate', 'cc_value' => 0.122, 'price_bs' => 280.00, 'stock' => 30],

            // CONTROL DE PESO
            ['sku' => '289', 'name' => 'Forever Lean', 'cc_value' => 0.167, 'price_bs' => 350.00, 'stock' => 25],
            ['sku' => '071', 'name' => 'Forever Garcinia Plus', 'cc_value' => 0.124, 'price_bs' => 250.00, 'stock' => 30],
            ['sku' => '463', 'name' => 'Forever Therm', 'cc_value' => 0.114, 'price_bs' => 230.00, 'stock' => 40],

            // COMBOS (Packs)
            ['sku' => '801', 'name' => 'Fast Start Pack', 'cc_value' => 2.000, 'price_bs' => 3500.00, 'stock' => 10],
            ['sku' => '001', 'name' => 'A Touch of Forever', 'cc_value' => 2.000, 'price_bs' => 3500.00, 'stock' => 5],
            ['sku' => '810', 'name' => 'Personal Care Pack', 'cc_value' => 0.145, 'price_bs' => 350.00, 'stock' => 20],
            ['sku' => '805', 'name' => 'C9 Pack Nutricional', 'cc_value' => 0.675, 'price_bs' => 1250.00, 'stock' => 15],

            // COLMENA
            ['sku' => '207', 'name' => 'Forever Bee Honey', 'cc_value' => 0.079, 'price_bs' => 145.00, 'stock' => 40],
            ['sku' => '036', 'name' => 'Forever Royal Jelly', 'cc_value' => 0.130, 'price_bs' => 270.00, 'stock' => 20],
            ['sku' => '051', 'name' => 'Aloe Propolis Creme', 'cc_value' => 0.079, 'price_bs' => 180.00, 'stock' => 50],
            ['sku' => '027', 'name' => 'Forever Bee Propolis', 'cc_value' => 0.127, 'price_bs' => 250.00, 'stock' => 30],
            ['sku' => '026', 'name' => 'Forever Bee Pollen', 'cc_value' => 0.063, 'price_bs' => 130.00, 'stock' => 60],

            // CUIDADO DE LA PIEL
            ['sku' => '061', 'name' => 'Aloe Vera Gelly', 'cc_value' => 0.056, 'price_bs' => 120.00, 'stock' => 100],
            ['sku' => '063', 'name' => 'Aloe Moisturizing Lotion', 'cc_value' => 0.056, 'price_bs' => 120.00, 'stock' => 60],
            ['sku' => '617', 'name' => 'Aloe Sunscreen', 'cc_value' => 0.083, 'price_bs' => 160.00, 'stock' => 40],
            ['sku' => '238', 'name' => 'Forever Aloe Scrub', 'cc_value' => 0.065, 'price_bs' => 135.00, 'stock' => 45],
            ['sku' => '647', 'name' => 'Aloe Body Lotion', 'cc_value' => 0.091, 'price_bs' => 190.00, 'stock' => 50],
            ['sku' => '022', 'name' => 'Forever Aloe Lips', 'cc_value' => 0.016, 'price_bs' => 35.00, 'stock' => 200],
            ['sku' => '612', 'name' => 'Aloe Activator', 'cc_value' => 0.064, 'price_bs' => 120.00, 'stock' => 40],
            ['sku' => '028', 'name' => 'Forever Bright Toothgel', 'cc_value' => 0.032, 'price_bs' => 70.00, 'stock' => 150],
        ];

        // 3. Insertamos usando el modelo
        foreach ($products as $product) {
            Product::create($product);
        }
    }
}