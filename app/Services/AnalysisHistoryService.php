<?php

namespace App\Services;

use App\Models\Analysis;
use App\Models\Device;
use App\Models\Scenario;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AnalysisHistoryService
{
    public function __construct(private readonly DecisionAnalysisService $engine) {}

    public function analyze(Device $device, array $input): Analysis
    {
        return DB::transaction(function () use ($device, $input): Analysis {
            $device = Device::query()->lockForUpdate()->findOrFail($device->id);
            abort_if($device->archived_at !== null, 409, 'Archived devices cannot receive new analyses.');
            $input = $this->engine->normalize($input);
            $result = $this->engine->calculate($input);

            return $device->analyses()->create([
                'device_snapshot' => $device->only(['id', 'name', 'category', 'brand', 'model', 'purchase_year', 'condition_notes']),
                'input_snapshot' => $input, 'result_snapshot' => $result,
                'calculation_version' => $result['calculation_version'], 'classification' => $result['classification'],
            ]);
        });
    }

    public function simulate(Analysis $analysis, array $overrides): Scenario
    {
        return DB::transaction(function () use ($analysis, $overrides): Scenario {
            $device = Device::query()->lockForUpdate()->findOrFail($analysis->device_id);
            abort_if($device->archived_at !== null, 409, 'Archived devices cannot receive new scenarios.');
            $baselineInput = $this->engine->normalize($analysis->input_snapshot);
            $input = $this->engine->normalize(array_replace($baselineInput, array_intersect_key($overrides, array_flip(DecisionAnalysisService::FIELDS))));
            if ($input === $baselineInput) {
                throw ValidationException::withMessages(['scenario' => 'At least one input must differ from the original analysis.']);
            }
            $result = $this->engine->calculate($input);

            return $analysis->scenarios()->create([
                'name' => $overrides['name'] ?? null, 'input_snapshot' => $input, 'result_snapshot' => $result,
                'calculation_version' => $result['calculation_version'], 'classification' => $result['classification'],
            ]);
        });
    }
}
