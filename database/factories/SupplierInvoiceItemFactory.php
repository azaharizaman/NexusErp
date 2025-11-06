<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SupplierInvoiceItem>
 */
class SupplierInvoiceItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 100);
        $unitPrice = $this->faker->randomFloat(2, 10, 1000);
        $lineTotal = $quantity * $unitPrice;

        return [
            'supplier_invoice_id' => \App\Models\SupplierInvoice::factory(),
            'item_code' => 'ITEM-' . $this->faker->unique()->numberBetween(1000, 9999),
            'item_description' => $this->faker->words(3, true),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal,
            'tax_rate' => 10.00,
            'tax_amount' => $lineTotal * 0.10,
            'discount_percent' => 0.00,
            'discount_amount' => 0.00,
            'sort_order' => 0,
        ];
    }
}
