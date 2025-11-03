<?php

namespace Database\Factories;

use App\Models\BusinessPartner;
use App\Models\BusinessPartnerContact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\BusinessPartnerContact>
 */
class BusinessPartnerContactFactory extends Factory
{
    protected $model = BusinessPartnerContact::class;

    public function definition(): array
    {
        return [
            'business_partner_id' => BusinessPartner::factory(),
            'name' => $this->faker->name(),
            'job_title' => $this->faker->optional()->jobTitle(),
            'email' => $this->faker->optional()->companyEmail(),
            'phone' => $this->faker->optional()->phoneNumber(),
            'mobile' => $this->faker->optional()->phoneNumber(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
