<?php

namespace App\Http\Requests;

class UpdateRepairRequest extends StoreRepairRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('repairs.edit') ?? false;
    }

    public function rules(): array
    {
        return [...parent::rules(), 'status_version' => ['required', 'integer', 'min:0']];
    }
}
