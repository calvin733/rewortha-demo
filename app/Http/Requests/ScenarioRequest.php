<?php

namespace App\Http\Requests;

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
            'repair_cost' => ['sometimes', 'required', 'integer:strict', 'min:0', 'max:'.DecisionAnalysisService::MAX_COST],
            'repair_lifetime_months' => ['sometimes', 'required', 'integer:strict', 'min:1', 'max:'.DecisionAnalysisService::MAX_MONTHS],
            'replacement_cost' => ['sometimes', 'required', 'integer:strict', 'min:0', 'max:'.DecisionAnalysisService::MAX_COST],
            'replacement_lifetime_months' => ['sometimes', 'required', 'integer:strict', 'min:1', 'max:'.DecisionAnalysisService::MAX_MONTHS],
            'repair_monthly_operating_cost' => ['sometimes', 'required', 'integer:strict', 'min:0', 'max:'.DecisionAnalysisService::MAX_OPERATING_COST],
            'replacement_monthly_operating_cost' => ['sometimes', 'required', 'integer:strict', 'min:0', 'max:'.DecisionAnalysisService::MAX_OPERATING_COST],
            'name' => ['sometimes', 'nullable', 'string', 'max:120'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (count(array_intersect(array_keys($this->all()), DecisionAnalysisService::FIELDS)) === 0) {
                $validator->errors()->add('scenario', 'Provide at least one cost or lifetime override.');
            }
        }];
    }
}
