<?php

namespace Litepie\Integration\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIntegrationRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'redirect_uris' => ['required', 'array', 'min:1'],
            'redirect_uris.*' => ['required', 'url', 'max:500'],
            'status' => ['sometimes', 'in:active,inactive'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The integration name is required.',
            'redirect_uris.required' => 'At least one redirect URI is required.',
            'redirect_uris.min' => 'At least one redirect URI is required.',
            'redirect_uris.*.url' => 'Each redirect URI must be a valid URL.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure redirect_uris is always an array
        if ($this->has('redirect_uris') && is_string($this->redirect_uris)) {
            $this->merge([
                'redirect_uris' => [$this->redirect_uris]
            ]);
        }
    }
}
