<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Currency>
 */
class CurrencyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->currencyCode(),
            'name' => fake()->word(),
            'symbol' => fake()->randomElement(['$', '€', '£', '¥', '₹']),
            'is_active' => true,
            'is_base' => false,
        ];
    }

    /**
     * Indicate that the currency is the base currency.
     */
    public function base(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_base' => true,
        ]);
    }
}
