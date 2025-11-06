<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AccountingPeriod>
 */
class AccountingPeriodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $periodNumber = $this->faker->numberBetween(1, 12);
        $year = $this->faker->year();
        $startDate = \Carbon\Carbon::parse($year . '-' . str_pad($periodNumber, 2, '0', STR_PAD_LEFT) . '-01');
        
        return [
            'fiscal_year_id' => \App\Models\FiscalYear::factory(),
            'period_name' => \Carbon\Carbon::parse($startDate)->format('F Y'),
            'period_code' => 'P' . str_pad($periodNumber, 2, '0', STR_PAD_LEFT) . '-' . $year,
            'period_type' => 'monthly',
            'period_number' => $periodNumber,
            'start_date' => $startDate,
            'end_date' => $startDate->copy()->endOfMonth(),
            'is_adjusting_period' => false,
        ];
    }
}
