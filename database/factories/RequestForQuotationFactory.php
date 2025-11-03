<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RequestForQuotation>
 */
class RequestForQuotationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rfq_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'expiry_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'status' => $this->faker->randomElement(['draft', 'sent', 'received', 'evaluated', 'closed']),
            'description' => $this->faker->sentence(),
            'terms_and_conditions' => $this->faker->paragraph(),
            'notes' => $this->faker->optional()->paragraph(),
        ];
    }
}
