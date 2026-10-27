<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('roles.manage') === true && ! $this->route('role')?->isProtected();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name) ? trim($this->name) : $this->name,
            'permissions' => $this->input('permissions', []),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('roles', 'name')->ignore($this->route('role')?->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'permissions' => ['array', 'max:'.count(config('permissions'))],
            'permissions.*' => ['required', 'string', 'distinct', Rule::in(array_keys(config('permissions')))],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $selected = $this->input('permissions', []);
            foreach ($selected as $permission) {
                foreach (config('permissions')[$permission]['requires'] as $required) {
                    if (! in_array($required, $selected, true)) {
                        $validator->errors()->add('permissions', '« '.config('permissions')[$permission]['label'].' » nécessite aussi « '.config('permissions')[$required]['label'].' ».');
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Indiquez le nom du rôle.',
            'name.unique' => 'Un rôle porte déjà ce nom.',
            'permissions.array' => 'Choisissez les permissions dans la liste.',
            'permissions.*.in' => 'Une permission sélectionnée ne fait pas partie du catalogue.',
            'permissions.*.distinct' => 'Une permission ne peut être sélectionnée qu’une fois.',
        ];
    }
}
