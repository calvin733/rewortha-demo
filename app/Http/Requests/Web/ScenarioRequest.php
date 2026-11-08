<?php

namespace App\Http\Requests\Web;

use App\Services\DecisionAnalysisService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;

class ScenarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        Gate::authorize('view', $this->route('analysis'));

        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:120'],
            'repair_cost' => ['nullable', 'integer', 'min:0', 'max:'.DecisionAnalysisService::MAX_COST],
            'repair_lifetime_months' => ['nullable', 'integer', 'min:1', 'max:'.DecisionAnalysisService::MAX_MONTHS],
            'replacement_cost' => ['nullable', 'integer', 'min:0', 'max:'.DecisionAnalysisService::MAX_COST],
            'replacement_lifetime_months' => ['nullable', 'integer', 'min:1', 'max:'.DecisionAnalysisService::MAX_MONTHS],
            'repair_monthly_operating_cost' => ['nullable', 'integer', 'min:0', 'max:'.DecisionAnalysisService::MAX_OPERATING_COST],
            'replacement_monthly_operating_cost' => ['nullable', 'integer', 'min:0', 'max:'.DecisionAnalysisService::MAX_OPERATING_COST],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            foreach (DecisionAnalysisService::FIELDS as $field) {
                if ($this->filled($field)) {
                    return;
                }
            }
            $validator->errors()->add('scenario', 'Change at least one cost or lifetime to create a scenario.');
        }];
    }

    /** @return array<string, int|string|null> */
    public function scenarioInput(): array
    {
        $data = $this->validated();
        $input = ['name' => $data['name'] ?? null];
        foreach (DecisionAnalysisService::FIELDS as $field) {
            if (isset($data[$field])) {
                $input[$field] = (int) $data[$field];
            }
        }

        return $input;
    }
}
