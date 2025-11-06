<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FiscalYear>
 */
class FiscalYearFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = $this->faker->year();
        $startDate = \Carbon\Carbon::parse($year . '-01-01');
        
        return [
            'name' => 'FY ' . $year,
            'code' => 'FY' . $year,
            'start_date' => $startDate,
            'end_date' => $startDate->copy()->endOfYear(),
            'company_id' => \App\Models\Company::factory(),
            'is_default' => false,
            'is_locked' => false,
        ];
    }
}
