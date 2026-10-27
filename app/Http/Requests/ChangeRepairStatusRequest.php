<?php

namespace App\Http\Requests;

use App\Enums\RepairStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeRepairStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('repairs.change_status') ?? false;
    }

    public function rules(): array
    {
        return ['statut' => ['required', Rule::enum(RepairStatus::class)], 'status_version' => ['required', 'integer', 'min:0']];
    }
}
