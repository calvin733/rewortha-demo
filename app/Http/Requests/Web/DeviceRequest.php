<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class DeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->route('device')) {
            Gate::authorize('view', $this->route('device'));
        }

        return true;
    }

    public function rules(): array
    {
        $presence = $this->isMethod('PATCH') ? 'sometimes' : 'required';

        return [
            'name' => [$presence, 'required', 'string', 'max:120'],
            'category' => [$presence, 'required', Rule::in(['laptop', 'smartphone', 'audio', 'peripheral', 'other'])],
            'brand' => ['sometimes', 'nullable', 'string', 'max:80'],
            'model' => ['sometimes', 'nullable', 'string', 'max:120'],
            'purchase_year' => ['sometimes', 'nullable', 'integer', 'between:1970,'.now()->year],
            'condition_notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }

    public function deviceData(): array
    {
        $data = $this->validated();
        if (isset($data['purchase_year'])) {
            $data['purchase_year'] = (int) $data['purchase_year'];
        }

        return $data;
    }
}
