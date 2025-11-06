<?php

namespace Database\Factories;

use App\Models\BusinessPartner;
use App\Models\Company;
use App\Models\Currency;
use App\Models\SupplierInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\SupplierInvoice>
 */
class SupplierInvoiceFactory extends Factory
{
    protected $model = SupplierInvoice::class;

    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 1000, 50000);
        $taxAmount = $subtotal * 0.06; // 6% tax
        $totalAmount = $subtotal + $taxAmount;

        return [
            'company_id' => Company::factory(),
            'supplier_id' => BusinessPartner::factory()->create(['is_supplier' => true])->id,
            'currency_id' => Currency::factory(),
            'supplier_invoice_number' => $this->faker->unique()->bothify('INV-####'),
            'invoice_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'due_date' => $this->faker->dateTimeBetween('now', '+60 days'),
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => 0,
            'total_amount' => $totalAmount,
            'paid_amount' => 0,
            'outstanding_amount' => $totalAmount,
            'description' => $this->faker->optional()->sentence(),
            'notes' => $this->faker->optional()->paragraph(),
        ];
    }
}
