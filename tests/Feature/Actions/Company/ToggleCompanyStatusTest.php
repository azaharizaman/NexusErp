<?php

namespace Tests\Feature\Actions\Company;

use App\Actions\Company\ToggleCompanyStatus;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToggleCompanyStatusTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_it_can_toggle_company_status()
    {
        $company = Company::factory()->create(['is_active' => true]);

        $this->assertTrue($company->is_active);

        // Toggle to inactive
        $updatedCompany = ToggleCompanyStatus::run($company);

        $this->assertFalse($updatedCompany->is_active);
        $companiesTable = $company->getTable();

        $this->assertDatabaseHas($companiesTable, [
            'id' => $company->id,
            'is_active' => false,
        ]);

        // Toggle back to active
        $reactivatedCompany = ToggleCompanyStatus::run($updatedCompany);

        $this->assertTrue($reactivatedCompany->is_active);
        $this->assertDatabaseHas($companiesTable, [
            'id' => $company->id,
            'is_active' => true,
        ]);
    }

    public function test_it_can_specifically_mark_company_as_inactive()
    {
        $company = Company::factory()->create(['is_active' => true]);
        $action = new ToggleCompanyStatus;

        $updatedCompany = $action->markInactive($company);

        $this->assertFalse($updatedCompany->is_active);
    }

    public function test_it_can_specifically_mark_company_as_active()
    {
        $company = Company::factory()->create(['is_active' => false]);
        $action = new ToggleCompanyStatus;

        $updatedCompany = $action->markActive($company);

        $this->assertTrue($updatedCompany->is_active);
    }

    public function test_it_can_force_set_status()
    {
        $company = Company::factory()->create(['is_active' => true]);

        // Force set to inactive
        $updatedCompany = ToggleCompanyStatus::run($company, false);
        $this->assertFalse($updatedCompany->is_active);

        // Force set to active
        $reactivatedCompany = ToggleCompanyStatus::run($company, true);
        $this->assertTrue($reactivatedCompany->is_active);
    }

    public function test_it_provides_appropriate_success_messages()
    {
        $company = Company::factory()->create(['is_active' => true, 'name' => 'Test Company']);
        $action = new ToggleCompanyStatus;

        // Test inactive message
        $inactiveCompany = $action->markInactive($company);
        $message = $action->getSuccessMessage($inactiveCompany);
        $this->assertStringContainsString('deactivated successfully', $message);
        $this->assertStringContainsString('Test Company', $message);

        // Test active message
        $activeCompany = $action->markActive($inactiveCompany);
        $message = $action->getSuccessMessage($activeCompany);
        $this->assertStringContainsString('activated successfully', $message);
        $this->assertStringContainsString('Test Company', $message);
    }

    public function test_it_updates_status_tracking_fields()
    {
        $company = Company::factory()->create(['is_active' => true]);

        $updatedCompany = ToggleCompanyStatus::run($company);

        // Check that tracking fields are updated (if they exist in your model)
        $this->assertNotNull($updatedCompany->updated_at);
        // Add assertions for status_changed_at and status_changed_by if you have them
    }

    public function test_action_as_controller_method()
    {
        $company = Company::factory()->create(['is_active' => true]);
        $action = new ToggleCompanyStatus;

        $result = $action->asController($company);

        $this->assertInstanceOf(Company::class, $result);
        $this->assertFalse($result->is_active);
    }
}
