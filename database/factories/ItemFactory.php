<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            "category_id" => $this->faker->numberBetween(1, 5),
            "location_id" => $this->faker->numberBetween(1, 4),
            "name" => $this->faker->words(3, true),
            "code" =>
                "BRG-" .
                strtoupper($this->faker->unique()->lexify("???")) .
                $this->faker->numberBetween(10, 99),
            "unit" => $this->faker->randomElement([
                "pcs",
                "unit",
                "set",
                "buah",
                "lembar",
            ]),
            "stock" => $this->faker->numberBetween(0, 50),
            "minimum_stock" => 5,
            "photo" => null,
            "description" => $this->faker->sentence(),
        ];
    }
}
