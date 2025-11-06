<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DebitNote>
 */
class DebitNoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => \App\Models\Company::factory(),
            'supplier_id' => \AzahariZaman\Backoffice\Models\BusinessPartner::factory()->state(['is_supplier' => true]),
            'currency_id' => \App\Models\Currency::factory(),
            'debit_note_date' => now(),
            'reason' => $this->faker->randomElement(['return', 'price_adjustment', 'error_correction', 'damage']),
            'amount' => $this->faker->randomFloat(2, 50, 5000),
            'status' => 'draft',
            'is_posted_to_gl' => false,
        ];
    }
}
