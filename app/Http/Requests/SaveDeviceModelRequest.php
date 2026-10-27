<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class SaveDeviceModelRequest extends SaveDeviceBrandRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'normalized_name' => ['required', 'string', Rule::unique('device_models')->where('device_brand_id', $this->route('brand')->id)->ignore($this->route('deviceModel')?->id)],
        ];
    }

    public function messages(): array
    {
        return ['normalized_name.unique' => 'Ce modèle existe déjà pour cette marque.', 'name.required' => 'Indiquez le nom du modèle.'];
    }
}
