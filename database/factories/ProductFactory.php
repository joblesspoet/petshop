<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            //
            'category_id' => Category::inRandomOrder()->first()->id,
            'title' => $this->faker->text(100),
            'price' => $this->faker->randomFloat(),
            'description' => $this->faker->paragraph(),
            'metadata' => [],
        ];
    }
}
