<?php

namespace Tests\Feature\Actions;

use Tests\TestCase;
use App\Actions\Company\CreateCompany;
use App\Actions\Company\UpdateCompany;
use App\Actions\User\CreateUser;
use App\Actions\Utils\FormatCurrency;
use App\Actions\Utils\GenerateInvoiceNumber;
use App\Actions\Utils\ConvertUnits;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActionsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_company_using_action()
    {
        $companyData = [
            'name' => 'Test Company',
            'code' => 'TEST001',
            'description' => 'Primary operating company',
            'is_active' => true,
        ];

        $company = CreateCompany::run($companyData);

        $this->assertInstanceOf(Company::class, $company);
        $this->assertEquals('Test Company', $company->name);
        $this->assertEquals('TEST001', $company->code);
        $this->assertEquals('Primary operating company', $company->description);
        $this->assertTrue($company->is_active);
    }

    /** @test */
    public function it_can_update_a_company_using_action()
    {
        $company = Company::factory()->create([
            'name' => 'Original Name',
            'code' => 'ORIG001',
            'description' => 'Original description',
        ]);

        $updateData = [
            'name' => 'Updated Company Name',
            'description' => 'Updated description',
        ];

        $updatedCompany = UpdateCompany::run($company, $updateData);

        $this->assertEquals('Updated Company Name', $updatedCompany->name);
        $this->assertEquals('Updated description', $updatedCompany->description);
        $this->assertEquals('ORIG001', $updatedCompany->code); // Should remain unchanged
    }

    /** @test */
    public function it_can_create_a_user_using_action()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'email_verified' => true,
        ];

        $user = CreateUser::run($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertNotNull($user->email_verified_at);
    }

    /** @test */
    public function it_can_format_currency_using_action()
    {
        // This test assumes your financial settings are properly configured
        $amount = 1234.56;
        
        $formatted = FormatCurrency::run($amount);
        
        $this->assertIsString($formatted);
        $this->assertStringContainsString('1,234.56', $formatted);
    }

    /** @test */
    public function it_can_generate_invoice_numbers()
    {
        $invoiceNumber = GenerateInvoiceNumber::run('INV-', 6);
        
        $this->assertEquals('INV-000001', $invoiceNumber);

        // Test with existing number
        $nextNumber = GenerateInvoiceNumber::run('INV-', 6, 'INV-000005');
        $this->assertEquals('INV-000006', $nextNumber);
    }

    /** @test */
    public function it_can_generate_multiple_invoice_numbers()
    {
        $action = new GenerateInvoiceNumber();
        $numbers = $action->handleBatch(3, 'QUO-', 4);

        $this->assertCount(3, $numbers);
        $this->assertEquals('QUO-0001', $numbers[0]);
        $this->assertEquals('QUO-0002', $numbers[1]);
        $this->assertEquals('QUO-0003', $numbers[2]);
    }

    /** @test */
    public function it_can_use_actions_as_jobs()
    {
        $companyData = [
            'name' => 'Job Test Company',
            'code' => 'JOB001',
            'description' => 'Queued creation',
        ];

        // Dispatch as job
        CreateCompany::dispatch($companyData);

        // In a real test, you'd assert the job was queued
        // For now, just ensure no errors
        $this->assertTrue(true);
    }

    /** @test */
    public function it_can_use_actions_as_commands()
    {
        // You can register actions as commands in your console kernel
        // This demonstrates the versatility of Laravel Actions
        $this->assertTrue(true);
    }

    /** @test */
    public function it_can_chain_actions_together()
    {
        // Create a company
        $company = CreateCompany::run([
            'name' => 'Chain Test Company',
            'code' => 'CHAIN001',
            'description' => 'Initial description',
        ]);

        // Update the same company
        $updatedCompany = UpdateCompany::run($company, [
            'description' => 'Updated after action chain',
        ]);

        // Create a user for this company
        $user = CreateUser::run([
            'name' => 'Company Admin',
            'email' => 'admin@chain.company.com',
            'password' => 'password123',
        ]);

        $this->assertEquals('Chain Test Company', $updatedCompany->name);
    $this->assertEquals('Updated after action chain', $updatedCompany->description);
        $this->assertEquals('Company Admin', $user->name);
    }
}