<?php

namespace Database\Seeders;

use AzahariZaman\BackOffice\Models\Company;
use AzahariZaman\BackOffice\Models\Department;
use AzahariZaman\BackOffice\Models\Office;
use AzahariZaman\BackOffice\Models\OfficeType;
use AzahariZaman\BackOffice\Models\Position;
use AzahariZaman\BackOffice\Models\Staff;
use AzahariZaman\BackOffice\Models\StaffTransfer;
use AzahariZaman\BackOffice\Models\Unit;
use AzahariZaman\BackOffice\Models\UnitGroup;
use Illuminate\Database\Seeder;

class BackOfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data to avoid unique constraint violations
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        \AzahariZaman\BackOffice\Models\StaffTransfer::truncate();
        \AzahariZaman\BackOffice\Models\Staff::truncate();
        \AzahariZaman\BackOffice\Models\Unit::truncate();
        \AzahariZaman\BackOffice\Models\UnitGroup::truncate();
        \AzahariZaman\BackOffice\Models\Position::truncate();
        \AzahariZaman\BackOffice\Models\Department::truncate();
        \AzahariZaman\BackOffice\Models\Office::truncate();
        \AzahariZaman\BackOffice\Models\OfficeType::truncate();
        \AzahariZaman\BackOffice\Models\Company::truncate();

        // Truncate pivot tables
        \DB::table('backoffice_office_office_type')->truncate();
        \DB::table('backoffice_staff_unit')->truncate();

        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        // Create office types
        $officeTypes = OfficeType::factory()->count(3)->create();

        // Create positions
        $positions = Position::factory()->count(5)->create();

        // Create parent company
        $parentCompany = Company::factory()->create([
            'name' => 'Nexus Corporation',
            'code' => 'NXS',
            'description' => 'Leading technology solutions provider',
        ]);

        // Create child companies
        $childCompanies = Company::factory()->count(1)->childOf($parentCompany)->create();

        // Create unit groups for the parent company
        $unitGroups = UnitGroup::factory()->count(2)->for($parentCompany)->create();

        // Create units for each unit group
        foreach ($unitGroups as $unitGroup) {
            Unit::factory()->count(rand(1, 2))->for($unitGroup)->create();
        }

        // Create offices for parent company
        $headOffice = Office::factory()->for($parentCompany)->create([
            'name' => 'Head Office',
            'code' => 'HO',
        ]);

        $regionalOffices = Office::factory()->count(2)->for($parentCompany)->create();

        // Create child offices under head office
        $branchOffices = Office::factory()->count(1)->childOf($headOffice)->create();

        // Attach office types to offices
        try {
            $headOffice->officeTypes()->attach($officeTypes->first()->id);
            $regionalOfficeTypeIndex = 0;
            foreach ($regionalOffices as $office) {
                $office->officeTypes()->attach($officeTypes->skip($regionalOfficeTypeIndex % $officeTypes->count())->first()->id);
                $regionalOfficeTypeIndex++;
            }
            $branchOfficeTypeIndex = 0;
            foreach ($branchOffices as $office) {
                $office->officeTypes()->attach($officeTypes->skip($branchOfficeTypeIndex % $officeTypes->count())->first()->id);
                $branchOfficeTypeIndex++;
            }
        } catch (\Exception $e) {
            // Skip office type attachment if there are issues
            $this->command->warn('Skipped office type attachments due to constraint issues');
        }

        // Create departments for parent company
        $departments = Department::factory()->count(4)->for($parentCompany)->create();

        // Create CEO
        $ceo = Staff::factory()->ceo()->inOffice($headOffice)->create([
            'first_name' => 'John',
            'last_name' => 'Smith',
            'position_id' => $positions->random()->id,
        ]);

        // Create senior managers reporting to CEO
        $seniorManagers = collect();
        foreach (range(1, 3) as $i) {
            $position = $positions->random();
            $seniorManager = Staff::factory()->withSupervisor($ceo)->inOffice($headOffice)->create([
                'position_id' => $position->id,
            ]);
            $seniorManagers->push($seniorManager);
        }

        // Create managers reporting to senior managers
        $managers = collect();
        foreach ($seniorManagers as $seniorManager) {
            $departmentManagers = collect();
            foreach (range(1, 1) as $i) {
                $position = $positions->random();
                $departmentManager = Staff::factory()->withSupervisor($seniorManager)->inOffice($headOffice)->create([
                    'department_id' => $departments->random()->id,
                    'position_id' => $position->id,
                ]);
                $departmentManagers->push($departmentManager);
            }
            $managers = $managers->merge($departmentManagers);
        }

        // Create regular employees
        $employees = collect();
        foreach ($managers as $manager) {
            $departmentEmployees = collect();
            foreach (range(1, rand(2, 3)) as $i) {
                $position = $positions->random();
                $departmentEmployee = Staff::factory()->withSupervisor($manager)->inOffice($headOffice)->create([
                    'department_id' => $departments->random()->id,
                    'position_id' => $position->id,
                ]);
                $departmentEmployees->push($departmentEmployee);
            }
            $employees = $employees->merge($departmentEmployees);
        }

        // Create staff in regional offices
        foreach ($regionalOffices as $regionalOffice) {
            // Regional manager
            $position = $positions->random();
            $regionalManager = Staff::factory()->withSupervisor($ceo)->inOffice($regionalOffice)->create([
                'department_id' => $departments->random()->id,
                'position_id' => $position->id,
            ]);

            // Regional employees
            foreach (range(1, rand(3, 4)) as $i) {
                $position = $positions->random();
                Staff::factory()->withSupervisor($regionalManager)->inOffice($regionalOffice)->create([
                    'department_id' => $departments->random()->id,
                    'position_id' => $position->id,
                ]);
            }
        }

        // Create some staff with different statuses
        Staff::factory()->create([
            'status' => \AzahariZaman\BackOffice\Enums\StaffStatus::INACTIVE,
            'office_id' => $headOffice->id,
            'department_id' => $departments->random()->id,
            'position_id' => $positions->random()->id,
        ]);

        Staff::factory()->create([
            'status' => \AzahariZaman\BackOffice\Enums\StaffStatus::TERMINATED,
            'office_id' => $headOffice->id,
            'department_id' => $departments->random()->id,
            'position_id' => $positions->random()->id,
        ]);

        Staff::factory()->create([
            'status' => \AzahariZaman\BackOffice\Enums\StaffStatus::ON_LEAVE,
            'office_id' => $headOffice->id,
            'department_id' => $departments->random()->id,
            'position_id' => $positions->random()->id,
        ]);

        // Create staff transfers in different states
        $transferStaff = Staff::where('is_active', true)->inRandomOrder()->limit(3)->get();

        foreach ($transferStaff as $staff) {
            // Create a pending transfer
            StaffTransfer::factory()->pending()->create([
                'staff_id' => $staff->id,
                'from_office_id' => $staff->office_id,
                'to_office_id' => Office::where('id', '!=', $staff->office_id)->inRandomOrder()->first()->id,
                'from_department_id' => $staff->department_id,
                'to_department_id' => Department::inRandomOrder()->first()->id,
                'requested_by_id' => $staff->id,
            ]);
        }

        // Create approved transfers
        $approvedTransferStaff = Staff::where('is_active', true)->inRandomOrder()->limit(2)->get();

        foreach ($approvedTransferStaff as $staff) {
            StaffTransfer::factory()->approved()->scheduled()->create([
                'staff_id' => $staff->id,
                'from_office_id' => $staff->office_id,
                'to_office_id' => Office::where('id', '!=', $staff->office_id)->inRandomOrder()->first()->id,
                'from_department_id' => $staff->department_id,
                'to_department_id' => Department::inRandomOrder()->first()->id,
                'requested_by_id' => $staff->id,
                'approved_by_id' => $ceo->id,
            ]);
        }

        // Create completed transfers
        $completedTransferStaff = Staff::where('is_active', true)->inRandomOrder()->limit(1)->get();

        foreach ($completedTransferStaff as $staff) {
            StaffTransfer::factory()->complete()->create([
                'staff_id' => $staff->id,
                'from_office_id' => $staff->office_id,
                'to_office_id' => Office::where('id', '!=', $staff->office_id)->inRandomOrder()->first()->id,
                'from_department_id' => $staff->department_id,
                'to_department_id' => Department::inRandomOrder()->first()->id,
                'requested_by_id' => $staff->id,
                'approved_by_id' => $ceo->id,
                'processed_by_id' => $ceo->id,
            ]);
        }

        // Assign some staff to units
        $allUnits = Unit::all();
        $staffToAssign = Staff::where('is_active', true)->inRandomOrder()->limit(5)->get();

        foreach ($staffToAssign as $staff) {
            $staff->units()->attach($allUnits->random(rand(1, 2)));
        }

        $this->command->info('BackOffice data seeded successfully!');
        $this->command->info('Created:');
        $this->command->info('- '.Company::count().' companies');
        $this->command->info('- '.Office::count().' offices');
        $this->command->info('- '.Department::count().' departments');
        $this->command->info('- '.Staff::count().' staff members');
        $this->command->info('- '.UnitGroup::count().' unit groups');
        $this->command->info('- '.Unit::count().' units');
        $this->command->info('- '.Position::count().' positions');
        $this->command->info('- '.OfficeType::count().' office types');
        $this->command->info('- '.StaffTransfer::count().' staff transfers');
    }
}
