<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SupplierInvoice>
 */
class SupplierInvoiceFactory extends Factory
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
            'supplier_invoice_number' => 'SI-' . $this->faker->unique()->numberBetween(1000, 9999),
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => $this->faker->randomFloat(2, 100, 10000),
            'tax_amount' => function (array $attributes) {
                return $attributes['subtotal'] * 0.10;
            },
            'discount_amount' => 0.00,
            'total_amount' => function (array $attributes) {
                return $attributes['subtotal'] + $attributes['tax_amount'] - $attributes['discount_amount'];
            },
            'paid_amount' => 0.00,
            'outstanding_amount' => function (array $attributes) {
                return $attributes['total_amount'];
            },
            'status' => 'draft',
            'is_posted_to_gl' => false,
        ];
    }
}
