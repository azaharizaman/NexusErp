<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JournalEntry>
 */
class JournalEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalAmount = $this->faker->randomFloat(4, 100, 10000);
        
        return [
            'journal_entry_number' => 'JE-' . now()->year . '-' . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'company_id' => \App\Models\Company::factory(),
            'fiscal_year_id' => \App\Models\FiscalYear::factory(),
            'accounting_period_id' => \App\Models\AccountingPeriod::factory(),
            'entry_type' => $this->faker->randomElement(['general', 'sales', 'purchases', 'payroll', 'adjusting', 'closing']),
            'entry_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'posting_date' => null,
            'reference_number' => $this->faker->optional()->numerify('REF-####'),
            'description' => $this->faker->sentence(),
            'status' => 'draft',
            'currency_id' => \App\Models\Currency::factory(),
            'exchange_rate' => 1.000000,
            'total_debit' => $totalAmount,
            'total_credit' => $totalAmount,
        ];
    }

    /**
     * Indicate that the journal entry is posted.
     */
    public function posted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'posted',
            'posting_date' => now(),
            'posted_by' => \App\Models\User::factory(),
        ]);
    }
}
