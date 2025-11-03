<?php

namespace App\Actions\Utils;

use Illuminate\Support\Facades\Artisan;
use Lorisleiva\Actions\Concerns\AsAction;

class ConvertUnits
{
    use AsAction;

    /**
     * Convert value from one unit to another using UOM command
     */
    public function handle(float $value, string $fromUnit, string $toUnit): array
    {
        try {
            // Use the UOM convert command
            $exitCode = Artisan::call('uom:convert', [
                'quantity' => $value,
                'from' => $fromUnit,
                'to' => $toUnit,
                '--json' => true, // If the command supports JSON output
            ]);

            if ($exitCode === 0) {
                $output = Artisan::output();

                // Parse the conversion result
                // This is a simplified implementation - you may need to adjust based on actual command output
                if (preg_match('/(\d+\.?\d*)\s*'.preg_quote($toUnit).'/i', $output, $matches)) {
                    $convertedValue = (float) $matches[1];

                    return [
                        'success' => true,
                        'original_value' => $value,
                        'original_unit' => $fromUnit,
                        'converted_value' => $convertedValue,
                        'converted_unit' => $toUnit,
                        'conversion_rate' => $convertedValue / $value,
                        'output' => trim($output),
                    ];
                }
            }

            return [
                'success' => false,
                'error' => 'Conversion failed or could not parse result',
                'original_value' => $value,
                'original_unit' => $fromUnit,
                'target_unit' => $toUnit,
                'command_output' => Artisan::output(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'original_value' => $value,
                'original_unit' => $fromUnit,
                'target_unit' => $toUnit,
            ];
        }
    }

    /**
     * Convert multiple values at once
     */
    public function handleBatch(array $conversions): array
    {
        $results = [];

        foreach ($conversions as $index => $conversion) {
            $results[$index] = $this->handle(
                $conversion['value'],
                $conversion['from_unit'],
                $conversion['to_unit']
            );
        }

        return $results;
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'value' => ['required', 'numeric', 'min:0'],
            'from_unit' => ['required', 'string'],
            'to_unit' => ['required', 'string'],
        ];
    }

    /**
     * Batch validation rules
     */
    public function batchRules(): array
    {
        return [
            'conversions' => ['required', 'array', 'min:1'],
            'conversions.*.value' => ['required', 'numeric', 'min:0'],
            'conversions.*.from_unit' => ['required', 'string'],
            'conversions.*.to_unit' => ['required', 'string'],
        ];
    }
}
