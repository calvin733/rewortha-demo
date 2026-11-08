<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class HistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1', 'max:100000'],
            'device_id' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
