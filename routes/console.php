<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Actions\Utils\GenerateInvoiceNumber;
use App\Console\Commands\ManageUserPermissionCommand;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Generate invoice numbers using Laravel Action
Artisan::command('invoice:generate-number 
                  {--prefix=INV- : The prefix for the invoice number}
                  {--length=6 : The length of the numeric part}
                  {--count=1 : Number of invoice numbers to generate}
                  {--from= : Generate from this number}', function () {
    
    $prefix = $this->option('prefix');
    $length = (int) $this->option('length');
    $count = (int) $this->option('count');
    $from = $this->option('from');

    if ($count === 1) {
        // Generate single number
        $number = GenerateInvoiceNumber::run($prefix, $length, $from);
        $this->info("Generated invoice number: {$number}");
    } else {
        // Generate multiple numbers
        $action = new GenerateInvoiceNumber();
        $numbers = $action->handleBatch($count, $prefix, $length, $from);
        
        $this->info("Generated {$count} invoice numbers:");
        foreach ($numbers as $number) {
            $this->line("  - {$number}");
        }
    }
})->purpose('Generate invoice numbers using Laravel Action');

// User permission management command
Artisan::command('user:permission {action} {email} {permission}', function () {
    $command = new ManageUserPermissionCommand();
    return $command->handle();
})->purpose('Assign or revoke permissions for a user');
