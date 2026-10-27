<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveDeviceBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('catalog.manage') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('name'))) {
            $name = preg_replace('/\s+/u', ' ', trim($this->input('name')));
            $this->merge(['name' => $name, 'normalized_name' => mb_strtolower($name)]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'normalized_name' => ['required', 'string', Rule::unique('device_brands')->ignore($this->route('brand')?->id)],
        ];
    }

    public function messages(): array
    {
        return ['normalized_name.unique' => 'Cette marque existe déjà.', 'name.required' => 'Indiquez le nom de la marque.'];
    }
}
