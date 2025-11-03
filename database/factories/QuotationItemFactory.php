<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QuotationItem>
 */
class QuotationItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->randomFloat(2, 1, 100);
        $unitPrice = $this->faker->randomFloat(2, 10, 1000);
        $lineTotal = $quantity * $unitPrice;
        $taxRate = $this->faker->randomFloat(2, 0, 15);
        $taxAmount = $lineTotal * ($taxRate / 100);

        return [
            'item_description' => $this->faker->words(3, true),
            'item_code' => $this->faker->optional()->bothify('ITM-####'),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'specifications' => $this->faker->optional()->paragraph(),
            'notes' => $this->faker->optional()->sentence(),
            'sort_order' => 0,
        ];
    }
}
