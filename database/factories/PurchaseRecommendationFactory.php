<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseRecommendation>
 */
class PurchaseRecommendationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'recommendation_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'status' => $this->faker->randomElement(['draft', 'submitted', 'approved', 'rejected']),
            'justification' => $this->faker->paragraph(),
            'comparison_notes' => $this->faker->optional()->paragraph(),
            'recommended_total' => $this->faker->randomFloat(2, 5000, 100000),
        ];
    }
}
