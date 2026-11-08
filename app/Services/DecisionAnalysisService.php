<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;

final class DecisionAnalysisService
{
    public const VERSION = '1.0.0';

    public const MAX_COST = 1_000_000_000;

    public const MAX_OPERATING_COST = 10_000_000;

    public const MAX_MONTHS = 240;

    public const FIELDS = [
        'repair_cost',
        'repair_lifetime_months',
        'replacement_cost',
        'replacement_lifetime_months',
        'repair_monthly_operating_cost',
        'replacement_monthly_operating_cost',
    ];

    public function __construct(private readonly int $closeCallBasisPoints = 1000)
    {
        if (PHP_INT_SIZE < 8 || $closeCallBasisPoints < 0 || $closeCallBasisPoints > 10_000) {
            throw new InvalidArgumentException('A 64-bit runtime and a threshold between 0 and 10000 are required.');
        }
    }

    /** @return array<string, int> */
    public function normalize(array $input): array
    {
        $normalized = [];
        foreach (self::FIELDS as $field) {
            $isLifetime = in_array($field, ['repair_lifetime_months', 'replacement_lifetime_months'], true);
            $isOperatingCost = in_array($field, ['repair_monthly_operating_cost', 'replacement_monthly_operating_cost'], true);
            $defaultValue = $isOperatingCost ? 0 : null;
            $value = array_key_exists($field, $input) ? $input[$field] : $defaultValue;
            $minimum = $isLifetime ? 1 : 0;

            if ($isLifetime) {
                $maximum = self::MAX_MONTHS;
            } elseif ($isOperatingCost) {
                $maximum = self::MAX_OPERATING_COST;
            } else {
                $maximum = self::MAX_COST;
            }

            if (! is_int($value) || $value < $minimum || $value > $maximum) {
                throw new InvalidArgumentException("Invalid integer or range for {$field}.");
            }
            $normalized[$field] = $value;
        }

        return $normalized;
    }

    /** @return array<string, mixed> */
    public function calculate(array $input): array
    {
        $input = $this->normalize($input);
        $repairLifetimeMonths = $input['repair_lifetime_months'];
        $replacementLifetimeMonths = $input['replacement_lifetime_months'];
        $repairCostNumerator = $input['repair_cost'] + $input['repair_monthly_operating_cost'] * $repairLifetimeMonths;
        $replacementCostNumerator = $input['replacement_cost'] + $input['replacement_monthly_operating_cost'] * $replacementLifetimeMonths;

        // Samakan penyebut agar biaya bulanan dibandingkan secara eksak tanpa float.
        $repairComparableCost = $repairCostNumerator * $replacementLifetimeMonths;
        $replacementComparableCost = $replacementCostNumerator * $repairLifetimeMonths;
        $costDifference = abs($repairComparableCost - $replacementComparableCost);
        $higherCost = max($repairComparableCost, $replacementComparableCost);
        $isCloseCall = $higherCost === 0 || $costDifference * 10_000 <= $higherCost * $this->closeCallBasisPoints;

        if ($isCloseCall) {
            $classification = 'CLOSE_CALL';
        } elseif ($repairComparableCost < $replacementComparableCost) {
            $classification = 'FAVOR_REPAIR';
        } else {
            $classification = 'FAVOR_REPLACE';
        }

        // Pembulatan hanya untuk tampilan, setelah klasifikasi ditentukan.
        $repairMonthlyCost = $this->roundHalfUp($repairCostNumerator, $repairLifetimeMonths);
        $replacementMonthlyCost = $this->roundHalfUp($replacementCostNumerator, $replacementLifetimeMonths);
        $monthlyDifference = $this->roundHalfUp($costDifference, $repairLifetimeMonths * $replacementLifetimeMonths);
        $relativeDifferenceBasisPoints = $higherCost === 0 ? 0 : $this->roundHalfUp($costDifference * 10_000, $higherCost);
        $reason = match ($classification) {
            'FAVOR_REPAIR' => 'Repair has the lower estimated monthly cost and the exact relative difference exceeds the close-call threshold.',
            'FAVOR_REPLACE' => 'Replacement has the lower estimated monthly cost and the exact relative difference exceeds the close-call threshold.',
            default => 'The exact relative difference is within or equal to the close-call threshold; cost alone does not strongly favor either option.',
        };

        return [
            'currency' => 'IDR',
            'calculation_version' => self::VERSION,
            'classification' => $classification,
            'repair_monthly_cost' => $repairMonthlyCost,
            'replacement_monthly_cost' => $replacementMonthlyCost,
            'monthly_difference' => $monthlyDifference,
            'relative_difference_basis_points' => $relativeDifferenceBasisPoints,
            'close_call_threshold_basis_points' => $this->closeCallBasisPoints,
            'exact_monthly_costs' => [
                'repair' => ['numerator' => $repairCostNumerator, 'denominator' => $repairLifetimeMonths],
                'replacement' => ['numerator' => $replacementCostNumerator, 'denominator' => $replacementLifetimeMonths],
            ],
            'rounding' => 'HALF_UP_TO_INTEGER_IDR',
            'formula' => 'upfront_cost / lifetime_months + monthly_operating_cost',
            'explanation' => "Repair upfront: IDR {$input['repair_cost']}; replacement upfront: IDR {$input['replacement_cost']}. Estimated monthly costs: IDR {$repairMonthlyCost} vs IDR {$replacementMonthlyCost}; monthly difference: IDR {$monthlyDifference}. {$reason}",
            'assumptions' => [
                "Repair lifetime: {$repairLifetimeMonths} months; replacement lifetime: {$replacementLifetimeMonths} months, as estimated by the user.",
                'Operating costs are constant per month; omitted operating costs are treated as zero.',
                'Relative difference uses the higher exact monthly cost as denominator; both zero means zero difference.',
                'Classification uses unrounded values; displayed amounts and basis points are rounded.',
            ],
            'warnings' => [
                'Normalized monthly costs are not a prediction of total spending over an equal time horizon.',
                'Affordability, repeat failures, residual value, performance, and lifetime uncertainty are not modeled.',
                'The threshold is a product heuristic, not a scientific standard. Estimates do not guarantee the best decision.',
                'No quantified electronic waste or carbon savings are inferred.',
            ],
        ];
    }

    private function roundHalfUp(int $numerator, int $denominator): int
    {
        $wholeRupiah = intdiv($numerator, $denominator);
        $remainder = $numerator % $denominator;
        $roundUp = $remainder * 2 >= $denominator;

        return $wholeRupiah + ($roundUp ? 1 : 0);
    }
}
