<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
{
    return [
        'sku'          => 'FL-' . $this->faker->unique()->numberBetween(100, 999),
        'name'         => $this->faker->words(3, true),
        'price_bs'     => $this->faker->randomFloat(2, 50, 500),
        'cc_value'     => $this->faker->randomFloat(3, 0.1, 1.5),
        'stock'        => $this->faker->numberBetween(0, 100),
        'image_base64' => null, // Opcional: puedes poner un string base64 corto aquí
    ];
}
}