<?php

namespace App\Http\Requests\Web;

use App\Services\DecisionAnalysisService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnalysisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'device_id' => [
                'required', 'integer', 'min:1', 'max:999999999999999999',
                Rule::exists('devices', 'id')->where('user_id', $this->user()->id),
            ],
            'repair_cost' => ['required', 'integer', 'min:0', 'max:'.DecisionAnalysisService::MAX_COST],
            'repair_lifetime_months' => ['required', 'integer', 'min:1', 'max:'.DecisionAnalysisService::MAX_MONTHS],
            'replacement_cost' => ['required', 'integer', 'min:0', 'max:'.DecisionAnalysisService::MAX_COST],
            'replacement_lifetime_months' => ['required', 'integer', 'min:1', 'max:'.DecisionAnalysisService::MAX_MONTHS],
            'repair_monthly_operating_cost' => ['nullable', 'integer', 'min:0', 'max:'.DecisionAnalysisService::MAX_OPERATING_COST],
            'replacement_monthly_operating_cost' => ['nullable', 'integer', 'min:0', 'max:'.DecisionAnalysisService::MAX_OPERATING_COST],
        ];
    }

    /** @return array<string, int> */
    public function analysisInput(): array
    {
        $data = $this->validated();
        $input = [];
        foreach (DecisionAnalysisService::FIELDS as $field) {
            if (isset($data[$field])) {
                $input[$field] = (int) $data[$field];
            }
        }

        return $input;
    }
}
