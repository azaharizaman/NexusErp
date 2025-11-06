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
        $totalAmount = $this->faker->randomFloat(2, 100, 10000);
        $paidAmount = 0.00;
        
        return [
            'invoice_number' => 'SI-' . now()->year . '-' . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'company_id' => \App\Models\Company::factory(),
            'supplier_id' => \App\Models\BusinessPartner::factory(['is_supplier' => true]),
            'currency_id' => \App\Models\Currency::factory(),
            'supplier_invoice_number' => 'SUPP-' . $this->faker->unique()->numerify('INV-####'),
            'invoice_date' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'due_date' => $this->faker->dateTimeBetween('now', '+60 days'),
            'status' => 'draft',
            'subtotal' => $totalAmount,
            'tax_amount' => 0.00,
            'discount_amount' => 0.00,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'outstanding_amount' => $totalAmount - $paidAmount,
            'description' => $this->faker->optional()->sentence(),
            'notes' => $this->faker->optional()->paragraph(),
        ];
    }

    /**
     * Indicate that the invoice is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by' => \App\Models\User::factory(),
            'approved_at' => now(),
        ]);
    }

    /**
     * Indicate that the invoice is partially paid.
     */
    public function partiallyPaid(): static
    {
        return $this->state(function (array $attributes) {
            $totalAmount = $attributes['total_amount'];
            $paidAmount = $totalAmount * 0.5; // 50% paid
            
            return [
                'paid_amount' => $paidAmount,
                'outstanding_amount' => $totalAmount - $paidAmount,
            ];
        });
    }

    /**
     * Indicate that the invoice is fully paid.
     */
    public function paid(): static
    {
        return $this->state(function (array $attributes) {
            $totalAmount = $attributes['total_amount'];
            
            return [
                'paid_amount' => $totalAmount,
                'outstanding_amount' => 0.00,
            ];
        });
    }

    /**
     * Indicate that the invoice is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
        ]);
    }

    /**
     * Indicate that the invoice is posted to GL.
     */
    public function postedToGl(): static
    {
        return $this->state(fn (array $attributes) => [
            'journal_entry_id' => \App\Models\JournalEntry::factory(),
        ]);
    }
}
