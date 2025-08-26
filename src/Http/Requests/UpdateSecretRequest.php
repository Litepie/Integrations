<?php

namespace Litepie\Integration\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSecretRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled in the controller
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'status' => ['sometimes', 'in:active,inactive'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The secret name is required.',
            'expires_at.after' => 'The expiration date must be in the future.',
        ];
    }
}
