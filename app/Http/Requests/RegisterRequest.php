<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => mb_strtolower(trim($this->input('email')))]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email:rfc', 'max:254', 'unique:users,email'],
            'password' => [
                'bail',
                'required',
                'string',
                'confirmed',
                'max:72',
                Password::min(12)->letters()->mixedCase()->numbers(),
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (strlen($value) > 72) {
                        $fail('The password must not exceed 72 bytes.');
                    }
                },
            ],
        ];
    }
}
