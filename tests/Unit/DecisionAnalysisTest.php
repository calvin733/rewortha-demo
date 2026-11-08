<?php

use App\Services\DecisionAnalysisService;

it('calculates the example with exact rational costs and half-up presentation', function (): void {
    $result = (new DecisionAnalysisService)->calculate(reworthaInput());
    expect($result)->toMatchArray([
        'repair_monthly_cost' => 58333, 'replacement_monthly_cost' => 166667,
        'monthly_difference' => 108333, 'relative_difference_basis_points' => 6500,
        'classification' => 'FAVOR_REPAIR', 'calculation_version' => '1.0.0',
    ])->and($result['exact_monthly_costs']['repair'])->toBe(['numerator' => 700000, 'denominator' => 12]);
});

it('preserves the complete public result for the reference example', function (): void {
    $result = (new DecisionAnalysisService)->calculate(reworthaInput());

    expect($result)->toBe([
        'currency' => 'IDR',
        'calculation_version' => '1.0.0',
        'classification' => 'FAVOR_REPAIR',
        'repair_monthly_cost' => 58333,
        'replacement_monthly_cost' => 166667,
        'monthly_difference' => 108333,
        'relative_difference_basis_points' => 6500,
        'close_call_threshold_basis_points' => 1000,
        'exact_monthly_costs' => [
            'repair' => ['numerator' => 700000, 'denominator' => 12],
            'replacement' => ['numerator' => 8000000, 'denominator' => 48],
        ],
        'rounding' => 'HALF_UP_TO_INTEGER_IDR',
        'formula' => 'upfront_cost / lifetime_months + monthly_operating_cost',
        'explanation' => 'Repair upfront: IDR 700000; replacement upfront: IDR 8000000. Estimated monthly costs: IDR 58333 vs IDR 166667; monthly difference: IDR 108333. Repair has the lower estimated monthly cost and the exact relative difference exceeds the close-call threshold.',
        'assumptions' => [
            'Repair lifetime: 12 months; replacement lifetime: 48 months, as estimated by the user.',
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
    ]);
});

it('classifies exact boundaries symmetrically', function (int $repair, int $replacement, string $expected): void {
    $result = (new DecisionAnalysisService)->calculate(reworthaInput(['repair_cost' => $repair, 'replacement_cost' => $replacement, 'repair_lifetime_months' => 1, 'replacement_lifetime_months' => 1]));
    expect($result['classification'])->toBe($expected);
})->with([
    [900, 1000, 'CLOSE_CALL'], [899, 1000, 'FAVOR_REPAIR'],
    [1000, 900, 'CLOSE_CALL'], [1000, 899, 'FAVOR_REPLACE'],
    [0, 0, 'CLOSE_CALL'], [0, 1, 'FAVOR_REPAIR'], [1, 0, 'FAVOR_REPLACE'], [42, 42, 'CLOSE_CALL'],
]);

it('includes operating costs and rounds half up', function (): void {
    $result = (new DecisionAnalysisService)->calculate(reworthaInput(['repair_cost' => 1, 'repair_lifetime_months' => 2, 'repair_monthly_operating_cost' => 10, 'replacement_cost' => 10, 'replacement_lifetime_months' => 3, 'replacement_monthly_operating_cost' => 2]));
    expect($result['repair_monthly_cost'])->toBe(11)->and($result['replacement_monthly_cost'])->toBe(5)->and($result['classification'])->toBe('FAVOR_REPLACE');
});

it('classifies before rounding the displayed percentage', function (): void {
    $result = (new DecisionAnalysisService)->calculate(reworthaInput(['repair_cost' => 89999, 'replacement_cost' => 100000, 'repair_lifetime_months' => 1, 'replacement_lifetime_months' => 1]));
    expect($result['relative_difference_basis_points'])->toBe(1000)->and($result['classification'])->toBe('FAVOR_REPAIR');
});

it('records the configured threshold', function (): void {
    $input = reworthaInput(['repair_cost' => 800, 'replacement_cost' => 1000, 'repair_lifetime_months' => 1, 'replacement_lifetime_months' => 1]);
    expect((new DecisionAnalysisService(2000))->calculate($input))->toMatchArray(['classification' => 'CLOSE_CALL', 'close_call_threshold_basis_points' => 2000]);
});

it('handles maximum supported values without floating point or overflow', function (): void {
    $result = (new DecisionAnalysisService)->calculate(reworthaInput(['repair_cost' => 1000000000, 'replacement_cost' => 1000000000, 'repair_lifetime_months' => 240, 'replacement_lifetime_months' => 1, 'repair_monthly_operating_cost' => 10000000, 'replacement_monthly_operating_cost' => 10000000]));
    expect($result['repair_monthly_cost'])->toBe(14166667)->and($result['replacement_monthly_cost'])->toBe(1010000000)->and($result['classification'])->toBe('FAVOR_REPAIR');
});

it('rejects invalid direct service input', function (string $field, mixed $value): void {
    (new DecisionAnalysisService)->calculate(reworthaInput([$field => $value]));
})->with([
    ['repair_lifetime_months', 0], ['repair_lifetime_months', -1], ['replacement_lifetime_months', 241],
    ['repair_cost', -1], ['replacement_cost', 1000000001], ['repair_monthly_operating_cost', 10000001],
    ['repair_cost', 1.5], ['repair_cost', '100'], ['repair_cost', null], ['repair_cost', true],
])->throws(InvalidArgumentException::class);

it('rejects invalid engine thresholds', function (int $threshold): void {
    new DecisionAnalysisService($threshold);
})->with([-1, 10001])->throws(InvalidArgumentException::class);
