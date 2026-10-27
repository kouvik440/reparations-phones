<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRepairRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('repairs.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'device_brand_id' => ['required', 'integer', 'exists:device_brands,id'],
            'device_model_id' => ['required', 'integer', Rule::exists('device_models', 'id')->where('device_brand_id', $this->input('device_brand_id'))],
            'probleme' => ['required', 'string', 'max:10000'],
            'date_depot' => ['required', 'date', 'before_or_equal:today'],
            'prix' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
        ];
    }

    public function messages(): array
    {
        return ['device_brand_id.required' => 'Choisissez une marque du catalogue.', 'device_model_id.required' => 'Choisissez un modèle.', 'device_model_id.exists' => 'Ce modèle ne correspond pas à la marque sélectionnée.'];
    }
}
