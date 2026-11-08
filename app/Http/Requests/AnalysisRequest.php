<?php

namespace App\Http\Requests;

use App\Services\DecisionAnalysisService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class AnalysisRequest extends FormRequest
{
    public function authorize(): bool
    {
        Gate::authorize('view', $this->route('device'));

        return true;
    }

    public function rules(): array
    {
        return [
            'repair_cost' => ['required', 'integer:strict', 'min:0', 'max:'.DecisionAnalysisService::MAX_COST],
            'repair_lifetime_months' => ['required', 'integer:strict', 'min:1', 'max:'.DecisionAnalysisService::MAX_MONTHS],
            'replacement_cost' => ['required', 'integer:strict', 'min:0', 'max:'.DecisionAnalysisService::MAX_COST],
            'replacement_lifetime_months' => ['required', 'integer:strict', 'min:1', 'max:'.DecisionAnalysisService::MAX_MONTHS],
            'repair_monthly_operating_cost' => ['sometimes', 'required', 'integer:strict', 'min:0', 'max:'.DecisionAnalysisService::MAX_OPERATING_COST],
            'replacement_monthly_operating_cost' => ['sometimes', 'required', 'integer:strict', 'min:0', 'max:'.DecisionAnalysisService::MAX_OPERATING_COST],
        ];
    }
}
