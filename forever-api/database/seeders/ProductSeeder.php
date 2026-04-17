<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Product as ModelsProduct;
use App\Models\Product as AppModelsProduct;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Limpiamos la tabla
        // Primero borramos las ventas para evitar el error de Foreign Key
        DB::table('sales')->delete();
        DB::table('products')->delete();
        
        // Reiniciamos el contador de ID para que el catálogo empiece desde 1
        DB::statement('ALTER SEQUENCE products_id_seq RESTART WITH 1');

        // 2. Definimos los productos (Datos del catálogo Forever Bolivia)
        $products = [
            ['sku' => '015', 'name' => 'Forever Aloe Vera Gel (1L)', 'cc_value' => 0.094, 'price_bs' => 215.00, 'stock' => 25],
            ['sku' => '034', 'name' => 'Forever Aloe Berry Nectar (1L)', 'cc_value' => 0.094, 'price_bs' => 215.00, 'stock' => 15],
            ['sku' => '077', 'name' => "Forever Aloe Bits N' Peaches", 'cc_value' => 0.094, 'price_bs' => 215.00, 'stock' => 10],
            ['sku' => '028', 'name' => 'Forever Bright Toothgel', 'cc_value' => 0.032, 'price_bs' => 70.00, 'stock' => 50],
            ['sku' => '067', 'name' => 'Aloe Ever-Shield (Desodorante)', 'cc_value' => 0.027, 'price_bs' => 65.00, 'stock' => 40],
            ['sku' => '064', 'name' => 'Aloe Heat Lotion', 'cc_value' => 0.056, 'price_bs' => 130.00, 'stock' => 20],
            ['sku' => '376', 'name' => 'Forever Arctic Sea (Omega-3)', 'cc_value' => 0.127, 'price_bs' => 250.00, 'stock' => 12],
        ];

        // 3. Insertamos usando el modelo
        foreach ($products as $product) {
            Product::create($product);
        }
    }
}