<?php

namespace Database\Seeders;

use App\Models\BusinessPartner;
use App\Models\BusinessPartnerContact;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessPartnerSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $parent = BusinessPartner::factory()->create([
                'name' => 'Global Tech Holdings',
                'code' => 'BP-GTH',
                'is_supplier' => true,
                'is_customer' => true,
                'email' => 'hq@globaltech.example',
                'phone' => '+1-555-0001',
                'website' => 'https://globaltech.example',
                'notes' => 'Primary master entity for Global Tech operations.',
            ]);

            BusinessPartnerContact::factory()->create([
                'business_partner_id' => $parent->id,
                'name' => 'Alicia Chen',
                'job_title' => 'Global Account Director',
                'email' => 'alicia.chen@globaltech.example',
                'phone' => '+1-555-1001',
            ]);

            $regional = BusinessPartner::factory()->create([
                'name' => 'Global Tech (APAC) Ltd',
                'code' => 'BP-GT-APAC',
                'parent_business_partner_id' => $parent->id,
                'is_supplier' => true,
                'is_customer' => false,
                'email' => 'hello@gt-apac.example',
                'phone' => '+65-555-2001',
                'website' => 'https://apac.globaltech.example',
                'notes' => 'Regional subsidiary serving Asia-Pacific customers.',
            ]);

            BusinessPartnerContact::factory()->create([
                'business_partner_id' => $regional->id,
                'name' => 'Farah Malik',
                'job_title' => 'Regional Sales Lead',
                'email' => 'farah.malik@gt-apac.example',
                'phone' => '+65-555-2002',
                'mobile' => '+65-555-9990',
            ]);

            BusinessPartner::factory()
                ->count(3)
                ->has(BusinessPartnerContact::factory()->count(2), 'contacts')
                ->create([
                    'parent_business_partner_id' => $parent->id,
                ]);
        });
    }
}
