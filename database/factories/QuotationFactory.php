<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quotation>
 */
class QuotationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 1000, 50000);
        $taxRate = $this->faker->randomFloat(2, 0, 15);
        $taxAmount = $subtotal * ($taxRate / 100);
        
        return [
            'quotation_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'valid_until' => $this->faker->dateTimeBetween('now', '+2 months'),
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $subtotal + $taxAmount,
            'status' => $this->faker->randomElement(['draft', 'submitted', 'accepted', 'rejected']),
            'terms_and_conditions' => $this->faker->optional()->paragraph(),
            'notes' => $this->faker->optional()->paragraph(),
            'delivery_lead_time_days' => $this->faker->numberBetween(1, 60),
            'payment_terms' => $this->faker->optional()->sentence(),
            'is_recommended' => false,
        ];
    }
}
