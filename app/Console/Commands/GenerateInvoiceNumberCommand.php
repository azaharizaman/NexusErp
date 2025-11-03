<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Actions\Utils\GenerateInvoiceNumber;

class GenerateInvoiceNumberCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'invoice:generate-number 
                            {--prefix=INV- : The prefix for the invoice number}
                            {--length=6 : The length of the numeric part}
                            {--count=1 : Number of invoice numbers to generate}
                            {--from= : Generate from this number}';

    /**
     * The console command description.
     */
    protected $description = 'Generate invoice numbers using Laravel Action';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
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

        return Command::SUCCESS;
    }
}