<?php

namespace Database\Factories;

use App\Models\BusinessPartner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\BusinessPartner>
 */
class BusinessPartnerFactory extends Factory
{
    protected $model = BusinessPartner::class;

    public function definition(): array
    {
        $isSupplier = $this->faker->boolean(60);
        $isCustomer = $isSupplier ? $this->faker->boolean(40) : true;

        return [
            'name' => $this->faker->company(),
            'code' => $this->faker->unique()->bothify('BP-#####'),
            'is_supplier' => $isSupplier,
            'is_customer' => $isCustomer,
            'email' => $this->faker->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'website' => $this->faker->url(),
            'notes' => $this->faker->optional()->paragraph(),
        ];
    }
}
