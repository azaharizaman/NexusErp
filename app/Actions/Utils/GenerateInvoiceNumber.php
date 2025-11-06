<?php

namespace App\Actions\Utils;

use Lorisleiva\Actions\Concerns\AsAction;

class GenerateInvoiceNumber
{
    use AsAction;

    /**
     * Generate the next invoice number
     */
    public function handle(string $prefix = 'INV-', int $length = 6, ?string $lastNumber = null): string
    {
        if ($lastNumber) {
            // Extract the numeric part from the last number
            $numericPart = (int) str_replace($prefix, '', $lastNumber);
            $nextNumber = $numericPart + 1;
        } else {
            // Start from 1 if no last number provided
            $nextNumber = 1;
        }

        // Format with leading zeros
        $formattedNumber = str_pad($nextNumber, $length, '0', STR_PAD_LEFT);

        return $prefix . $formattedNumber;
    }

    /**
     * Generate multiple sequential numbers
     */
    public function handleBatch(int $count, string $prefix = 'INV-', int $length = 6, ?string $startFrom = null): array
    {
        $numbers = [];
        $currentNumber = $startFrom;

        for ($i = 0; $i < $count; $i++) {
            $currentNumber = $this->handle($prefix, $length, $currentNumber);
            $numbers[] = $currentNumber;
        }

        return $numbers;
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'prefix' => ['required', 'string', 'max:10'],
            'length' => ['required', 'integer', 'min:4', 'max:10'],
            'count' => ['sometimes', 'integer', 'min:1', 'max:1000'],
        ];
    }
}
